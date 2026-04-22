<?php
/**
 * Main Dashboard - Painel Principal do Plugin
 * Página inicial do admin do SM Student Control
 */

if (!defined('ABSPATH')) {
    exit;
}

// Verificar permissões
if (!current_user_can('manage_options')) {
    wp_die(__('Você não tem permissão para acessar esta página.', 'sm-student-control'));
}

error_log('[SM-SC-ADMIN] 📊 Renderizando dashboard principal');

// Obter estatísticas básicas (se disponíveis)
$stats = [
    'total_students' => 0,
    'total_courses' => 0,
    'active_enrollments' => 0,
    'completed_courses' => 0,
];

// Tentar obter estatísticas se as tabelas existirem e WordPress estiver carregado
if (function_exists('get_current_user_id') && isset($GLOBALS['wpdb'])) {
    try {
        global $wpdb;

        if ($wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}stm_lms_user_courses'") === $wpdb->prefix . 'stm_lms_user_courses') {
            $stats['total_students'] = (int) $wpdb->get_var("SELECT COUNT(DISTINCT user_id) FROM {$wpdb->prefix}stm_lms_user_courses");
            $stats['total_courses'] = (int) $wpdb->get_var("SELECT COUNT(DISTINCT course_id) FROM {$wpdb->prefix}stm_lms_user_courses");
            $stats['active_enrollments'] = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}stm_lms_user_courses WHERE status = 'enrolled'");
            $stats['completed_courses'] = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}stm_lms_user_courses WHERE status = 'completed'");
        }
    } catch (Exception $e) {
        error_log('[SM-SC-ADMIN] ⚠️ Erro ao obter estatísticas: ' . $e->getMessage());
    }
}

?>

<div class="wrap">
    <div class="sm-sc-dashboard-header">
        <h1><?php _e('Painel Professor - SM Student Control', 'sm-student-control'); ?></h1>
        <p><?php _e('Gerencie seus alunos e cursos do MasterStudy LMS de forma eficiente.', 'sm-student-control'); ?></p>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="sm-sc-stats-grid">
        <div class="sm-sc-stat-card">
            <div class="sm-sc-stat-icon">
                <span class="dashicons dashicons-groups"></span>
            </div>
            <div class="sm-sc-stat-content">
                <h3><?php echo number_format($stats['total_students']); ?></h3>
                <p><?php _e('Total de Estudantes', 'sm-student-control'); ?></p>
            </div>
        </div>

        <div class="sm-sc-stat-card">
            <div class="sm-sc-stat-icon">
                <span class="dashicons dashicons-book"></span>
            </div>
            <div class="sm-sc-stat-content">
                <h3><?php echo number_format($stats['total_courses']); ?></h3>
                <p><?php _e('Cursos Disponíveis', 'sm-student-control'); ?></p>
            </div>
        </div>

        <div class="sm-sc-stat-card">
            <div class="sm-sc-stat-icon">
                <span class="dashicons dashicons-yes"></span>
            </div>
            <div class="sm-sc-stat-content">
                <h3><?php echo number_format($stats['active_enrollments']); ?></h3>
                <p><?php _e('Matrículas Ativas', 'sm-student-control'); ?></p>
            </div>
        </div>

        <div class="sm-sc-stat-card">
            <div class="sm-sc-stat-icon">
                <span class="dashicons dashicons-awards"></span>
            </div>
            <div class="sm-sc-stat-content">
                <h3><?php echo number_format($stats['completed_courses']); ?></h3>
                <p><?php _e('Cursos Concluídos', 'sm-student-control'); ?></p>
            </div>
        </div>
    </div>

    <!-- Ações Rápidas -->
    <div class="sm-sc-quick-actions">
        <h2><?php _e('Ações Rápidas', 'sm-student-control'); ?></h2>
        <div class="sm-sc-actions-grid">
            <a href="<?php echo admin_url('admin.php?page=sm-student-control-settings'); ?>" class="sm-sc-action-card">
                <span class="dashicons dashicons-admin-settings"></span>
                <h3><?php _e('Configurações', 'sm-student-control'); ?></h3>
                <p><?php _e('Configure APIs, cache e outras opções', 'sm-student-control'); ?></p>
            </a>

            <a href="<?php echo admin_url('admin.php?page=sm-student-control-cache'); ?>" class="sm-sc-action-card">
                <span class="dashicons dashicons-database"></span>
                <h3><?php _e('Gerenciar Cache', 'sm-student-control'); ?></h3>
                <p><?php _e('Limpe ou atualize o cache de dados', 'sm-student-control'); ?></p>
            </a>

            <a href="<?php echo admin_url('admin.php?page=sm-student-control-shortcode'); ?>" class="sm-sc-action-card">
                <span class="dashicons dashicons-shortcode"></span>
                <h3><?php _e('Gerador de Shortcode', 'sm-student-control'); ?></h3>
                <p><?php _e('Gere shortcodes para suas páginas', 'sm-student-control'); ?></p>
            </a>

            <a href="<?php echo admin_url('admin.php?page=sm-student-control-logs'); ?>" class="sm-sc-action-card">
                <span class="dashicons dashicons-search"></span>
                <h3><?php _e('Logs e Debug', 'sm-student-control'); ?></h3>
                <p><?php _e('Visualize logs e informações de debug', 'sm-student-control'); ?></p>
            </a>
        </div>
    </div>

    <!-- Informações do Sistema -->
    <div class="sm-sc-system-info">
        <h2><?php _e('Informações do Sistema', 'sm-student-control'); ?></h2>
        <div class="sm-sc-info-grid">
            <div class="sm-sc-info-item">
                <strong><?php _e('Versão do Plugin:', 'sm-student-control'); ?></strong>
                <span><?php echo SM_STUDENT_CONTROL_VERSION; ?></span>
            </div>

            <div class="sm-sc-info-item">
                <strong><?php _e('Status do Cache:', 'sm-student-control'); ?></strong>
                <span><?php echo SM_Student_Control_Settings::get('cache_enabled') ? __('Ativado', 'sm-student-control') : __('Desativado', 'sm-student-control'); ?></span>
            </div>

            <div class="sm-sc-info-item">
                <strong><?php _e('Modo Debug:', 'sm-student-control'); ?></strong>
                <span><?php echo SM_Student_Control_Settings::get('debug_mode') ? __('Ativado', 'sm-student-control') : __('Desativado', 'sm-student-control'); ?></span>
            </div>

            <div class="sm-sc-info-item">
                <strong><?php _e('Última Verificação:', 'sm-student-control'); ?></strong>
                <span><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format')); ?></span>
            </div>
        </div>
    </div>

    <!-- Shortcode de Exemplo -->
    <div class="sm-sc-shortcode-info">
        <h2><?php _e('Como Usar o Plugin', 'sm-student-control'); ?></h2>
        <div class="sm-sc-shortcode-box">
            <p><?php _e('Para exibir o painel do professor em qualquer página ou post, use o shortcode:', 'sm-student-control'); ?></p>
            <code>[painel_professor]</code>
        </div>
        <p><?php _e('Certifique-se de que o usuário esteja logado e tenha as permissões necessárias para visualizar os dados.', 'sm-student-control'); ?></p>
    </div>
