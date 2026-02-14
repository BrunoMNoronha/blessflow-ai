<?php

class BlessFlow_Admin {

	private $plugin_name;
	private $version;

	public function __construct() {
		$this->plugin_name = 'blessflow-ai';
		$this->version = BLESSFLOW_VERSION;

		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	public function add_plugin_admin_menu() {
		add_menu_page(
			'BlessFlow AI', 
			'BlessFlow AI', 
			'manage_options', 
			$this->plugin_name, 
			array( $this, 'display_plugin_admin_page' ), 
			'dashicons-superhero', 
			6
		);
	}

	public function register_settings() {
		register_setting( 'blessflow_options_group', 'blessflow_gemini_api_key' );
		register_setting( 'blessflow_options_group', 'blessflow_gemini_model' );
		register_setting( 'blessflow_options_group', 'blessflow_unsplash_key' );
		// New Prompt Settings
		register_setting( 'blessflow_options_group', 'blessflow_system_instruction' );
		register_setting( 'blessflow_options_group', 'blessflow_prompt_template' );
	}

	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, BLESSFLOW_PLUGIN_URL . 'assets/css/blessflow-admin.css', array(), $this->version, 'all' );
	}

	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, BLESSFLOW_PLUGIN_URL . 'assets/js/blessflow-admin.js', array( 'jquery' ), $this->version, false );
		wp_localize_script( $this->plugin_name, 'blessflow_ajax', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'blessflow_ajax_nonce' )
		));
	}

	public function display_plugin_admin_page() {
		$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'dashboard';

		// Compatibilidade com links antigos do runner clássico.
		if ( 'runner' === $active_tab ) {
			$active_tab = 'runner_v2';
		}
		?>
		<div class="wrap">
			<h1>🤖 BlessFlow AI</h1>
			
			<h2 class="nav-tab-wrapper">
				<a href="?page=blessflow-ai&tab=dashboard" class="nav-tab <?php echo $active_tab == 'dashboard' ? 'nav-tab-active' : ''; ?>">Dashboard</a>
				<a href="?page=blessflow-ai&tab=runner_v2" class="nav-tab <?php echo $active_tab == 'runner_v2' ? 'nav-tab-active' : ''; ?>">✨ Novo Post V2</a>
				<a href="?page=blessflow-ai&tab=history" class="nav-tab <?php echo $active_tab == 'history' ? 'nav-tab-active' : ''; ?>">Histórico</a>
				<a href="?page=blessflow-ai&tab=settings" class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>">Configurações</a>
				<a href="?page=blessflow-ai&tab=help" class="nav-tab <?php echo $active_tab == 'help' ? 'nav-tab-active' : ''; ?>">Ajuda</a>
			</h2>

			<div class="blessflow-tab-content">
				<?php
				switch ( $active_tab ) {
					case 'dashboard':
						require_once BLESSFLOW_PLUGIN_DIR . 'templates/dashboard.php';
						break;
					case 'runner_v2':
						require_once BLESSFLOW_PLUGIN_DIR . 'templates/runner-v2.php';
						break;
					case 'history':
						require_once BLESSFLOW_PLUGIN_DIR . 'templates/history.php';
						break;
					case 'settings':
						require_once BLESSFLOW_PLUGIN_DIR . 'templates/settings.php';
						break;
					case 'help':
						require_once BLESSFLOW_PLUGIN_DIR . 'templates/help.php';
						break;
					default:
						require_once BLESSFLOW_PLUGIN_DIR . 'templates/dashboard.php';
						break;
				}
				?>
			</div>
		</div>
		<?php
	}
}
