<?php
/**
 * Template: Detalhes do Estudante
 *
 * Template para exibir detalhes completos de um estudante
 */

if (!defined('ABSPATH')) {
    exit;
}

// Verificar se temos student_id
if (!isset($student_id) || !isset($atts)) {
    return;
}

// Obter dados do estudante
$student = SM_Student_Control_Professor_Students::get_student_details($student_id);

if (!$student) {
    echo '<div class="sm-sc-error">' . __('Estudante não encontrado', 'sm-student-control') . '</div>';
    return;
}

// Verificar permissões
if (!SM_Student_Control_Professor_Students::professor_has_access_to_student(
    get_current_user_id(),
    $student_id
)) {
    echo '<div class="sm-sc-error">' . __('Acesso negado a este estudante', 'sm-student-control') . '</div>';
    return;
}
?>

<div class="sm-sc-student-details-container">
    <div class="sm-sc-student-profile-header">
        <div class="sm-sc-profile-avatar-section">
            <?php if (!empty($student['avatar'])): ?>
                <img src="<?php echo esc_url($student['avatar']); ?>"
                     alt="<?php echo esc_attr($student['display_name']); ?>"
                     class="sm-sc-profile-avatar">
            <?php else: ?>
                <div class="sm-sc-profile-avatar-placeholder">
                    <?php echo esc_html(SM_Student_Control_Helpers::get_initials($student['display_name'])); ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="sm-sc-profile-info-section">
            <h3 class="sm-sc-student-full-name"><?php echo esc_html($student['display_name']); ?></h3>
            <div class="sm-sc-student-contact">
                <div class="sm-sc-contact-item">
                    <span class="dashicons dashicons-email"></span>
                    <a href="mailto:<?php echo esc_attr($student['user_email']); ?>">
                        <?php echo esc_html($student['user_email']); ?>
                    </a>
                </div>
                <div class="sm-sc-contact-item">
                    <span class="dashicons dashicons-id"></span>
                    <span><?php printf(__('ID: %d', 'sm-student-control'), $student['id']); ?></span>
                </div>
            </div>

            <div class="sm-sc-student-meta">
                <?php if (!empty($student['registration_date'])): ?>
                    <div class="sm-sc-meta-item">
                        <span class="sm-sc-meta-label"><?php _e('Data de Cadastro:', 'sm-student-control'); ?></span>
                        <span class="sm-sc-meta-value">
                            <?php echo esc_html(SM_Student_Control_Helpers::format_date($student['registration_date'])); ?>
                        </span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($student['last_login'])): ?>
                    <div class="sm-sc-meta-item">
                        <span class="sm-sc-meta-label"><?php _e('Último Acesso:', 'sm-student-control'); ?></span>
                        <span class="sm-sc-meta-value">
                            <?php echo esc_html(SM_Student_Control_Helpers::format_date($student['last_login'])); ?>
                        </span>
                    </div>
                <?php endif; ?>

                <div class="sm-sc-meta-item">
                    <span class="sm-sc-meta-label"><?php _e('Status da Conta:', 'sm-student-control'); ?></span>
                    <span class="sm-sc-meta-value sm-sc-status-<?php echo esc_attr($student['user_status']); ?>">
                        <?php echo esc_html(SM_Student_Control_Helpers::get_user_status_label($student['user_status'])); ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="sm-sc-profile-actions">
            <button type="button"
                    class="button secondary sm-sc-refresh-student-cache"
                    data-student-id="<?php echo esc_attr($student_id); ?>">
                <span class="dashicons dashicons-update"></span>
                <?php _e('Atualizar Cache', 'sm-student-control'); ?>
            </button>
        </div>
    </div>

    <!-- Estatísticas Gerais -->
    <div class="sm-sc-student-stats-grid">
        <div class="sm-sc-stat-card">
            <div class="sm-sc-stat-icon">
                <span class="dashicons dashicons-book"></span>
            </div>
            <div class="sm-sc-stat-content">
                <div class="sm-sc-stat-number"><?php echo esc_html($student['courses_count']); ?></div>
                <div class="sm-sc-stat-label">
                    <?php echo _n('Curso Matriculado', 'Cursos Matriculados', $student['courses_count'], 'sm-student-control'); ?>
                </div>
            </div>
        </div>

        <div class="sm-sc-stat-card">
            <div class="sm-sc-stat-icon">
                <span class="dashicons dashicons-yes"></span>
            </div>
            <div class="sm-sc-stat-content">
                <div class="sm-sc-stat-number"><?php echo esc_html($student['completed_courses']); ?></div>
                <div class="sm-sc-stat-label">
                    <?php echo _n('Curso Concluído', 'Cursos Concluídos', $student['completed_courses'], 'sm-student-control'); ?>
                </div>
            </div>
        </div>

        <div class="sm-sc-stat-card">
            <div class="sm-sc-stat-icon">
                <span class="dashicons dashicons-chart-line"></span>
            </div>
            <div class="sm-sc-stat-content">
                <div class="sm-sc-stat-number"><?php echo esc_html($student['progress']); ?>%</div>
                <div class="sm-sc-stat-label"><?php _e('Progresso Médio', 'sm-student-control'); ?></div>
            </div>
        </div>

        <div class="sm-sc-stat-card">
            <div class="sm-sc-stat-icon">
                <span class="dashicons dashicons-clock"></span>
            </div>
            <div class="sm-sc-stat-content">
                <div class="sm-sc-stat-number"><?php echo esc_html($student['total_time']); ?></div>
                <div class="sm-sc-stat-label"><?php _e('Tempo Total', 'sm-student-control'); ?></div>
            </div>
        </div>
    </div>

    <!-- Cursos do Estudante -->
    <?php if (!empty($student['courses'])): ?>
        <div class="sm-sc-student-courses-section">
            <h4><?php _e('Cursos Matriculados', 'sm-student-control'); ?></h4>

            <div class="sm-sc-courses-list">
                <?php foreach ($student['courses'] as $course): ?>
                    <div class="sm-sc-course-item">
                        <div class="sm-sc-course-header">
                            <div class="sm-sc-course-info">
                                <h5><?php echo esc_html($course['title']); ?></h5>
                                <div class="sm-sc-course-meta">
                                    <span class="sm-sc-course-id"><?php printf(__('ID: %d', 'sm-student-control'), $course['id']); ?></span>
                                    <span class="sm-sc-course-status sm-sc-status-<?php echo esc_attr($course['status']); ?>">
                                        <?php echo esc_html(SM_Student_Control_Helpers::get_course_status_label($course['status'])); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="sm-sc-course-progress">
                                <div class="sm-sc-progress-info">
                                    <span class="sm-sc-progress-label"><?php _e('Progresso:', 'sm-student-control'); ?></span>
                                    <span class="sm-sc-progress-value"><?php echo esc_html($course['progress']); ?>%</span>
                                </div>
                                <div class="sm-sc-progress-bar">
                                    <div class="sm-sc-progress-fill" style="width: <?php echo esc_attr($course['progress']); ?>%"></div>
                                </div>
                            </div>
                        </div>

                        <div class="sm-sc-course-details">
                            <div class="sm-sc-course-stats">
                                <?php if (!empty($course['start_date'])): ?>
                                    <div class="sm-sc-course-stat">
                                        <span class="sm-sc-stat-label"><?php _e('Data de Início:', 'sm-student-control'); ?></span>
                                        <span class="sm-sc-stat-value">
                                            <?php echo esc_html(SM_Student_Control_Helpers::format_date($course['start_date'])); ?>
                                        </span>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($course['last_activity'])): ?>
                                    <div class="sm-sc-course-stat">
                                        <span class="sm-sc-stat-label"><?php _e('Última Atividade:', 'sm-student-control'); ?></span>
                                        <span class="sm-sc-stat-value">
                                            <?php echo esc_html(SM_Student_Control_Helpers::format_date($course['last_activity'])); ?>
                                        </span>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($course['time_spent'])): ?>
                                    <div class="sm-sc-course-stat">
                                        <span class="sm-sc-stat-label"><?php _e('Tempo Gasto:', 'sm-student-control'); ?></span>
                                        <span class="sm-sc-stat-value"><?php echo esc_html($course['time_spent']); ?></span>
                                    </div>
                                <?php endif; ?>

                                <div class="sm-sc-course-stat">
                                    <span class="sm-sc-stat-label"><?php _e('Lições Completadas:', 'sm-student-control'); ?></span>
                                    <span class="sm-sc-stat-value">
                                        <?php echo esc_html($course['completed_lessons']); ?> / <?php echo esc_html($course['total_lessons']); ?>
                                    </span>
                                </div>
                            </div>

                            <?php if (!empty($course['current_lesson'])): ?>
                                <div class="sm-sc-current-lesson">
                                    <h6><?php _e('Lição Atual', 'sm-student-control'); ?></h6>
                                    <p><?php echo esc_html($course['current_lesson']); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Atividades Recentes -->
    <?php if (!empty($student['recent_activities'])): ?>
        <div class="sm-sc-student-activities-section">
            <h4><?php _e('Atividades Recentes', 'sm-student-control'); ?></h4>

            <div class="sm-sc-activities-list">
                <?php foreach ($student['recent_activities'] as $activity): ?>
                    <div class="sm-sc-activity-item">
                        <div class="sm-sc-activity-icon">
                            <span class="dashicons <?php echo esc_attr(SM_Student_Control_Helpers::get_activity_icon($activity['type'])); ?>"></span>
                        </div>
                        <div class="sm-sc-activity-content">
                            <div class="sm-sc-activity-description">
                                <?php echo esc_html($activity['description']); ?>
                            </div>
                            <div class="sm-sc-activity-meta">
                                <span class="sm-sc-activity-date">
                                    <?php echo esc_html(SM_Student_Control_Helpers::format_date($activity['date'])); ?>
                                </span>
                                <?php if (!empty($activity['course'])): ?>
                                    <span class="sm-sc-activity-course">
                                        <?php echo esc_html($activity['course']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Certificados -->
    <?php if (!empty($student['certificates'])): ?>
        <div class="sm-sc-student-certificates-section">
            <h4><?php _e('Certificados Obtidos', 'sm-student-control'); ?></h4>

            <div class="sm-sc-certificates-list">
                <?php foreach ($student['certificates'] as $certificate): ?>
                    <div class="sm-sc-certificate-item">
                        <div class="sm-sc-certificate-icon">
                            <span class="dashicons dashicons-awards"></span>
                        </div>
                        <div class="sm-sc-certificate-content">
                            <h5><?php echo esc_html($certificate['course_title']); ?></h5>
                            <div class="sm-sc-certificate-meta">
                                <span><?php printf(__('Emitido em: %s', 'sm-student-control'), SM_Student_Control_Helpers::format_date($certificate['issue_date'])); ?></span>
                                <?php if (!empty($certificate['download_url'])): ?>
                                    <a href="<?php echo esc_url($certificate['download_url']); ?>" target="_blank" class="button small">
                                        <span class="dashicons dashicons-download"></span>
                                        <?php _e('Download', 'sm-student-control'); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>