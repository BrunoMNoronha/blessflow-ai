<div class="blessflow-settings">
    <h2>⚙️ Configurações</h2>
    
    <form method="post" action="options.php">
        <?php settings_fields( 'blessflow_options_group' ); ?>
        <?php do_settings_sections( 'blessflow_options_group' ); ?>
        
        <div class="blessflow-card">
            <h3 class="title">Google Gemini API</h3>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">API Key:</th>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <input type="password" name="blessflow_gemini_api_key" id="blessflow_gemini_api_key" value="<?php echo esc_attr( get_option('blessflow_gemini_api_key') ); ?>" class="regular-text" />
                            <button type="button" class="button button-secondary" onclick="testGeminiKey()">Testar Conexão</button>
                            <span id="gemini_status"></span>
                        </div>
                        <p class="description">Sua chave do Google AI Studio. <a href="https://aistudio.google.com/app/apikey" target="_blank">Gerar chave aqui</a></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Modelo:</th>
                    <td>
                        <input type="text" name="blessflow_gemini_model" value="<?php echo esc_attr( get_option('blessflow_gemini_model', 'gemini-1.5-flash') ); ?>" class="regular-text" />
                        <p class="description">Ex: <code>gemini-2.5-flash</code> ou <code>gemini-1.5-pro</code></p>
                    </td>
                </tr>
            </table>
        </div>

        <div class="blessflow-card" style="margin-top: 20px;">
            <h3 class="title">Unsplash API</h3>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Access Key:</th>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <input type="password" name="blessflow_unsplash_key" id="blessflow_unsplash_key" value="<?php echo esc_attr( get_option('blessflow_unsplash_key') ); ?>" class="regular-text" />
                            <button type="button" class="button button-secondary" onclick="testUnsplashKey()">Testar Conexão</button>
                            <span id="unsplash_status"></span>
                        </div>
                        <p class="description">Chave de acesso para busca de imagens. <a href="https://unsplash.com/developers" target="_blank">Gerar chave aqui</a></p>
                    </td>
                </tr>
            </table>
        </div>
        
        <?php submit_button(); ?>
    </form>
</div>
