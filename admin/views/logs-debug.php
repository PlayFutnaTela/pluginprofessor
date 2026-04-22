<div class="wrap">
    <h1><?php _e('Logs e Debug - Painel Professor', 'sm-student-control'); ?></h1>

    <div class="sm-sc-debug-dashboard">
        <!-- Informações do Sistema -->
        <div class="sm-sc-system-info">
            <h3><?php _e('Informações do Sistema', 'sm-student-control'); ?></h3>
            <table class="widefat">
                <tbody>
                    <tr>
                        <td><strong><?php _e('Versão do Plugin', 'sm-student-control'); ?>:</strong></td>
                        <td><?php echo SM_STUDENT_CONTROL_VERSION; ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('Versão do WordPress', 'sm-student-control'); ?>:</strong></td>
                        <td><?php echo get_bloginfo('version'); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('Versão do PHP', 'sm-student-control'); ?>:</strong></td>
                        <td><?php echo PHP_VERSION; ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('Memória Limite', 'sm-student-control'); ?>:</strong></td>
                        <td><?php echo ini_get('memory_limit'); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('Tempo Limite de Execução', 'sm-student-control'); ?>:</strong></td>
                        <td><?php echo ini_get('max_execution_time'); ?>s</td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('Extensões PHP', 'sm-student-control'); ?>:</strong></td>
                        <td><?php echo implode(', ', get_loaded_extensions()); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Status das Configurações -->
        <div class="sm-sc-config-status">
            <h3><?php _e('Status das Configurações', 'sm-student-control'); ?></h3>
            <div id="config-status-content">
                <div class="sm-sc-loading"><?php _e('Verificando configurações...', 'sm-student-control'); ?></div>
            </div>
        </div>

        <!-- Testes de Conectividade -->
        <div class="sm-sc-connectivity-tests">
            <h3><?php _e('Testes de Conectividade', 'sm-student-control'); ?></h3>

            <div class="sm-sc-test-buttons">
                <button type="button" id="test-database" class="button button-secondary">
                    <span class="dashicons dashicons-database"></span>
                    <?php _e('Testar Banco de Dados', 'sm-student-control'); ?>
                </button>

                <button type="button" id="test-api-connection" class="button button-secondary">
                    <span class="dashicons dashicons-rest-api"></span>
                    <?php _e('Testar API Externa', 'sm-student-control'); ?>
                </button>

                <button type="button" id="test-jwt-token" class="button button-secondary">
                    <span class="dashicons dashicons-lock"></span>
                    <?php _e('Testar JWT', 'sm-student-control'); ?>
                </button>

                <button type="button" id="check-lms-tables" class="button button-secondary">
                    <span class="dashicons dashicons-list-view"></span>
                    <?php _e('Verificar Tabelas LMS', 'sm-student-control'); ?>
                </button>
            </div>

            <div id="test-results" class="sm-sc-test-results" style="display: none;">
                <h4><?php _e('Resultados dos Testes', 'sm-student-control'); ?>:</h4>
                <div id="test-output"></div>
            </div>
        </div>

        <!-- Visualizador de Logs -->
        <div class="sm-sc-log-viewer">
            <h3><?php _e('Logs de Debug', 'sm-student-control'); ?></h3>

            <div class="sm-sc-log-controls">
                <div class="log-filters">
                    <select id="log-level">
                        <option value=""><?php _e('Todos os níveis', 'sm-student-control'); ?></option>
                        <option value="ERROR"><?php _e('Erro', 'sm-student-control'); ?></option>
                        <option value="WARNING"><?php _e('Aviso', 'sm-student-control'); ?></option>
                        <option value="INFO"><?php _e('Info', 'sm-student-control'); ?></option>
                        <option value="DEBUG"><?php _e('Debug', 'sm-student-control'); ?></option>
                    </select>

                    <input type="date" id="log-date" placeholder="<?php _e('Data', 'sm-student-control'); ?>">

                    <input type="text" id="log-search" placeholder="<?php _e('Buscar...', 'sm-student-control'); ?>" class="regular-text">
                </div>

                <div class="log-actions">
                    <button type="button" id="refresh-logs" class="button button-secondary">
                        <span class="dashicons dashicons-update"></span>
                        <?php _e('Atualizar', 'sm-student-control'); ?>
                    </button>

                    <button type="button" id="clear-logs" class="button button-secondary">
                        <span class="dashicons dashicons-trash"></span>
                        <?php _e('Limpar Logs', 'sm-student-control'); ?>
                    </button>

                    <button type="button" id="export-logs" class="button button-secondary">
                        <span class="dashicons dashicons-download"></span>
                        <?php _e('Exportar', 'sm-student-control'); ?>
                    </button>
                </div>
            </div>

            <div id="logs-content" class="sm-sc-logs-content">
                <div class="sm-sc-loading"><?php _e('Carregando logs...', 'sm-student-control'); ?></div>
            </div>

            <div class="sm-sc-log-pagination" id="logs-pagination" style="display: none;">
                <!-- Paginação será inserida aqui -->
            </div>
        </div>

        <!-- Ações de Debug -->
        <div class="sm-sc-debug-actions">
            <h3><?php _e('Ações de Debug', 'sm-student-control'); ?></h3>

            <div class="debug-options">
                <div class="debug-option">
                    <label>
                        <input type="checkbox" id="enable-debug-mode" <?php checked(SM_Student_Control_Settings::get('debug_mode'), true); ?>>
                        <?php _e('Habilitar modo debug', 'sm-student-control'); ?>
                    </label>
                    <p class="description"><?php _e('Registra informações detalhadas sobre operações do plugin.', 'sm-student-control'); ?></p>
                </div>

                <div class="debug-option">
                    <label>
                        <input type="checkbox" id="log-api-calls" <?php checked(SM_Student_Control_Settings::get('log_api_calls'), true); ?>>
                        <?php _e('Logar chamadas de API', 'sm-student-control'); ?>
                    </label>
                    <p class="description"><?php _e('Registra todas as requisições para APIs externas.', 'sm-student-control'); ?></p>
                </div>

                <div class="debug-option">
                    <label>
                        <input type="checkbox" id="log-database-queries" <?php checked(SM_Student_Control_Settings::get('log_database_queries'), true); ?>>
                        <?php _e('Logar queries de banco', 'sm-student-control'); ?>
                    </label>
                    <p class="description"><?php _e('Registra todas as queries executadas no banco de dados.', 'sm-student-control'); ?></p>
                </div>
            </div>

            <div class="debug-actions">
                <button type="button" id="save-debug-settings" class="button button-primary">
                    <?php _e('Salvar Configurações', 'sm-student-control'); ?>
                </button>

                <button type="button" id="run-diagnostics" class="button button-secondary">
                    <?php _e('Executar Diagnóstico Completo', 'sm-student-control'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Carregar status das configurações
    loadConfigStatus();

    // Testes de conectividade
    $('#test-database').on('click', function() {
        runTest('database');
    });

    $('#test-api-connection').on('click', function() {
        runTest('api');
    });

    $('#test-jwt-token').on('click', function() {
        runTest('jwt');
    });

    $('#check-lms-tables').on('click', function() {
        runTest('lms_tables');
    });

    // Controles de log
    $('#refresh-logs').on('click', function() {
        loadLogs();
    });

    $('#clear-logs').on('click', function() {
        if (confirm('<?php _e('Tem certeza que deseja limpar todos os logs?', 'sm-student-control'); ?>')) {
            clearLogs();
        }
    });

    $('#export-logs').on('click', function() {
        exportLogs();
    });

    // Filtros de log
    $('#log-level, #log-date, #log-search').on('change input', function() {
        loadLogs();
    });

    // Salvar configurações de debug
    $('#save-debug-settings').on('click', function() {
        saveDebugSettings();
    });

    // Executar diagnóstico
    $('#run-diagnostics').on('click', function() {
        runDiagnostics();
    });

    function runTest(testType) {
        var button = $('#test-' + testType.replace('_', '-'));
        var originalText = button.html();

        button.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> <?php _e('Testando...', 'sm-student-control'); ?>');

        $.post(ajaxurl, {
            action: 'sm_sc_run_test',
            nonce: smscAdmin.nonce,
            test_type: testType
        })
        .done(function(response) {
            displayTestResult(testType, response);
        })
        .fail(function() {
            displayTestResult(testType, {success: false, error: '<?php _e('Erro na requisição', 'sm-student-control'); ?>'});
        })
        .always(function() {
            button.prop('disabled', false).html(originalText);
        });
    }

    function displayTestResult(testType, result) {
        $('#test-results').show();
        var output = $('#test-output');

        var testName = getTestName(testType);
        var html = '<div class="sm-sc-test-result ' + (result.success ? 'success' : 'error') + '">';
        html += '<h4>' + testName + ': ' + (result.success ? '<?php _e('Sucesso', 'sm-student-control'); ?>' : '<?php _e('Erro', 'sm-student-control'); ?>') + '</h4>';

        if (result.success) {
            html += '<p>' + (result.message || '<?php _e('Teste executado com sucesso', 'sm-student-control'); ?>') + '</p>';
            if (result.data) {
                html += '<pre>' + JSON.stringify(result.data, null, 2) + '</pre>';
            }
        } else {
            html += '<p class="error">' + (result.error || '<?php _e('Erro desconhecido', 'sm-student-control'); ?>') + '</p>';
        }

        html += '</div>';

        if (output.find('.sm-sc-test-result').length === 0) {
            output.html(html);
        } else {
            output.append(html);
        }

        // Scroll para o resultado
        output[0].scrollIntoView({ behavior: 'smooth' });
    }

    function getTestName(testType) {
        var names = {
            'database': '<?php _e('Banco de Dados', 'sm-student-control'); ?>',
            'api': '<?php _e('API Externa', 'sm-student-control'); ?>',
            'jwt': '<?php _e('JWT', 'sm-student-control'); ?>',
            'lms_tables': '<?php _e('Tabelas LMS', 'sm-student-control'); ?>'
        };
        return names[testType] || testType;
    }

    function loadConfigStatus() {
        $.post(ajaxurl, {
            action: 'sm_sc_get_config_status',
            nonce: smscAdmin.nonce
        })
        .done(function(response) {
            if (response.success) {
                displayConfigStatus(response.data);
            } else {
                $('#config-status-content').html('<div class="error"><?php _e('Erro ao carregar status', 'sm-student-control'); ?></div>');
            }
        })
        .fail(function() {
            $('#config-status-content').html('<div class="error"><?php _e('Erro na requisição', 'sm-student-control'); ?></div>');
        });
    }

    function displayConfigStatus(status) {
        var html = '<div class="sm-sc-status-grid">';

        // JWT
        html += '<div class="status-item ' + (status.jwt_configured ? 'success' : 'error') + '">';
        html += '<span class="dashicons ' + (status.jwt_configured ? 'dashicons-yes' : 'dashicons-no') + '"></span>';
        html += '<span><?php _e('JWT Configurado', 'sm-student-control'); ?></span>';
        html += '</div>';

        // API
        html += '<div class="status-item ' + (status.api_configured ? 'success' : 'error') + '">';
        html += '<span class="dashicons ' + (status.api_configured ? 'dashicons-yes' : 'dashicons-no') + '"></span>';
        html += '<span><?php _e('API Externa', 'sm-student-control'); ?></span>';
        html += '</div>';

        // Cache
        html += '<div class="status-item ' + (status.cache_enabled ? 'success' : 'warning') + '">';
        html += '<span class="dashicons ' + (status.cache_enabled ? 'dashicons-yes' : 'dashicons-warning') + '"></span>';
        html += '<span><?php _e('Cache Habilitado', 'sm-student-control'); ?></span>';
        html += '</div>';

        // LMS Tables
        html += '<div class="status-item ' + (status.lms_tables_exist ? 'success' : 'error') + '">';
        html += '<span class="dashicons ' + (status.lms_tables_exist ? 'dashicons-yes' : 'dashicons-no') + '"></span>';
        html += '<span><?php _e('Tabelas LMS', 'sm-student-control'); ?></span>';
        html += '</div>';

        html += '</div>';

        $('#config-status-content').html(html);
    }

    function loadLogs(page = 1) {
        var level = $('#log-level').val();
        var date = $('#log-date').val();
        var search = $('#log-search').val();

        $('#logs-content').html('<div class="sm-sc-loading"><?php _e('Carregando logs...', 'sm-student-control'); ?></div>');

        $.post(ajaxurl, {
            action: 'sm_sc_get_logs',
            nonce: smscAdmin.nonce,
            level: level,
            date: date,
            search: search,
            page: page
        })
        .done(function(response) {
            if (response.success) {
                displayLogs(response.data.logs, response.data.pagination);
            } else {
                $('#logs-content').html('<div class="error"><?php _e('Erro ao carregar logs', 'sm-student-control'); ?></div>');
            }
        })
        .fail(function() {
            $('#logs-content').html('<div class="error"><?php _e('Erro na requisição', 'sm-student-control'); ?></div>');
        });
    }

    function displayLogs(logs, pagination) {
        if (!logs || logs.length === 0) {
            $('#logs-content').html('<p><?php _e('Nenhum log encontrado.', 'sm-student-control'); ?></p>');
            $('#logs-pagination').hide();
            return;
        }

        var html = '<table class="widefat striped">';
        html += '<thead>';
        html += '<tr>';
        html += '<th><?php _e('Data/Hora', 'sm-student-control'); ?></th>';
        html += '<th><?php _e('Nível', 'sm-student-control'); ?></th>';
        html += '<th><?php _e('Mensagem', 'sm-student-control'); ?></th>';
        html += '</tr>';
        html += '</thead>';
        html += '<tbody>';

        logs.forEach(function(log) {
            var levelClass = 'log-level-' + log.level.toLowerCase();
            html += '<tr>';
            html += '<td>' + log.timestamp + '</td>';
            html += '<td><span class="log-level ' + levelClass + '">' + log.level + '</span></td>';
            html += '<td>' + log.message + '</td>';
            html += '</tr>';
        });

        html += '</tbody>';
        html += '</table>';

        $('#logs-content').html(html);

        // Configurar paginação se necessário
        if (pagination && pagination.total_pages > 1) {
            displayPagination(pagination);
            $('#logs-pagination').show();
        } else {
            $('#logs-pagination').hide();
        }
    }

    function displayPagination(pagination) {
        var html = '<div class="tablenav"><div class="tablenav-pages">';

        if (pagination.current_page > 1) {
            html += '<a href="#" class="prev-page" data-page="' + (pagination.current_page - 1) + '">&laquo; <?php _e('Anterior', 'sm-student-control'); ?></a>';
        }

        html += '<span class="paging-input">';
        html += '<span class="total-pages"><?php _e('Página', 'sm-student-control'); ?> ' + pagination.current_page + ' <?php _e('de', 'sm-student-control'); ?> ' + pagination.total_pages + '</span>';
        html += '</span>';

        if (pagination.current_page < pagination.total_pages) {
            html += '<a href="#" class="next-page" data-page="' + (pagination.current_page + 1) + '"><?php _e('Próximo', 'sm-student-control'); ?> &raquo;</a>';
        }

        html += '</div></div>';

        $('#logs-pagination').html(html);

        // Eventos da paginação
        $('#logs-pagination .prev-page, #logs-pagination .next-page').on('click', function(e) {
            e.preventDefault();
            var page = $(this).data('page');
            loadLogs(page);
        });
    }

    function clearLogs() {
        $.post(ajaxurl, {
            action: 'sm_sc_clear_logs',
            nonce: smscAdmin.nonce
        })
        .done(function(response) {
            if (response.success) {
                loadLogs();
                alert('<?php _e('Logs limpos com sucesso!', 'sm-student-control'); ?>');
            } else {
                alert(response.error || '<?php _e('Erro ao limpar logs', 'sm-student-control'); ?>');
            }
        });
    }

    function exportLogs() {
        var level = $('#log-level').val();
        var date = $('#log-date').val();

        var url = ajaxurl + '?action=sm_sc_export_logs&nonce=' + smscAdmin.nonce;
        if (level) url += '&level=' + level;
        if (date) url += '&date=' + date;

        window.open(url, '_blank');
    }

    function saveDebugSettings() {
        var debugMode = $('#enable-debug-mode').is(':checked');
        var logApiCalls = $('#log-api-calls').is(':checked');
        var logDbQueries = $('#log-database-queries').is(':checked');

        $.post(ajaxurl, {
            action: 'sm_sc_save_debug_settings',
            nonce: smscAdmin.nonce,
            debug_mode: debugMode,
            log_api_calls: logApiCalls,
            log_database_queries: logDbQueries
        })
        .done(function(response) {
            if (response.success) {
                alert('<?php _e('Configurações salvas com sucesso!', 'sm-student-control'); ?>');
            } else {
                alert(response.error || '<?php _e('Erro ao salvar configurações', 'sm-student-control'); ?>');
            }
        });
    }

    function runDiagnostics() {
        $('#run-diagnostics').prop('disabled', true).text('<?php _e('Executando...', 'sm-student-control'); ?>');

        $.post(ajaxurl, {
            action: 'sm_sc_run_diagnostics',
            nonce: smscAdmin.nonce
        })
        .done(function(response) {
            $('#run-diagnostics').prop('disabled', false).text('<?php _e('Executar Diagnóstico Completo', 'sm-student-control'); ?>');

            if (response.success) {
                $('#test-results').show();
                $('#test-output').html('<div class="sm-sc-test-result success"><h4><?php _e('Diagnóstico Completo', 'sm-student-control'); ?>:</h4><pre>' + JSON.stringify(response.data, null, 2) + '</pre></div>');
            } else {
                $('#test-results').show();
                $('#test-output').html('<div class="sm-sc-test-result error"><h4><?php _e('Erro no Diagnóstico', 'sm-student-control'); ?>:</h4><p>' + response.error + '</p></div>');
            }
        });
    }

    // Carregar logs inicialmente
    loadLogs();
});
</script>