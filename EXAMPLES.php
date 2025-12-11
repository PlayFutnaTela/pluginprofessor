<?php
/**
 * Exemplo de como configurar e usar a integração com API externa
 * 
 * Adicione este código a um hook apropriado no seu tema ou plugin
 * Por exemplo: em functions.php do seu tema ou em um plugin helper
 */

// Configurar o token de autenticação (execute uma única vez)
// update_option('sm_student_control_api_token', 'eyJhbGciOiJSUzI1NiIsInR5cCIgOiAiSldUIiwia2lkIiA6ICJD...');

// ============================================================
// EXEMPLO 1: Usar a classe diretamente no servidor (PHP)
// ============================================================

function example_get_classes_server_side() {
    // Carregar a classe de integração
    require_once plugin_dir_path(__FILE__) . 'includes/class-sm-student-control-external-api.php';
    
    // Buscar turmas do professor
    $result = SM_Student_Control_External_API::get_classes_by_user(
        $school_id = 2003,   // ID da escola
        $user_id = 65,       // ID do professor
        $type = 1,           // Tipo
        $role_id = 1         // ID da função
    );
    
    // Verificar se houve erro
    if (is_wp_error($result)) {
        error_log('Erro: ' . $result->get_error_message());
        return;
    }
    
    // Processar dados
    if (is_array($result)) {
        foreach ($result as $class) {
            echo 'Turma: ' . $class['classId'] . ' - ' . $class['className'] . PHP_EOL;
        }
    } else {
        echo 'Turma única: ' . $result['classId'] . ' - ' . $result['className'] . PHP_EOL;
    }
}

// ============================================================
// EXEMPLO 2: Usar AJAX desde o Frontend (JavaScript)
// ============================================================

function example_add_admin_page() {
    add_menu_page(
        'API Integration Test',
        'API Test',
        'manage_options',
        'api-integration-test',
        function() {
            ?>
            <div class="wrap">
                <h1><?php _e('API Integration Test'); ?></h1>
                
                <h2><?php _e('Buscar Turmas'); ?></h2>
                <form id="form-get-classes">
                    <table class="form-table">
                        <tr>
                            <th><label for="school_id"><?php _e('School ID'); ?></label></th>
                            <td><input type="number" id="school_id" name="school_id" value="2003" /></td>
                        </tr>
                        <tr>
                            <th><label for="user_id"><?php _e('User ID'); ?></label></th>
                            <td><input type="number" id="user_id" name="user_id" value="65" /></td>
                        </tr>
                    </table>
                    <button type="submit" class="button button-primary"><?php _e('Buscar Turmas'); ?></button>
                </form>
                <div id="classes-result" style="margin-top: 20px;"></div>
                
                <hr>
                
                <h2><?php _e('Buscar Alunos'); ?></h2>
                <form id="form-get-students">
                    <table class="form-table">
                        <tr>
                            <th><label for="class_id"><?php _e('Class ID'); ?></label></th>
                            <td><input type="number" id="class_id" name="class_id" value="15458" /></td>
                        </tr>
                        <tr>
                            <th><label for="limit"><?php _e('Limit'); ?></label></th>
                            <td><input type="number" id="limit" name="limit" value="20" /></td>
                        </tr>
                        <tr>
                            <th><label for="page"><?php _e('Page'); ?></label></th>
                            <td><input type="number" id="page" name="page" value="1" /></td>
                        </tr>
                    </table>
                    <button type="submit" class="button button-primary"><?php _e('Buscar Alunos'); ?></button>
                </form>
                <div id="students-result" style="margin-top: 20px;"></div>
            </div>
            
            <script>
            jQuery(document).ready(function($) {
                
                // Buscar turmas
                $('#form-get-classes').on('submit', function(e) {
                    e.preventDefault();
                    
                    var schoolId = $('#school_id').val();
                    var userId = $('#user_id').val();
                    
                    $.ajax({
                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                        type: 'POST',
                        data: {
                            action: 'get_classes_by_user',
                            school_id: schoolId,
                            user_id: userId,
                            security: '<?php echo wp_create_nonce('sm_student_control_nonce'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                var html = '<h3><?php _e('Turmas encontradas'); ?></h3>';
                                html += '<pre>' + JSON.stringify(response.data, null, 2) + '</pre>';
                                $('#classes-result').html(html);
                            } else {
                                $('#classes-result').html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
                            }
                        },
                        error: function(xhr, status, error) {
                            $('#classes-result').html('<div class="notice notice-error"><p><?php _e('Erro na requisição'); ?>: ' + error + '</p></div>');
                        }
                    });
                });
                
                // Buscar alunos
                $('#form-get-students').on('submit', function(e) {
                    e.preventDefault();
                    
                    var classId = $('#class_id').val();
                    var limit = $('#limit').val();
                    var page = $('#page').val();
                    
                    $.ajax({
                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                        type: 'POST',
                        data: {
                            action: 'get_assigned_students',
                            class_id: classId,
                            limit: limit,
                            page: page,
                            security: '<?php echo wp_create_nonce('sm_student_control_nonce'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                var html = '<h3><?php _e('Alunos encontrados'); ?></h3>';
                                html += '<p><?php _e('Total'); ?>: ' + response.data.totalStudents + '</p>';
                                html += '<pre>' + JSON.stringify(response.data.students, null, 2) + '</pre>';
                                $('#students-result').html(html);
                            } else {
                                $('#students-result').html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
                            }
                        },
                        error: function(xhr, status, error) {
                            $('#students-result').html('<div class="notice notice-error"><p><?php _e('Erro na requisição'); ?>: ' + error + '</p></div>');
                        }
                    });
                });
            });
            </script>
            <?php
        }
    );
}

