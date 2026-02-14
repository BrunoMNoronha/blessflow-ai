<div class="blessflow-runner">
    <h2>✨ Novo Post com IA</h2>
    
    <div class="blessflow-grid">
        <!-- Card de Geração (Runner) -->
        <div class="blessflow-card">
            <h3>Gerador de Conteúdo</h3>
            
            <label for="blessflow_topico">Tópico ou Palavra-Chave:</label>
            <input type="text" id="blessflow_topico" class="widefat" placeholder="Ex: Guia completo sobre Python para iniciantes">
            
            <label for="blessflow_categoria" style="margin-top: 15px; display: block;">Categoria:</label>
            <?php
            $args = array(
                'show_option_none'   => 'Selecione uma categoria',
                'option_none_value'  => '',
                'orderby'            => 'name',
                'order'              => 'ASC',
                'show_count'         => 1,
                'hide_empty'         => 0,
                'child_of'           => 0,
                'exclude'            => '',
                'echo'               => 1,
                'selected'           => 0,
                'hierarchical'       => 1,
                'name'               => 'blessflow_categoria',
                'id'                 => 'blessflow_categoria',
                'class'              => 'widefat',
                'depth'              => 0,
                'tab_index'          => 0,
                'taxonomy'           => 'category',
                'hide_if_empty'      => false,
            );
            wp_dropdown_categories( $args );
            ?>

            <div style="margin-top: 15px;">
                <label>
                    <input type="checkbox" id="blessflow_usar_imagem" checked> Buscar Imagem de Capa (Unsplash)
                </label>
            </div>

            <div class="blessflow-actions" style="margin-top: 20px;">
                <button type="button" id="blessflow_gerar_btn" class="button button-primary button-large" style="width: 100%;">
                    <span id="blessflow_btn_txt">✨ Gerar Artigo</span>
                    <span id="blessflow_loader" style="display:none;">⏳ Processando...</span>
                </button>
            </div>
        </div>

        <!-- Card de Configuração de Prompts -->
        <div class="blessflow-card">
            <h3 class="title">Configuração de Prompts</h3>
            <form method="post" action="options.php">
                <?php 
                    // Render hidden fields for all registered settings in this group
                    settings_fields( 'blessflow_options_group' ); 
                    // Output options for just this section isn't quite how do_settings_sections works usually, 
                    // but since we are manually building the form, we just include the fields.
                    // However, options.php expects all fields in the group to be present or it might unset them if not careful? 
                    // Actually, WordPress options API handles updating only sent fields usually, but settings_fields outputs nonces.
                    // We need to make sure we don't accidentally overwrite other settings with nulls if they are in the same group but not in this form.
                    // `register_setting` sanitization callbacks are important here. 
                    // For now, assuming standard WP behavior where only submitted fields are updated.
                ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">System Instruction:</th>
                        <td>
                            <textarea name="blessflow_system_instruction" rows="5" class="large-text code"><?php echo esc_textarea( get_option('blessflow_system_instruction') ); ?></textarea>
                            <p class="description">Define o comportamento do IA. Ex: "Você é um especialista em SEO...". Deixe em branco para usar o padrão.</p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Template de Prompt:</th>
                        <td>
                            <textarea name="blessflow_prompt_template" rows="3" class="large-text code"><?php echo esc_textarea( get_option('blessflow_prompt_template') ); ?></textarea>
                            <p class="description">Use <code>{topic}</code> onde o usuário inserirá o tópico. Ex: "Escreva um artigo sobre: {topic}".</p>
                        </td>
                    </tr>
                </table>
                <?php submit_button('Salvar Configurações de Prompt'); ?>
            </form>
        </div>
    </div>

    <!-- Console de Logs -->
    <div id="blessflow_debug_console" class="blessflow-console" style="display:none; margin-top: 20px;">
        <div class="console-header">💻 BlessFlow Terminal</div>
        <div id="blessflow_debug_log" class="console-body"></div>
    </div>

    <div id="blessflow_resposta_area" style="margin-top: 20px;"></div>
</div>
