<?php

class BlessFlow_Gemini {

	private $api_key;
	private $model;
	private $api_url = 'https://generativelanguage.googleapis.com/v1beta/models/';

	public function __construct( $api_key = null ) {
		$this->api_key = $api_key ? $api_key : get_option( 'blessflow_gemini_api_key' );
		$this->model = get_option( 'blessflow_gemini_model', 'gemini-1.5-flash' );
	}

	public function generate_post( $topic ) {
		if ( empty( $this->api_key ) ) {
			return new WP_Error( 'missing_key', 'API Key do Gemini não configurada.' );
		}

		$endpoint = $this->api_url . $this->model . ':generateContent?key=' . $this->api_key;

		$default_system = "Você é um especialista em SEO. Siga rigorosamente estas regras:
1. Estruture o conteúdo com tags HTML (H2, H3, p, ul/ol).
2. O conteúdo deve ser rico, informativo e ter entre 800-1200 palavras.
3. Responda APENAS em JSON válido.
4. O JSON deve ter as chaves: 'titulo', 'conteudo', 'meta_descricao', 'tags' (array) e 'image_keyword' (palavra-chave em inglês para buscar imagem).";

		$system_instruction = get_option( 'blessflow_system_instruction' );
		if ( empty( $system_instruction ) ) {
			$system_instruction = $default_system;
		}

		$prompt_template = get_option( 'blessflow_prompt_template' );
		if ( empty( $prompt_template ) ) {
			$prompt_template = "Escreva um artigo completo e otimizado para SEO sobre: '{topic}'.";
		}

		$prompt = str_replace( '{topic}', $topic, $prompt_template );

		$body = [
			'system_instruction' => [
				'parts' => [ [ 'text' => $system_instruction ] ]
			],
			'contents' => [
				[ 'parts' => [ [ 'text' => $prompt ] ] ]
			],
			'generationConfig' => [
				'response_mime_type' => 'application/json'
			]
		];

		$args = [
			'headers' => [ 'Content-Type' => 'application/json' ],
			'body'    => json_encode( $body ),
			'timeout' => 60
		];

		$response = wp_remote_post( $endpoint, $args );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body_res = wp_remote_retrieve_body( $response );
		$data = json_decode( $body_res, true );

		if ( $code !== 200 ) {
			$msg = isset( $data['error']['message'] ) ? $data['error']['message'] : 'Erro desconhecido na API do Gemini.';
			return new WP_Error( 'api_error', $msg );
		}

		if ( isset( $data['candidates'][0]['content']['parts'][0]['text'] ) ) {
			$raw_json = $data['candidates'][0]['content']['parts'][0]['text'];
			$parsed = json_decode( $raw_json, true );

			if ( json_last_error() === JSON_ERROR_NONE ) {
				// Adiciona estimativa de tokens (simulada pois a API nem sempre retorna usageMetadata em falhas)
				$usage = isset($data['usageMetadata']) ? $data['usageMetadata'] : ['promptTokenCount' => 0, 'candidatesTokenCount' => 0];
				$parsed['usage'] = $usage;
				return $parsed;
			} else {
				return new WP_Error( 'json_parse_error', 'Falha ao decodificar JSON do Gemini.' );
			}
		}

		return new WP_Error( 'no_content', 'Gemini não retornou conteúdo válido.' );
	}
	public function test_connection() {
		if ( empty( $this->api_key ) ) {
			return new WP_Error( 'missing_key', 'API Key do Gemini não configurada.' );
		}

		$endpoint = $this->api_url . $this->model . ':generateContent?key=' . $this->api_key;
		
		$body = [
			'contents' => [
				[ 'parts' => [ [ 'text' => 'Hello' ] ] ]
			]
		];

		$args = [
			'headers' => [ 'Content-Type' => 'application/json' ],
			'body'    => json_encode( $body ),
			'timeout' => 10
		];

		$response = wp_remote_post( $endpoint, $args );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body_res = wp_remote_retrieve_body( $response );
		$data = json_decode( $body_res, true );

		if ( $code !== 200 ) {
			$msg = isset( $data['error']['message'] ) ? $data['error']['message'] : 'Erro ao conectar com Gemini.';
			return new WP_Error( 'api_error', $msg );
		}

		return true;
	}
	public function generate_title( $topic, $params = array() ) {
		$system_instruction = "Você é um especialista em SEO. Escreva 5 variações de títulos engajadores para o tópico fornecido. Responda APENAS em JSON no formato: { \"options\": [\"Titulo 1\", \"Titulo 2\", ...] }";
		
		$prompt = "Tópico: $topic";
		return $this->make_request( $system_instruction, $prompt, $params );
	}

