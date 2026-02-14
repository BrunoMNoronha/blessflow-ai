jQuery(document).ready(function ($) {

    function log(msg, type = 'info') {
        const $console = $('#blessflow_debug_console');
        const $logBody = $('#blessflow_debug_log');

        $console.show();

        let color = '#d4d4d4';
        if (type === 'error') color = '#f44747';
        if (type === 'success') color = '#b5cea8';
        if (type === 'warn') color = '#ce9178';

        const time = new Date().toLocaleTimeString();
        $logBody.append(`<div><span style="color: #6a9955">[${time}]</span> <span style="color: ${color}">${msg}</span></div>`);
        $logBody.scrollTop($logBody[0].scrollHeight);
    }

    $('#blessflow_gerar_btn').on('click', function () {
        const btn = $(this);
        const topic = $('#blessflow_topico').val();
        const cat = $('#blessflow_categoria').val();
        const useImage = $('#blessflow_usar_imagem').is(':checked');
        const $resArea = $('#blessflow_resposta_area');
        const $loader = $('#blessflow_loader');
        const $btnTxt = $('#blessflow_btn_txt');

        if (!topic) {
            alert('Digite um tópico!');
            return;
        }

        btn.prop('disabled', true);
        $btnTxt.hide();
        $loader.show();
        $resArea.empty();
        $('#blessflow_debug_log').empty();

        log('Iniciando geração...', 'info');

        // Step 1: Generate Content
        $.ajax({
            url: blessflow_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'blessflow_generate_content', // Changed action
                nonce: blessflow_ajax.nonce,
                topico: topic,
                categoria: cat
            },
            success: function (response) {
                if (response.success) {
                    const data = response.data;

                    if (data.debug_logs) {
                        data.debug_logs.forEach(item => log(item.msg, item.type));
                    }

                    // Step 2: Generate Image (Optional)
                    if (useImage && data.image_keyword && data.post_id) {
                        log('Iniciando busca de imagem...', 'info');

                        $.ajax({
                            url: blessflow_ajax.ajax_url,
                            type: 'POST',
                            data: {
                                action: 'blessflow_generate_image',
                                nonce: blessflow_ajax.nonce,
                                post_id: data.post_id,
                                keyword: data.image_keyword
                            },
                            success: function (imgResp) {
                                if (imgResp.success) {
                                    if (imgResp.data.debug_logs) {
                                        imgResp.data.debug_logs.forEach(item => log(item.msg, item.type));
                                    }
                                } else {
                                    log('Erro na imagem: ' + imgResp.data, 'warn');
                                }
                            },
                            error: function () {
                                log('Erro de conexão ao buscar imagem.', 'warn');
                            },
                            complete: function () {
                                finishProcess(data);
                            }
                        });
                    } else {
                        if (!useImage) log('Pulo de etapa: Imagem desativada pelo usuário.', 'info');
                        finishProcess(data);
                    }

                } else {
                    log('Erro: ' + response.data, 'error');
                    $resArea.html(`<div class="error notice"><p>Erro: ${response.data}</p></div>`);
                    resetBtn();
                }
            },
            error: function (xhr, status, error) {
                log('Erro de comunicação: ' + error, 'error');
                $resArea.html(`<div class="error notice"><p>Falha na requisição AJAX.</p></div>`);
                resetBtn();
            }
        });

        function finishProcess(data) {
            log('Processo finalizado!', 'success');
            $resArea.html(`
                <div class="updated notice notice-success">
                    <p>Post criado: <strong>${data.titulo}</strong> <a href="${data.edit_link}" target="_blank">Editar</a></p>
                </div>
            `);
            resetBtn();
        }

        function resetBtn() {
            btn.prop('disabled', false);
            $btnTxt.show();
            $loader.hide();
        }
    });

    // Make functions globally available for onclick events
    window.testGeminiKey = function () {
        const apiKey = $('#blessflow_gemini_api_key').val();
        const $status = $('#gemini_status');

        if (!apiKey) {
            alert('Por favor, insira uma API Key.');
            return;
        }

        $status.text('⏳ Verificando...').css('color', 'orange');

        $.ajax({
            url: blessflow_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'blessflow_test_gemini',
                nonce: blessflow_ajax.nonce,
                api_key: apiKey
            },
            success: function (response) {
                if (response.success) {
                    $status.text('✅ ' + response.data).css('color', 'green');
                } else {
                    $status.text('❌ ' + response.data).css('color', 'red');
                }
            },
            error: function () {
                $status.text('❌ Erro de comunicação.').css('color', 'red');
            }
        });
    };

    window.testUnsplashKey = function () {
        const apiKey = $('#blessflow_unsplash_key').val();
        const $status = $('#unsplash_status');

        if (!apiKey) {
            alert('Por favor, insira uma Access Key.');
            return;
        }

        $status.text('⏳ Verificando...').css('color', 'orange');

        $.ajax({
            url: blessflow_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'blessflow_test_unsplash',
                nonce: blessflow_ajax.nonce,
                api_key: apiKey
            },
            success: function (response) {
                if (response.success) {
                    $status.text('✅ ' + response.data).css('color', 'green');
                } else {
                    $status.text('❌ ' + response.data).css('color', 'red');
                }
            },
            error: function () {
                $status.text('❌ Erro de comunicação.').css('color', 'red');
            }
        });
    };

    // V2 RUNNER LOGIC
    // -------------------------------------------------------------
    $('#btn_step_title').on('click', function () {
        const topic = $('#blessflow_v2_topic').val();
        if (!topic) return alert('Digite um tópico.');

        logV2('Gerando título...', 'info');

        $.post(blessflow_ajax.ajax_url, {
            action: 'blessflow_step_title',
            nonce: blessflow_ajax.nonce,
            topic: topic,
            model: $('#blessflow_v2_model').val(),
            language: $('#blessflow_v2_lang').val()
        }, function (res) {
            if (res.success) {
                const titles = res.data.options;
                if (titles && titles.length > 0) {
                    $('#blessflow_v2_title').val(titles[0]); // Pega o primeiro como default
                    logV2('Título gerado: ' + titles[0], 'success');
                }
            } else {
                logV2('Erro título: ' + res.data, 'error');
            }
        });
    });

    $('#btn_step_outline').on('click', function () {
        const topic = $('#blessflow_v2_topic').val();
        const title = $('#blessflow_v2_title').val();

        if (!title) return alert('Gere ou digite um título.');

        logV2('Gerando outline...', 'info');
        $('#step_outline_container').show();

        $.post(blessflow_ajax.ajax_url, {
            action: 'blessflow_step_outline',
            nonce: blessflow_ajax.nonce,
            topic: topic,
            title: title,
            num_sections: $('#blessflow_v2_num_sections').val(),
            model: $('#blessflow_v2_model').val(),
            language: $('#blessflow_v2_lang').val()
        }, function (res) {
            if (res.success) {
                const sections = res.data.sections;
                const $list = $('#blessflow_v2_outline_list');
                $list.empty();

                sections.forEach((sec, idx) => {
                    $list.append(`
                        <div class="outline-item" style="margin-bottom: 5px;">
                            <input type="text" class="widefat section-input" value="${sec}" data-id="${idx}">
                        </div>
                    `);
                });

                logV2('Outline gerado com ' + sections.length + ' seções.', 'success');
            } else {
                logV2('Erro outline: ' + res.data, 'error');
            }
        });
    });

    $('#btn_step_content').on('click', async function () {
        const $sections = $('.section-input');
        if ($sections.length === 0) return alert('Gere o outline primeiro.');

        logV2('Iniciando geração de conteúdo em lote...', 'info');
        $('#step_content_container').show();
        const $editor = $('#blessflow_v2_content_editor');
        $editor.empty();

        const title = $('#blessflow_v2_title').val();
        const total = $sections.length;

        // Loop sequencial para gerar seção por seção
        for (let i = 0; i < total; i++) {
            const sectionTitle = $($sections[i]).val();
            logV2(`Gerando seção ${i + 1}/${total}: ${sectionTitle}...`, 'warn');

            try {
                const res = await $.post(blessflow_ajax.ajax_url, {
                    action: 'blessflow_step_content',
                    nonce: blessflow_ajax.nonce,
                    section: sectionTitle,
                    title: title,
                    model: $('#blessflow_v2_model').val(),
                    language: $('#blessflow_v2_lang').val(),
                    temperature: $('#blessflow_v2_temp').val()
                });

                if (res.success) {
                    const content = res.data.text || res.data; // Pode vir texto puro ou obj
                    $editor.append(`<h3>${sectionTitle}</h3>`);
                    $editor.append(content);
                } else {
                    $editor.append(`<p style="color:red">[Erro na seção: ${sectionTitle}]</p>`);
                }
            } catch (e) {
                logV2('Erro de request na seção ' + (i + 1), 'error');
            }
        }

        logV2('Conteúdo completo gerado!', 'success');
    });

    $('#btn_save_post').on('click', function () {
        const title = $('#blessflow_v2_title').val();
        const content = $('#blessflow_v2_content_editor').html();

        if (!content) return alert('Gere o conteúdo antes de salvar.');

        logV2('Salvando post...', 'info');

        $.post(blessflow_ajax.ajax_url, {
            action: 'blessflow_save_post_v2',
            nonce: blessflow_ajax.nonce,
            title: title,
            content: content,
            meta_desc: $('#blessflow_v2_excerpt').val(),
            category: 1, // ToDo: Adicionar input de categoria na UI V2
        }, function (res) {
            if (res.success) {
                logV2('Post salvo com sucesso! ID: ' + res.data.post_id, 'success');
                alert('Post salvo com sucesso!');
            } else {
                logV2('Erro ao salvar: ' + res.data, 'error');
            }
        });
    });

    function logV2(msg, type) {
        const $logBody = $('#blessflow_v2_log_body');
        const color = type === 'error' ? 'red' : (type === 'success' ? 'green' : '#444');
        $logBody.append(`<div style="color:${color}">[${new Date().toLocaleTimeString()}] ${msg}</div>`);
        $logBody.scrollTop($logBody[0].scrollHeight);
    }

});
