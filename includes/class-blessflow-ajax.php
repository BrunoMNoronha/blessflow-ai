<?php

class BlessFlow_Ajax {

	private $gemini;
	private $unsplash;
	private $db;

	public function __construct() {
		$this->gemini = new BlessFlow_Gemini();
		$this->unsplash = new BlessFlow_Unsplash();
		$this->db = new BlessFlow_DB();

		add_action( 'wp_ajax_blessflow_generate_content', array( $this, 'handle_generate_content' ) );
		add_action( 'wp_ajax_blessflow_generate_image', array( $this, 'handle_generate_image' ) );
        
        // Validation Actions
        add_action( 'wp_ajax_blessflow_test_gemini', array( $this, 'handle_test_gemini' ) );
		// V2 Steps
		add_action( 'wp_ajax_blessflow_step_title', array( $this, 'handle_step_title' ) );
		add_action( 'wp_ajax_blessflow_step_outline', array( $this, 'handle_step_outline' ) );
		add_action( 'wp_ajax_blessflow_step_content', array( $this, 'handle_step_content' ) );
		add_action( 'wp_ajax_blessflow_save_post_v2', array( $this, 'handle_save_post_v2' ) );
	}

	// ... Existing methods ...

	public function handle_step_title() {
		check_ajax_referer( 'blessflow_ajax_nonce', 'nonce' );
		if ( ! current_user_can( 'edit_posts' ) ) wp_send_json_error( 'Sem permissão.' );

		$topic = sanitize_text_field( $_POST['topic'] );
		$params = $this->get_params_from_request();

		$result = $this->gemini->generate_title( $topic, $params );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}
		