	public function generate_outline( $topic, $title, $num_sections, $params = array() ) {
		$system_instruction = "Você é um especialista em SEO. Crie uma estrutura de tópicos (outline) para um artigo. Responda APENAS em JSON no formato: { \"sections\": [\"Seção 1\", \"Seção 2\", ...] }";
		
		$prompt = "Tópico: $topic\nTítulo: $title\nGere exatamente $num_sections seções.";
		return $this->make_request( $system_instruction, $prompt, $params );
	}

	public function generate_content_section( $section_title, $context_title, $params = array() ) {
		$system_instruction = "Você é um redator SEO experiente. Escreva o conteúdo para a seção de um artigo. Use HTML (p, ul, ol, strong). Não use H1 ou H2 (já definidos). Mantenha o tom informativo.";
		
		$prompt = "Título do Artigo: $context_title\nEscreva sobre a seção: $section_title";
		return $this->make_request( $system_instruction, $prompt, $params, false ); // Retorna texto puro, não JSON
	}

	public function generate_excerpt( $content, $params = array() ) {
		$system_instruction = "Resuma o texto abaixo em uma meta-description SEO de até 160 caracteres.";
		$prompt = $content;
		return $this->make_request( $system_instruction, $prompt, $params, false );
	}

	private function make_request( $system, $prompt, $params, $expect_json = true ) {
		if ( empty( $this->api_key ) ) {
			return new WP_Error( 'missing_key', 'API Key do Gemini não configurada.' );
		}

		$model = isset($params['model']) && !empty($params['model']) ? $params['model'] : $this->model;
		$temperature = isset($params['temperature']) ? floatval($params['temperature']) : 1.0;

		$endpoint = $this->api_url . $model . ':generateContent?key=' . $this->api_key;

		$body = [
			'system_instruction' => [ 'parts' => [ [ 'text' => $system ] ] ],
			'contents' => [ [ 'parts' => [ [ 'text' => $prompt ] ] ] ],
			'generationConfig' => [
				'temperature' => $temperature
			]
		];

		if ( $expect_json ) {
			$body['generationConfig']['response_mime_type'] = 'application/json';
		}

		$args = [
			'headers' => [ 'Content-Type' => 'application/json' ],
			'body'    => json_encode( $body ),
			'timeout' => 60
		];

		$response = wp_remote_post( $endpoint, $args );

		if ( is_wp_error( $response ) ) return $response;

		$code = wp_remote_retrieve_response_code( $response );
		$body_res = wp_remote_retrieve_body( $response );
		$data = json_decode( $body_res, true );

		if ( $code !== 200 ) {
			$msg = isset( $data['error']['message'] ) ? $data['error']['message'] : 'Erro na API Gemini.';
			return new WP_Error( 'api_error', $msg );
		}

		if ( isset( $data['candidates'][0]['content']['parts'][0]['text'] ) ) {
			$text = $data['candidates'][0]['content']['parts'][0]['text'];
			
			// Usage metadata
			$usage = isset($data['usageMetadata']) ? $data['usageMetadata'] : ['promptTokenCount' => 0, 'candidatesTokenCount' => 0];

			if ( $expect_json ) {
				$parsed = json_decode( $text, true );
				if ( json_last_error() === JSON_ERROR_NONE ) {
					$parsed['usage'] = $usage;
					return $parsed;
				}
				return new WP_Error( 'json_parse_error', 'Erro ao processar JSON da IA.' );
			}
			
			return [ 'text' => $text, 'usage' => $usage ];
		}

		return new WP_Error( 'no_content', 'Conteúdo vazio da IA.' );
	}
}
