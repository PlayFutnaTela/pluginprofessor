<div class="wrap">
    <h1><?php _e('Gerenciamento de Cache - Painel Professor', 'sm-student-control'); ?></h1>

    <div class="sm-sc-cache-dashboard">
        <!-- Estatísticas do Cache -->
        <div class="sm-sc-cache-stats">
            <h3><?php _e('Estatísticas do Cache', 'sm-student-control'); ?></h3>
            <div id="cache-stats-content">
                <div class="sm-sc-loading"><?php _e('Carregando estatísticas...', 'sm-student-control'); ?></div>
            </div>
        </div>

        <!-- Ações de Cache -->
        <div class="sm-sc-cache-actions">
            <h3><?php _e('Ações de Cache', 'sm-student-control'); ?></h3>

            <div class="sm-sc-action-buttons">
                <button type="button" id="refresh-all-cache" class="button button-primary button-large">
                    <span class="dashicons dashicons-update"></span>
                    <?php _e('Atualizar Cache de Todos os Alunos', 'sm-student-control'); ?>
                </button>

                <button type="button" id="clear-all-cache" class="button button-secondary button-large">
                    <span class="dashicons dashicons-trash"></span>
                    <?php _e('Limpar Todo o Cache', 'sm-student-control'); ?>
                </button>
            </div>

            <div class="sm-sc-cache-form">
                <h4><?php _e('Atualizar Cache Específico', 'sm-student-control'); ?></h4>
                <form id="refresh-student-cache-form">
                    <div class="form-row">
                        <label for="student-id"><?php _e('ID do Aluno:', 'sm-student-control'); ?></label>
                        <input type="number" id="student-id" name="student_id" min="1" placeholder="123">
                        <button type="submit" class="button button-secondary">
                            <?php _e('Atualizar', 'sm-student-control'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Barra de Progresso -->
        <div id="cache-progress-container" style="display: none;">
            <h3><?php _e('Progresso da Operação', 'sm-student-control'); ?></h3>
            <div class="sm-sc-progress-bar">
                <div class="sm-sc-progress-fill" id="progress-fill" style="width: 0%;"></div>
            </div>
            <div class="sm-sc-progress-text" id="progress-text">
                <?php _e('Iniciando...', 'sm-student-control'); ?>
            </div>
        </div>

        <!-- Lista de Alunos em Cache -->
        <div class="sm-sc-cached-students">
            <h3><?php _e('Alunos em Cache', 'sm-student-control'); ?></h3>

            <div class="sm-sc-filters">
                <input type="text" id="student-search" placeholder="<?php _e('Buscar aluno...', 'sm-student-control'); ?>" class="regular-text">
                <select id="school-filter">
                    <option value=""><?php _e('Todas as escolas', 'sm-student-control'); ?></option>
                    <!-- Opções serão carregadas dinamicamente -->
                </select>
            </div>

            <div id="cached-students-table">
                <div class="sm-sc-loading"><?php _e('Carregando alunos...', 'sm-student-control'); ?></div>
            </div>

            <div class="sm-sc-pagination" id="students-pagination" style="display: none;">
                <!-- Paginação será inserida aqui -->
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação -->
<div id="sm-sc-confirm-modal" class="sm-sc-modal" style="display: none;">
    <div class="sm-sc-modal-overlay"></div>
    <div class="sm-sc-modal-content">
        <h3><?php _e('Confirmar Ação', 'sm-student-control'); ?></h3>
        <p id="modal-message"></p>
        <div class="sm-sc-modal-buttons">
            <button type="button" id="modal-cancel" class="button"><?php _e('Cancelar', 'sm-student-control'); ?></button>
            <button type="button" id="modal-confirm" class="button button-primary"><?php _e('Confirmar', 'sm-student-control'); ?></button>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    var currentAction = null;
    var progressInterval = null;

    // Carregar estatísticas do cache
    loadCacheStats();

    // Atualizar cache de todos
    $('#refresh-all-cache').on('click', function() {
        confirmAction(
            '<?php _e('Tem certeza que deseja atualizar o cache de todos os alunos? Esta operação pode demorar alguns minutos.', 'sm-student-control'); ?>',
            'refresh_all_cache'
        );
    });

    // Limpar todo cache
    $('#clear-all-cache').on('click', function() {
        confirmAction(
            '<?php _e('Tem certeza que deseja limpar todo o cache? Os dados serão recarregados da API externa ou banco de dados.', 'sm-student-control'); ?>',
            'clear_all_cache'
        );
    });

    // Atualizar cache de aluno específico
    $('#refresh-student-cache-form').on('submit', function(e) {
        e.preventDefault();
        var studentId = $('#student-id').val();

        if (!studentId) {
            alert('<?php _e('Por favor, informe o ID do aluno.', 'sm-student-control'); ?>');
            return;
        }

        executeAction('refresh_student_cache', { student_id: studentId });
    });

    // Busca de alunos
    $('#student-search').on('input', function() {
        loadCachedStudents();
    });

    // Filtro por escola
    $('#school-filter').on('change', function() {
        loadCachedStudents();
    });

    function confirmAction(message, action) {
        $('#modal-message').text(message);
        currentAction = action;
        $('#sm-sc-confirm-modal').show();
    }

    $('#modal-cancel').on('click', function() {
        $('#sm-sc-confirm-modal').hide();
        currentAction = null;
    });

    $('#modal-confirm').on('click', function() {
        $('#sm-sc-confirm-modal').hide();
        executeAction(currentAction);
        currentAction = null;
    });

    function executeAction(action, params = {}) {
        showProgress('<?php _e('Executando operação...', 'sm-student-control'); ?>');

        $.post(ajaxurl, {
            action: 'sm_sc_' + action,
            nonce: smscAdmin.nonce,
            ...params
        })
        .done(function(response) {
            if (response.success) {
                showProgress('<?php _e('Operação concluída com sucesso!', 'sm-student-control'); ?>', 100);
                setTimeout(function() {
                    hideProgress();
                    loadCacheStats();
                    if (action.includes('student') || action.includes('all')) {
                        loadCachedStudents();
                    }
                }, 2000);
            } else {
                showProgress(response.error || '<?php _e('Erro na operação', 'sm-student-control'); ?>', 0);
                setTimeout(hideProgress, 3000);
            }
        })
        .fail(function() {
            showProgress('<?php _e('Erro na requisição', 'sm-student-control'); ?>', 0);
            setTimeout(hideProgress, 3000);
        });
    }

    function showProgress(text, percent = 0) {
        $('#cache-progress-container').show();
        $('#progress-text').text(text);
        $('#progress-fill').css('width', percent + '%');
    }

    function hideProgress() {
        $('#cache-progress-container').hide();
        $('#progress-fill').css('width', '0%');
        $('#progress-text').text('');
    }

    function loadCacheStats() {
        $.post(ajaxurl, {
            action: 'sm_sc_get_cache_stats',
            nonce: smscAdmin.nonce
        })
        .done(function(response) {
            if (response.success) {
                displayCacheStats(response.data);
            } else {
                $('#cache-stats-content').html('<div class="error"><?php _e('Erro ao carregar estatísticas', 'sm-student-control'); ?></div>');
            }
        })
        .fail(function() {
            $('#cache-stats-content').html('<div class="error"><?php _e('Erro na requisição', 'sm-student-control'); ?></div>');
        });
    }

    function displayCacheStats(stats) {
        var html = '<div class="sm-sc-stats-grid">';

        html += '<div class="stat-box">';
        html += '<h4><?php _e('Status do Cache', 'sm-student-control'); ?></h4>';
        html += '<span class="stat-value ' + (stats.enabled ? 'enabled' : 'disabled') + '">' +
                (stats.enabled ? '<?php _e('Habilitado', 'sm-student-control'); ?>' : '<?php _e('Desabilitado', 'sm-student-control'); ?>') + '</span>';
        html += '</div>';

        html += '<div class="stat-box">';
        html += '<h4><?php _e('Itens em Cache', 'sm-student-control'); ?></h4>';
        html += '<span class="stat-value">' + stats.total_items + '</span>';
        html += '</div>';

        html += '<div class="stat-box">';
        html += '<h4><?php _e('Tamanho', 'sm-student-control'); ?></h4>';
        html += '<span class="stat-value">' + stats.total_size_mb + ' MB</span>';
        html += '</div>';

        html += '<div class="stat-box">';
        html += '<h4><?php _e('Duração', 'sm-student-control'); ?></h4>';
        html += '<span class="stat-value">' + stats.duration_hours + 'h</span>';
        html += '</div>';

        html += '</div>';

        $('#cache-stats-content').html(html);
    }

    function loadCachedStudents(page = 1) {
        var search = $('#student-search').val();
        var school = $('#school-filter').val();

        $('#cached-students-table').html('<div class="sm-sc-loading"><?php _e('Carregando alunos...', 'sm-student-control'); ?></div>');

        $.post(ajaxurl, {
            action: 'sm_sc_get_cached_students',
            nonce: smscAdmin.nonce,
            search: search,
            school: school,
            page: page
        })
        .done(function(response) {
            if (response.success) {
                displayCachedStudents(response.data.students, response.data.pagination);
            } else {
                $('#cached-students-table').html('<div class="error"><?php _e('Erro ao carregar alunos', 'sm-student-control'); ?></div>');
            }
        })
        .fail(function() {
            $('#cached-students-table').html('<div class="error"><?php _e('Erro na requisição', 'sm-student-control'); ?></div>');
        });
    }

    function displayCachedStudents(students, pagination) {
        if (!students || students.length === 0) {
            $('#cached-students-table').html('<p><?php _e('Nenhum aluno encontrado em cache.', 'sm-student-control'); ?></p>');
            $('#students-pagination').hide();
            return;
        }

        var html = '<table class="widefat striped">';
        html += '<thead>';
        html += '<tr>';
        html += '<th><?php _e('ID', 'sm-student-control'); ?></th>';
        html += '<th><?php _e('Nome', 'sm-student-control'); ?></th>';
        html += '<th><?php _e('Email', 'sm-student-control'); ?></th>';
        html += '<th><?php _e('Última Atualização', 'sm-student-control'); ?></th>';
        html += '<th><?php _e('Ações', 'sm-student-control'); ?></th>';
        html += '</tr>';
        html += '</thead>';
        html += '<tbody>';

        students.forEach(function(student) {
            html += '<tr>';
            html += '<td>' + student.id + '</td>';
            html += '<td>' + student.name + '</td>';
            html += '<td>' + student.email + '</td>';
            html += '<td>' + student.last_updated + '</td>';
            html += '<td>';
            html += '<button class="button button-small refresh-student-cache" data-student-id="' + student.id + '">';
            html += '<?php _e('Atualizar', 'sm-student-control'); ?>';
            html += '</button>';
            html += '</td>';
            html += '</tr>';
        });

        html += '</tbody>';
        html += '</table>';

        $('#cached-students-table').html(html);

        // Configurar paginação
        if (pagination && pagination.total_pages > 1) {
            displayPagination(pagination);
            $('#students-pagination').show();
        } else {
            $('#students-pagination').hide();
        }

        // Eventos dos botões
        $('.refresh-student-cache').on('click', function() {
            var studentId = $(this).data('student-id');
            executeAction('refresh_student_cache', { student_id: studentId });
        });
    }

    function displayPagination(pagination) {
        var html = '';

        if (pagination.current_page > 1) {
            html += '<a href="#" class="page-link" data-page="' + (pagination.current_page - 1) + '">&laquo; <?php _e('Anterior', 'sm-student-control'); ?></a>';
        }

        for (var i = 1; i <= pagination.total_pages; i++) {
            if (i === pagination.current_page) {
                html += '<span class="page-link current">' + i + '</span>';
            } else {
                html += '<a href="#" class="page-link" data-page="' + i + '">' + i + '</a>';
            }
        }

        if (pagination.current_page < pagination.total_pages) {
            html += '<a href="#" class="page-link" data-page="' + (pagination.current_page + 1) + '"><?php _e('Próximo', 'sm-student-control'); ?> &raquo;</a>';
        }

        $('#students-pagination').html(html);

        // Eventos da paginação
        $('#students-pagination .page-link').on('click', function(e) {
            e.preventDefault();
            var page = $(this).data('page');
            if (page) {
                loadCachedStudents(page);
            }
        });
    }

    // Carregar alunos inicialmente
    loadCachedStudents();
});
</script>