		wp_send_json_success( $result );
	}

	public function handle_step_outline() {
		check_ajax_referer( 'blessflow_ajax_nonce', 'nonce' );
		if ( ! current_user_can( 'edit_posts' ) ) wp_send_json_error( 'Sem permissão.' );

		$topic = sanitize_text_field( $_POST['topic'] );
		$title = sanitize_text_field( $_POST['title'] );
		$num_sections = intval( $_POST['num_sections'] );
		$params = $this->get_params_from_request();

		$result = $this->gemini->generate_outline( $topic, $title, $num_sections, $params );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}
		
		wp_send_json_success( $result );
	}

	public function handle_step_content() {
		check_ajax_referer( 'blessflow_ajax_nonce', 'nonce' );
		if ( ! current_user_can( 'edit_posts' ) ) wp_send_json_error( 'Sem permissão.' );

		$section = sanitize_text_field( $_POST['section'] );
		$title = sanitize_text_field( $_POST['title'] );
		$params = $this->get_params_from_request();

		$result = $this->gemini->generate_content_section( $section, $title, $params );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}
		
		// Log usage for each section generated is optional but good for costs
		// $this->log_db( ... ); 

		wp_send_json_success( $result );
	}

	public function handle_save_post_v2() {
		check_ajax_referer( 'blessflow_ajax_nonce', 'nonce' );
		if ( ! current_user_can( 'edit_posts' ) ) wp_send_json_error( 'Sem permissão.' );

		$title = sanitize_text_field( $_POST['title'] );
		// Content can contain HTML, so use wp_kses_post or similar
		$content = wp_kses_post( $_POST['content'] );
		$cat_id = intval( $_POST['category'] );
		$tags = isset($_POST['tags']) ? array_map('sanitize_text_field', $_POST['tags']) : [];
		$meta_desc = sanitize_text_field( $_POST['meta_desc'] );

		$post_id = wp_insert_post( array(
			'post_title'    => $title,
			'post_content'  => $content,
			'post_status'   => 'draft',
			'post_category' => array( $cat_id )
		));

		if ( is_wp_error( $post_id ) ) {
			wp_send_json_error( 'Erro ao criar post.' );
		}

		if ( ! empty( $tags ) ) {
			wp_set_post_tags( $post_id, $tags );
		}
		update_post_meta( $post_id, '_yoast_wpseo_metadesc', $meta_desc );

		// Image Sideload Logic (Optional V2) - Can reuse existing handle_generate_image via separate call
		
		wp_send_json_success( array( 'post_id' => $post_id, 'edit_link' => get_edit_post_link( $post_id, 'raw' ) ) );
	}

	private function get_params_from_request() {
		return [
			'model' => isset($_POST['model']) ? sanitize_text_field($_POST['model']) : '',
			'temperature' => isset($_POST['temperature']) ? floatval($_POST['temperature']) : 1.0,
			'language' => isset($_POST['language']) ? sanitize_text_field($_POST['language']) : 'pt-BR'
		];
	}


	public function handle_generate_content() {
		check_ajax_referer( 'blessflow_ajax_nonce', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( 'Sem permissão.' );
		}

		$topic = sanitize_text_field( $_POST['topico'] );
		$cat_id = intval( $_POST['categoria'] );

		if ( empty( $topic ) ) {
			wp_send_json_error( 'Tópico obrigatório.' );
		}

		$debug_logs = array();
		$debug_logs[] = array( 'msg' => 'Iniciando geração de texto com Gemini...', 'type' => 'info' );

		// 1. Generate Content (Gemini)
		$content_data = $this->gemini->generate_post( $topic );

		if ( is_wp_error( $content_data ) ) {
			$this->log_db( $topic, 'erro', 0, 0, 0, $content_data->get_error_message() );
			wp_send_json_error( $content_data->get_error_message() );
		}

		$debug_logs[] = array( 'msg' => 'Texto gerado. Criando post...', 'type' => 'success' );

		// 2. Create Post
		$post_id = wp_insert_post( array(
			'post_title'    => $content_data['titulo'],
			'post_content'  => $content_data['conteudo'],
			'post_status'   => 'draft',
			'post_category' => array( $cat_id )
		));

		if ( is_wp_error( $post_id ) ) {
			wp_send_json_error( 'Erro ao criar post no WordPress.' );
		}

		// 3. Set Meta & Tags
		if ( ! empty( $content_data['tags'] ) ) {
			wp_set_post_tags( $post_id, $content_data['tags'] );
		}
		update_post_meta( $post_id, '_yoast_wpseo_metadesc', $content_data['meta_descricao'] );

		// 4. Log to DB
		$usage = isset($content_data['usage']) ? $content_data['usage'] : ['promptTokenCount' => 0, 'candidatesTokenCount' => 0];
		$cost = $this->calculate_cost( $usage['promptTokenCount'], $usage['candidatesTokenCount'] );
		
		$this->log_db( $topic, 'sucesso', $usage['promptTokenCount'], $usage['candidatesTokenCount'], $cost, get_option( 'blessflow_gemini_model' ), $post_id );

		$image_keyword = ! empty( $content_data['image_keyword'] ) ? $content_data['image_keyword'] : $topic;

		wp_send_json_success( array(
			'post_id'       => $post_id,
			'titulo'        => $content_data['titulo'],
			'edit_link'     => get_edit_post_link( $post_id, 'raw' ),
			'image_keyword' => $image_keyword,
			'debug_logs'    => $debug_logs
		));
	}

	public function handle_generate_image() {
		check_ajax_referer( 'blessflow_ajax_nonce', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( 'Sem permissão.' );
		}

		$post_id = intval( $_POST['post_id'] );
		$keyword = sanitize_text_field( $_POST['keyword'] );
		$debug_logs = array();

		if ( ! get_option( 'blessflow_unsplash_key' ) ) {
			wp_send_json_error( 'Unsplash API Key não configurada.' );
		}

		$debug_logs[] = array( 'msg' => "Buscando imagem para: $keyword", 'type' => 'info' );
		
		$image_data = $this->unsplash->search_image( $keyword );

		if ( ! is_wp_error( $image_data ) ) {
			$debug_logs[] = array( 'msg' => 'Imagem encontrada. Baixando...', 'type' => 'info' );
			$sideload = $this->sideload_image( $image_data['url'], $post_id, $image_data['creditos'] );
			
			if ( ! is_wp_error( $sideload ) ) {
				$debug_logs[] = array( 'msg' => 'Imagem destacada definida com sucesso.', 'type' => 'success' );
				wp_send_json_success( array( 'debug_logs' => $debug_logs ) );
			} else {
				$msg = 'Falha no sideload: ' . $sideload->get_error_message();
				$debug_logs[] = array( 'msg' => $msg, 'type' => 'warn' );
				wp_send_json_error( $msg );
			}
		} else {
			$msg = 'Erro Unsplash: ' . $image_data->get_error_message();
			$debug_logs[] = array( 'msg' => $msg, 'type' => 'warn' );
			wp_send_json_error( $msg );
		}
	}

	private function log_db( $topic, $status, $in, $out, $cost, $model, $post_id = 0 ) {
		$this->db->log_generation( array(
			'post_id'        => $post_id,
			'topico'         => $topic,
			'status'         => $status,
			'tokens_input'   => $in,
			'tokens_output'  => $out,
			'custo_estimado' => $cost,
			'modelo_usado'   => $model
		));
	}

	private function calculate_cost( $input, $output ) {
		// Pricing for Gemini 2.5 Flash (USD)
		$input_price_usd = 0.30 / 1000000;
		$output_price_usd = 2.50 / 1000000;
		
		$cost_usd = ( $input * $input_price_usd ) + ( $output * $output_price_usd );
		
		// Convert to BRL (Fixed Rate 6.0 as per spec)
		return $cost_usd * 6.0;
	}

	private function sideload_image( $url, $post_id, $creditos ) {
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );

		$tmp = download_url( $url, 300, false );

		if ( is_wp_error( $tmp ) ) {
			return $tmp;
		}

		$file_array = array(
			'name'     => 'blessflow-img-' . time() . '.jpg',
			'tmp_name' => $tmp
		);

		$attachment_id = media_handle_sideload( $file_array, $post_id, $creditos );

		if ( is_wp_error( $attachment_id ) ) {
			@unlink( $tmp );
			return $attachment_id;
		}

		set_post_thumbnail( $post_id, $attachment_id );
		
		wp_update_post( array(
			'ID'           => $attachment_id,
			'post_excerpt' => $creditos
		));

		return $attachment_id;
	}

	public function handle_test_unsplash() {
		check_ajax_referer( 'blessflow_ajax_nonce', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( 'Sem permissão.' );
		}

		$key = isset( $_POST['api_key'] ) ? sanitize_text_field( $_POST['api_key'] ) : '';

		if ( empty( $key ) ) {
			wp_send_json_error( 'Chave de API vazia.' );
		}

		$unsplash = new BlessFlow_Unsplash( $key );
		// Try a simple search for "nature" to test the key
		$result = $unsplash->search_image( 'nature' );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		} else {
			wp_send_json_success( 'Conexão com Unsplash bem-sucedida! Encontrou: ' . $result['creditos'] );
		}
	}

	public function handle_test_gemini() {
		check_ajax_referer( 'blessflow_ajax_nonce', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( 'Sem permissão.' );
		}

		$key = isset( $_POST['api_key'] ) ? sanitize_text_field( $_POST['api_key'] ) : '';

		if ( empty( $key ) ) {
			wp_send_json_error( 'Chave de API vazia.' );
		}

		$gemini = new BlessFlow_Gemini( $key );
		$result = $gemini->test_connection();

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		} else {
			wp_send_json_success( 'Conexão com Gemini bem-sucedida!' );
		}
	}
}
