<?php

class BlessFlow_Unsplash {

	private $access_key;
	private $api_url = 'https://api.unsplash.com/search/photos';

	public function __construct( $key = null ) {
		$this->access_key = $key ? $key : get_option( 'blessflow_unsplash_key' );
	}

	public function search_image( $query ) {
		if ( empty( $this->access_key ) ) {
			return new WP_Error( 'missing_key', 'Access Key do Unsplash não configurada.' );
		}

		// Clean stop words
		$clean_query = $this->remove_stop_words( $query );
		
		$url = add_query_arg([
			'query'     => urlencode( $clean_query ),
			'per_page'  => 1,
			'client_id' => $this->access_key,
			'orientation' => 'landscape'
		], $this->api_url);

		$response = wp_remote_get( $url, [ 'timeout' => 20 ] );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );
		
		if ( $code === 401 ) {
			return new WP_Error( 'auth_error', 'Chave de Acesso Inválida (401).' );
		}
		
		if ( $code === 403 || $code === 429 ) {
			return new WP_Error( 'rate_limit', 'Limite de requisições excedido (403/429).' );
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( empty( $data['results'] ) ) {
			return new WP_Error( 'no_results', "Nenhuma imagem encontrada para: $clean_query" );
		}

		$photo = $data['results'][0];

		return [
			'url'      => $photo['urls']['regular'],
			'creditos' => 'Foto de ' . $photo['user']['name'] . ' no Unsplash',
			'alt'      => $photo['alt_description'] ?: $clean_query
		];
	}

	private function remove_stop_words( $text ) {
		$stop_words = [ 'de', 'do', 'da', 'o', 'a', 'os', 'as', 'em', 'um', 'uma', 'para', 'com', 'no', 'na', 'the', 'and', 'with', 'for', 'best', 'how', 'to', 'guide', 'tutorial' ];
		
		$words = explode( ' ', $text );
		$filtered = array_filter( $words, function($w) use ($stop_words) {
			return !in_array( strtolower($w), $stop_words );
		});
		
		return implode( ' ', $filtered );
	}
}