// Descomente para adicionar a página de teste no admin
// add_action('admin_menu', 'example_add_admin_page');

// ============================================================
// EXEMPLO 3: Cron Job para sincronizar dados
// ============================================================

function example_schedule_sync() {
    // Agendar sincronização diária
    if (!wp_next_scheduled('sm_student_control_sync_external_classes')) {
        wp_schedule_event(time(), 'daily', 'sm_student_control_sync_external_classes');
    }
}

function example_sync_external_classes() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-sm-student-control-external-api.php';
    
    // Parâmetros de sincronização
    $schools = [
        ['school_id' => 2003, 'user_id' => 65, 'type' => 1, 'role_id' => 1],
        // Adicione mais escolas/professores conforme necessário
    ];
    
    foreach ($schools as $config) {
        $result = SM_Student_Control_External_API::get_classes_by_user(
            $config['school_id'],
            $config['user_id'],
            $config['type'],
            $config['role_id']
        );
        
        if (!is_wp_error($result)) {
            // Salvar em opção ou tabela personalizada
            update_option(
                'sm_student_control_synced_classes_' . $config['school_id'] . '_' . $config['user_id'],
                $result,
                false
            );
            
            // Log de sucesso
            error_log('Sincronização bem-sucedida: Escola ' . $config['school_id'] . ', Professor ' . $config['user_id']);
        } else {
            // Log de erro
            error_log('Erro na sincronização: ' . $result->get_error_message());
        }
    }
}

// Descomente para agendar a sincronização
// add_action('wp_loaded', 'example_schedule_sync');
// add_action('sm_student_control_sync_external_classes', 'example_sync_external_classes');

// ============================================================
// EXEMPLO 4: Recuperar dados sincroniados
// ============================================================

function example_get_synced_classes($school_id, $user_id) {
    $data = get_option('sm_student_control_synced_classes_' . $school_id . '_' . $user_id);
    
    if ($data) {
        return $data;
    } else {
        return false;
    }
}

// Uso:
// $classes = example_get_synced_classes(2003, 65);
// if ($classes) {
//     foreach ($classes as $class) {
//         echo $class['className'];
//     }
// }
