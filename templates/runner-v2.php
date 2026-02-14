<div class="blessflow-runner-v2">
    <h2>✨ Novo Post com IA (V2)</h2>
    
    <div class="blessflow-grid-v2">
        
        <!-- Coluna 1: Inputs de Tópico -->
        <div class="blessflow-card blessflow-col-1">
            <h3>1. Tópico & Modelo</h3>
            
            <label for="blessflow_v2_model" class="label-heading">Modelo</label>
            <select id="blessflow_v2_model" class="widefat">
                <option value="gemini-2.5-flash">Gemini 2.5 Flash</option>
            </select>
            
            <label for="blessflow_v2_category" class="label-heading" style="margin-top: 15px;">Categoria</label>
            <select id="blessflow_v2_category" class="widefat">
                <?php
                $categories = get_categories( array( 'hide_empty' => 0 ) );
                foreach ( $categories as $category ) {
                    echo '<option value="' . esc_attr( $category->term_id ) . '">' . esc_html( $category->name ) . '</option>';
                }
                ?>
            </select>


            <label for="blessflow_v2_topic" class="label-heading" style="margin-top: 15px;">Tópico</label>
            <textarea id="blessflow_v2_topic" class="widefat" rows="5" placeholder="Ex: Guia completo sobre Python para iniciantes"></textarea>
c:\Users\Notebook.gemini\antigravity\playground\quantum-protostar\session_state.md
.

Session State

            <button type="button" id="btn_step_title" class="button button-primary button-large" style="width: 100%; margin-top: 20px;">
                Gerar Título &rarr;
            </button>
        </div>

        <!-- Coluna 2: Staging Area (Onde a mágica acontece) -->
        <div class="blessflow-card blessflow-col-2">
            <h3>2. Construção do Post</h3>

            <!-- Passo A: Título -->
            <div id="step_title_container">
                <label class="label-heading">Título</label>
                <input type="text" id="blessflow_v2_title" class="widefat" placeholder="O título aparecerá aqui...">
                <div class="step-actions">
                    <label>Nº Seções: <input type="number" id="blessflow_v2_num_sections" value="5" min="2" max="10" style="width: 60px;"></label>
                    <button type="button" id="btn_step_outline" class="button button-secondary">Gerar Seções &rarr;</button>
                </div>
            </div>

            <!-- Passo B: Seções (Outline) -->
            <div id="step_outline_container" style="margin-top: 20px; display: none;">
                <label class="label-heading">Seções (Outline)</label>
                <div id="blessflow_v2_outline_list"></div>
                <p class="description">Edite, remova ou reordene as seções antes de gerar o conteúdo.</p>
                <div class="step-actions">
                    <label>Parágrafos/Seção: <input type="number" id="blessflow_v2_num_paragraphs" value="2" min="1" max="5" style="width: 60px;"></label>
                    <button type="button" id="btn_step_content" class="button button-secondary">Gerar Conteúdo &rarr;</button>
                </div>
            </div>

            <!-- Passo C: Conteúdo Full -->
            <div id="step_content_container" style="margin-top: 20px; display: none;">
                <label class="label-heading">Conteúdo Final</label>
                <div id="blessflow_v2_content_editor" style="min-height: 300px; border: 1px solid #ddd; padding: 10px; background: #fff;"></div>
                
                <label class="label-heading" style="margin-top: 15px;">Resumo / Meta Description</label>
                <textarea id="blessflow_v2_excerpt" class="widefat" rows="2"></textarea>

                <div class="final-actions" style="margin-top: 20px; text-align: right;">
                    <button type="button" id="btn_save_post" class="button button-primary button-large">💾 Criar Post no WordPress</button>
                </div>
            </div>
        </div>

        <!-- Coluna 3: Configurações & Prompts -->
        <div class="blessflow-card blessflow-col-3">
            <h3>3. Configurações</h3>

            <div class="config-section">
                <label class="label-heading">Idioma</label>
                <select id="blessflow_v2_lang" class="widefat">
                    <option value="pt-BR">Português (BR)</option>
                    <option value="en-US">English (US)</option>
                    <option value="es-ES">Español</option>
                </select>
            </div>

            <div class="config-section" style="margin-top: 15px;">
                <label class="label-heading">Temperatura (Criatividade)</label>
                <input type="range" id="blessflow_v2_temp" min="0" max="1" step="0.1" value="0.7" oninput="document.getElementById('temp_val').innerText = this.value">
                <span id="temp_val" style="font-weight: bold;">0.7</span>
            </div>

            <hr>

            <div class="accordion-section">
                <h4 style="cursor: pointer;" onclick="jQuery('#prompt_title_config').toggle()">📝 Prompt: Título</h4>
                <div id="prompt_title_config" style="display:none;">
                    <textarea class="code widefat" rows="3">Write a title for an article regarding {topic} in {language}.</textarea>
                </div>
            </div>

            <div class="accordion-section">
                <h4 style="cursor: pointer;" onclick="jQuery('#prompt_outline_config').toggle()">📑 Prompt: Seções</h4>
                <div id="prompt_outline_config" style="display:none;">
                    <textarea class="code widefat" rows="3">Create an outline with {num_sections} sections for "{title}".</textarea>
                </div>
            </div>
            
             <!-- Console de Logs Local -->
            <div id="blessflow_v2_console" class="blessflow-console" style="margin-top: 20px; max-height: 200px;">
                <div class="console-header">Log de Execução</div>
                <div id="blessflow_v2_log_body" class="console-body"></div>
            </div>
        </div>
    </div>
</div>

<style>
/* CSS Inline for V2 Prototype */
.blessflow-grid-v2 {
    display: grid;
    grid-template-columns: 250px 1fr 300px;
    gap: 20px;
    margin-top: 20px;
    align-items: start;
}

@media (max-width: 1200px) {
    .blessflow-grid-v2 {
        grid-template-columns: 1fr;
    }
}

.label-heading {
    font-weight: 600;
    display: block;
    margin-bottom: 5px;
    color: #2c3338;
}

.step-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 10px;
    background: #f0f0f1;
    padding: 10px;
    border-radius: 4px;
}

.blessflow-col-2 {
    min-height: 600px;
}
</style>
