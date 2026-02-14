<?php

class BlessFlow_DB {

	private $table_name;

	public function __construct() {
		global $wpdb;
		$this->table_name = $wpdb->prefix . 'blessflow_historico';
	}

	public function log_generation( $data ) {
		global $wpdb;

		return $wpdb->insert(
			$this->table_name,
			array(
				'data_hora'      => current_time( 'mysql' ),
				'post_id'        => $data['post_id'],
				'topico'         => $data['topico'],
				'status'         => $data['status'],
				'tokens_input'   => $data['tokens_input'],
				'tokens_output'  => $data['tokens_output'],
				'custo_estimado' => $data['custo_estimado'],
				'modelo_usado'   => $data['modelo_usado']
			),
			array( '%s', '%d', '%s', '%s', '%d', '%d', '%f', '%s' )
		);
	}

	public function get_logs( $limit = 50 ) {
		global $wpdb;
		return $wpdb->get_results( "SELECT * FROM {$this->table_name} ORDER BY id DESC LIMIT $limit" );
	}
}
