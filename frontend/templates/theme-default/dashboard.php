<?php
/**
 * Template: Dashboard do Professor
 *
 * Template padrão para o painel do professor
 */

if (!defined('ABSPATH')) {
    exit;
}

// Verificar se temos dados do professor
if (!isset($professor_data) || !isset($atts)) {
    return;
}
?>

<div class="sm-sc-frontend sm-sc-dashboard" data-template="<?php echo esc_attr($atts['template']); ?>">
    <div class="sm-sc-container">

        <!-- Header -->
        <div class="sm-sc-header">
            <div class="sm-sc-header-content">
                <h1 class="sm-sc-title">
                    <?php echo esc_html($atts['title']); ?>
                </h1>
                <div class="sm-sc-header-meta">
                    <span class="sm-sc-professor-name">
                        <?php echo esc_html($professor_data['professor_name']); ?>
                    </span>
                    <span class="sm-sc-school-name">
                        <?php echo esc_html($professor_data['school_name']); ?>
                    </span>
                </div>
            </div>

            <div class="sm-sc-header-actions">
                <button type="button" class="button secondary sm-sc-refresh-all" title="<?php esc_attr_e('Atualizar todos os caches', 'sm-student-control'); ?>">
                    <span class="dashicons dashicons-update"></span>
                    <?php _e('Atualizar Tudo', 'sm-student-control'); ?>
                </button>
            </div>
        </div>

        <!-- Filtros e Busca -->
        <div class="sm-sc-filters">
            <div class="sm-sc-filters-row">
                <div class="sm-sc-search-box">
                    <input type="text"
                           id="sm-sc-search"
                           placeholder="<?php esc_attr_e('Buscar alunos por nome, email ou ID...', 'sm-student-control'); ?>"
                           class="sm-sc-search-input">
                    <button type="button" class="sm-sc-search-clear" title="<?php esc_attr_e('Limpar busca', 'sm-student-control'); ?>">
                        <span class="dashicons dashicons-no"></span>
                    </button>
                </div>

                <div class="sm-sc-course-filter">
                    <select id="sm-sc-course-filter" class="sm-sc-select">
                        <option value=""><?php _e('Todos os cursos', 'sm-student-control'); ?></option>
                        <?php
                        $courses = SM_Student_Control_Professor_Students::get_professor_courses(
                            $professor_data['school_id'],
                            $professor_data['professor_id']
                        );
                        foreach ($courses as $course) {
                            echo '<option value="' . esc_attr($course['id']) . '">' . esc_html($course['title']) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="sm-sc-per-page">
                    <label for="sm-sc-per-page-select"><?php _e('Mostrar:', 'sm-student-control'); ?></label>
                    <select id="sm-sc-per-page-select" class="sm-sc-select">
                        <option value="10">10</option>
                        <option value="20" selected>20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Estatísticas Rápidas -->
        <div class="sm-sc-stats">
            <div class="sm-sc-stat-card">
                <div class="sm-sc-stat-icon">
                    <span class="dashicons dashicons-groups"></span>
                </div>
                <div class="sm-sc-stat-content">
                    <div class="sm-sc-stat-number" id="sm-sc-total-students">
                        <span class="sm-sc-loading"><?php _e('Carregando...', 'sm-student-control'); ?></span>
                    </div>
                    <div class="sm-sc-stat-label">
                        <?php _e('Total de Alunos', 'sm-student-control'); ?>
                    </div>
                </div>
            </div>

            <div class="sm-sc-stat-card">
                <div class="sm-sc-stat-icon">
                    <span class="dashicons dashicons-book"></span>
                </div>
                <div class="sm-sc-stat-content">
                    <div class="sm-sc-stat-number" id="sm-sc-active-courses">
                        <span class="sm-sc-loading"><?php _e('Carregando...', 'sm-student-control'); ?></span>
                    </div>
                    <div class="sm-sc-stat-label">
                        <?php _e('Cursos Ativos', 'sm-student-control'); ?>
                    </div>
                </div>
            </div>

            <div class="sm-sc-stat-card">
                <div class="sm-sc-stat-icon">
                    <span class="dashicons dashicons-chart-line"></span>
                </div>
                <div class="sm-sc-stat-content">
                    <div class="sm-sc-stat-number" id="sm-sc-avg-progress">
                        <span class="sm-sc-loading"><?php _e('Carregando...', 'sm-student-control'); ?></span>
                    </div>
                    <div class="sm-sc-stat-label">
                        <?php _e('Progresso Médio', 'sm-student-control'); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de Alunos -->
        <div class="sm-sc-students-section">
            <div class="sm-sc-section-header">
                <h2><?php _e('Meus Alunos', 'sm-student-control'); ?></h2>
                <div class="sm-sc-section-actions">
                    <button type="button" class="button secondary sm-sc-export-csv" title="<?php esc_attr_e('Exportar para CSV', 'sm-student-control'); ?>">
                        <span class="dashicons dashicons-download"></span>
                        <?php _e('Exportar', 'sm-student-control'); ?>
                    </button>
                </div>
            </div>

            <div class="sm-sc-students-list" id="sm-sc-students-list">
                <div class="sm-sc-loading-message">
                    <div class="sm-sc-spinner"></div>
                    <p><?php _e('Carregando alunos...', 'sm-student-control'); ?></p>
                </div>
            </div>

            <!-- Paginação -->
            <div class="sm-sc-pagination" id="sm-sc-pagination" style="display: none;">
                <button type="button" class="button secondary sm-sc-prev-page" disabled>
                    <span class="dashicons dashicons-arrow-left-alt2"></span>
                    <?php _e('Anterior', 'sm-student-control'); ?>
                </button>

                <span class="sm-sc-page-info" id="sm-sc-page-info">
                    <?php _e('Página 1 de 1', 'sm-student-control'); ?>
                </span>

                <button type="button" class="button secondary sm-sc-next-page" disabled>
                    <?php _e('Próximo', 'sm-student-control'); ?>
                    <span class="dashicons dashicons-arrow-right-alt2"></span>
                </button>
            </div>
        </div>

        <!-- Modal de Detalhes do Aluno -->
        <div class="sm-sc-modal" id="sm-sc-student-modal" style="display: none;">
            <div class="sm-sc-modal-overlay"></div>
            <div class="sm-sc-modal-content">
                <div class="sm-sc-modal-header">
                    <h3><?php _e('Detalhes do Aluno', 'sm-student-control'); ?></h3>
                    <button type="button" class="sm-sc-modal-close">
                        <span class="dashicons dashicons-no"></span>
                    </button>
                </div>
                <div class="sm-sc-modal-body" id="sm-sc-student-details">
                    <div class="sm-sc-loading-message">
                        <div class="sm-sc-spinner"></div>
                        <p><?php _e('Carregando detalhes...', 'sm-student-control'); ?></p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Templates Handlebars para renderização dinâmica -->
<script type="text/x-handlebars-template" id="sm-sc-student-row-template">
    <div class="sm-sc-student-row" data-student-id="{{id}}">
        <div class="sm-sc-student-avatar">
            {{#if avatar}}
                <img src="{{avatar}}" alt="{{display_name}}" class="sm-sc-avatar">
            {{else}}
                <div class="sm-sc-avatar-placeholder">
                    {{initials}}
                </div>
            {{/if}}
        </div>

        <div class="sm-sc-student-info">
            <div class="sm-sc-student-name">
                <a href="#" class="sm-sc-student-link" data-student-id="{{id}}">
                    {{display_name}}
                </a>
            </div>
            <div class="sm-sc-student-email">{{user_email}}</div>
            <div class="sm-sc-student-meta">
                <span class="sm-sc-student-id">ID: {{id}}</span>
                {{#if last_login}}
                    <span class="sm-sc-last-login">
                        <?php _e('Último acesso:', 'sm-student-control'); ?> {{last_login_formatted}}
                    </span>
                {{/if}}
            </div>
        </div>

        <div class="sm-sc-student-progress">
            <div class="sm-sc-progress-bar">
                <div class="sm-sc-progress-fill" style="width: {{progress}}%"></div>
            </div>
            <div class="sm-sc-progress-text">{{progress}}%</div>
        </div>

        <div class="sm-sc-student-courses">
            <div class="sm-sc-courses-count">
                {{courses_count}} <?php _e('cursos', 'sm-student-control'); ?>
            </div>
            {{#if current_course}}
                <div class="sm-sc-current-course">{{current_course}}</div>
            {{/if}}
        </div>

        <div class="sm-sc-student-actions">
            <button type="button" class="button small sm-sc-view-details" data-student-id="{{id}}" title="<?php esc_attr_e('Ver detalhes', 'sm-student-control'); ?>">
                <span class="dashicons dashicons-visibility"></span>
            </button>
            <button type="button" class="button small secondary sm-sc-refresh-cache" data-student-id="{{id}}" title="<?php esc_attr_e('Atualizar cache', 'sm-student-control'); ?>">
                <span class="dashicons dashicons-update"></span>
            </button>
        </div>
    </div>
</script>

<script type="text/x-handlebars-template" id="sm-sc-student-details-template">
    <div class="sm-sc-student-profile">
        <div class="sm-sc-profile-header">
            {{#if avatar}}
                <img src="{{avatar}}" alt="{{display_name}}" class="sm-sc-profile-avatar">
            {{else}}
                <div class="sm-sc-profile-avatar-placeholder">{{initials}}</div>
            {{/if}}
            <div class="sm-sc-profile-info">
                <h4>{{display_name}}</h4>
                <p>{{user_email}}</p>
                <div class="sm-sc-profile-meta">
                    <span><?php _e('ID:', 'sm-student-control'); ?> {{id}}</span>
                    {{#if last_login}}
                        <span><?php _e('Último acesso:', 'sm-student-control'); ?> {{last_login_formatted}}</span>
                    {{/if}}
                </div>
            </div>
        </div>

        <div class="sm-sc-profile-stats">
            <div class="sm-sc-stat-item">
                <span class="sm-sc-stat-value">{{courses_count}}</span>
                <span class="sm-sc-stat-label"><?php _e('Cursos', 'sm-student-control'); ?></span>
            </div>
            <div class="sm-sc-stat-item">
                <span class="sm-sc-stat-value">{{completed_courses}}</span>
                <span class="sm-sc-stat-label"><?php _e('Concluídos', 'sm-student-control'); ?></span>
            </div>
            <div class="sm-sc-stat-item">
                <span class="sm-sc-stat-value">{{progress}}%</span>
                <span class="sm-sc-stat-label"><?php _e('Progresso Médio', 'sm-student-control'); ?></span>
            </div>
        </div>

        {{#if courses}}
        <div class="sm-sc-student-courses-list">
            <h5><?php _e('Cursos Matriculados', 'sm-student-control'); ?></h5>
            {{#each courses}}
            <div class="sm-sc-course-item">
                <div class="sm-sc-course-info">
                    <h6>{{title}}</h6>
                    <div class="sm-sc-course-progress">
                        <div class="sm-sc-progress-bar small">
                            <div class="sm-sc-progress-fill" style="width: {{progress}}%"></div>
                        </div>
                        <span>{{progress}}%</span>
                    </div>
                </div>
                <div class="sm-sc-course-meta">
                    <span><?php _e('Status:', 'sm-student-control'); ?> {{status}}</span>
                    {{#if last_activity}}
                        <span><?php _e('Última atividade:', 'sm-student-control'); ?> {{last_activity_formatted}}</span>
                    {{/if}}
                </div>
            </div>
            {{/each}}
        </div>
        {{/if}}
    </div>
</script>