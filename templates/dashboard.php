<div class="blessflow-dashboard">
    <div class="blessflow-header">
        <h2>🚀 Runner Manual</h2>
        <p>Gere artigos otimizados para SEO usando a inteligência do Google Gemini 2.5 Flash.</p>
    </div>

    <div class="blessflow-grid">
        <!-- Card de Status -->
        <div class="blessflow-card">
            <h3>Status do Sistema</h3>
            <ul>
                <li><strong>Modelo:</strong> <?php echo esc_html( get_option( 'blessflow_gemini_model', 'gemini-1.5-flash' ) ); ?></li>
                <li><strong>Unsplash:</strong> <?php echo get_option( 'blessflow_unsplash_key' ) ? '<span class="status-ok">Conectado</span>' : '<span class="status-err">Desconectado</span>'; ?></li>
                <li><strong>Ambiente:</strong> <?php echo wp_get_environment_type(); ?></li>
            </ul>
        </div>
    </div>
</div>