</div>

<style>
/* Dashboard Styles */
.sm-sc-dashboard-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    border-radius: 8px;
    margin-bottom: 30px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.sm-sc-dashboard-header h1 {
    margin: 0 0 10px 0;
    font-size: 2.5em;
    font-weight: 300;
}

.sm-sc-dashboard-header p {
    margin: 0;
    font-size: 1.1em;
    opacity: 0.9;
}

/* Stats Grid */
.sm-sc-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.sm-sc-stat-card {
    background: white;
    border: 1px solid #e1e1e1;
    border-radius: 8px;
    padding: 25px;
    display: flex;
    align-items: center;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.sm-sc-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.sm-sc-stat-icon {
    background: #f1f1f1;
    border-radius: 50%;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 20px;
    font-size: 24px;
    color: #666;
}

.sm-sc-stat-content h3 {
    margin: 0 0 5px 0;
    font-size: 2.5em;
    font-weight: 600;
    color: #333;
}

.sm-sc-stat-content p {
    margin: 0;
    color: #666;
    font-size: 0.9em;
}

/* Quick Actions */
.sm-sc-quick-actions {
    margin-bottom: 40px;
}

.sm-sc-quick-actions h2 {
    margin-bottom: 20px;
    color: #333;
}

.sm-sc-actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.sm-sc-action-card {
    background: white;
    border: 1px solid #e1e1e1;
    border-radius: 8px;
    padding: 25px;
    text-decoration: none;
    color: #333;
    display: block;
    transition: all 0.2s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.sm-sc-action-card:hover {
    border-color: #007cba;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.sm-sc-action-card .dashicons {
    font-size: 32px;
    width: 32px;
    height: 32px;
    color: #007cba;
    margin-bottom: 15px;
}

.sm-sc-action-card h3 {
    margin: 0 0 10px 0;
    font-size: 1.2em;
}

.sm-sc-action-card p {
    margin: 0;
    color: #666;
    font-size: 0.9em;
}

/* System Info */
.sm-sc-system-info {
    margin-bottom: 40px;
}

.sm-sc-system-info h2 {
    margin-bottom: 20px;
    color: #333;
}

.sm-sc-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.sm-sc-info-item {
    background: white;
    border: 1px solid #e1e1e1;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.sm-sc-info-item strong {
    display: block;
    margin-bottom: 5px;
    color: #333;
}

.sm-sc-info-item span {
    color: #666;
}

/* Shortcode Info */
.sm-sc-shortcode-info {
    background: #f9f9f9;
    border: 1px solid #e1e1e1;
    border-radius: 8px;
    padding: 30px;
}

.sm-sc-shortcode-info h2 {
    margin-top: 0;
    color: #333;
}

.sm-sc-shortcode-box {
    background: white;
    border: 2px dashed #007cba;
    border-radius: 4px;
    padding: 20px;
    text-align: center;
    margin: 20px 0;
}

.sm-sc-shortcode-box code {
    background: #f1f1f1;
    padding: 10px 20px;
    border-radius: 4px;
    font-size: 1.2em;
    font-family: monospace;
    color: #007cba;
}

/* Responsividade */
@media (max-width: 768px) {
    .sm-sc-dashboard-header {
        padding: 20px;
    }

    .sm-sc-dashboard-header h1 {
        font-size: 2em;
    }

    .sm-sc-stats-grid,
    .sm-sc-actions-grid,
    .sm-sc-info-grid {
        grid-template-columns: 1fr;
    }

    .sm-sc-stat-card {
        padding: 20px;
    }

    .sm-sc-action-card {
        padding: 20px;
    }
}
</style>

<script>
// JavaScript para interações dinâmicas (opcional)
jQuery(document).ready(function($) {
    console.log('[SM-SC-DASHBOARD] 🎯 Dashboard carregado com sucesso');

    // Adicionar tooltips ou outras interações se necessário
    $('.sm-sc-action-card').on('mouseenter', function() {
        $(this).addClass('hover');
    }).on('mouseleave', function() {
        $(this).removeClass('hover');
    });
});
</script>

<?php error_log('[SM-SC-ADMIN] ✅ Dashboard principal renderizado'); ?>