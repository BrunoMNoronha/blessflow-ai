<div class="blessflow-history">
    <h2>📜 Histórico de Gerações</h2>
    <p>Visualize os posts gerados e seus custos estimados.</p>
    
    <div class="blessflow-card">
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Tópico</th>
                    <th>Tokens (In/Out)</th>
                    <th>Custo Est. (BRL)</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody id="blessflow-history-body">
                <?php
                if ( ! class_exists( 'BlessFlow_DB' ) ) {
                    require_once BLESSFLOW_PLUGIN_DIR . 'includes/class-blessflow-db.php';
                }
                $db = new BlessFlow_DB();
                $logs = $db->get_logs();

                if ( ! empty( $logs ) ) {
                    foreach ( $logs as $log ) {
                        $edit_link = get_edit_post_link( $log->post_id );
                        $status_class = $log->status === 'sucesso' ? 'status-ok' : 'status-err';
                        $data_fmt = date( 'd/m H:i', strtotime( $log->data_hora ) );
                        
                        echo "<tr>";
                        echo "<td>{$data_fmt}</td>";
                        echo "<td>" . esc_html( $log->topico ) . "</td>";
                        echo "<td>In: {$log->tokens_input} / Out: {$log->tokens_output}</td>";
                        echo "<td>R$ " . number_format( $log->custo_estimado, 4, ',', '.' ) . "</td>";
                        echo "<td><span class='{$status_class}'>" . ucfirst( $log->status ) . "</span></td>";
                        echo "<td>";
                        if ( $log->post_id ) {
                            echo "<a href='{$edit_link}' target='_blank' class='button button-small'>Editar</a>";
                        }
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>Nenhum histórico encontrado.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
