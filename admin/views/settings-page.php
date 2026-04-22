<div class="wrap">
    <h1><?php _e('Configurações - Painel Professor', 'sm-student-control'); ?></h1>

    <?php settings_errors(); ?>

    <form method="post" action="options.php">
        <?php
        settings_fields('sm_sc_settings');
        do_settings_sections('sm_sc_settings');
        submit_button();
        ?>
    </form>

    <div class="sm-sc-admin-notice">
        <h3><?php _e('Teste de Configurações', 'sm-student-control'); ?></h3>
        <p><?php _e('Clique nos botões abaixo para testar as configurações atuais.', 'sm-student-control'); ?></p>

        <div class="sm-sc-test-buttons">
            <button type="button" id="test-jwt" class="button button-secondary">
                <?php _e('Testar JWT', 'sm-student-control'); ?>
            </button>

            <button type="button" id="test-api" class="button button-secondary">
                <?php _e('Testar API Externa', 'sm-student-control'); ?>
            </button>

            <button type="button" id="check-lms-tables" class="button button-secondary">
                <?php _e('Verificar Tabelas LMS', 'sm-student-control'); ?>
            </button>
        </div>

        <div id="test-results" style="display: none; margin-top: 15px;">
            <div id="test-output" class="sm-sc-test-output"></div>
        </div>
    </div>

    <div class="sm-sc-admin-info">
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
                    <td><strong><?php _e('URL do Site', 'sm-student-control'); ?>:</strong></td>
                    <td><?php echo get_site_url(); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Testar JWT
    $('#test-jwt').on('click', function() {
        $(this).prop('disabled', true).text('<?php _e('Testando...', 'sm-student-control'); ?>');

        $.post(ajaxurl, {
            action: 'sm_sc_test_jwt',
            nonce: smscAdmin.nonce
        })
        .done(function(response) {
            displayTestResult('JWT', response);
        })
        .fail(function() {
            displayTestResult('JWT', {success: false, error: '<?php _e('Erro na requisição', 'sm-student-control'); ?>'});
        })
        .always(function() {
            $('#test-jwt').prop('disabled', false).text('<?php _e('Testar JWT', 'sm-student-control'); ?>');
        });
    });

    // Testar API
    $('#test-api').on('click', function() {
        $(this).prop('disabled', true).text('<?php _e('Testando...', 'sm-student-control'); ?>');

        $.post(ajaxurl, {
            action: 'sm_sc_test_api',
            nonce: smscAdmin.nonce
        })
        .done(function(response) {
            displayTestResult('API', response);
        })
        .fail(function() {
            displayTestResult('API', {success: false, error: '<?php _e('Erro na requisição', 'sm-student-control'); ?>'});
        })
        .always(function() {
            $('#test-api').prop('disabled', false).text('<?php _e('Testar API Externa', 'sm-student-control'); ?>');
        });
    });

    // Verificar tabelas LMS
    $('#check-lms-tables').on('click', function() {
        $(this).prop('disabled', true).text('<?php _e('Verificando...', 'sm-student-control'); ?>');

        $.post(ajaxurl, {
            action: 'sm_sc_check_lms_tables',
            nonce: smscAdmin.nonce
        })
        .done(function(response) {
            displayTestResult('LMS Tables', response);
        })
        .fail(function() {
            displayTestResult('LMS Tables', {success: false, error: '<?php _e('Erro na requisição', 'sm-student-control'); ?>'});
        })
        .always(function() {
            $('#check-lms-tables').prop('disabled', false).text('<?php _e('Verificar Tabelas LMS', 'sm-student-control'); ?>');
        });
    });

    function displayTestResult(testName, result) {
        $('#test-results').show();
        var output = $('#test-output');

        var html = '<div class="sm-sc-test-result ' + (result.success ? 'success' : 'error') + '">';
        html += '<h4>' + testName + ': ' + (result.success ? '<?php _e('Sucesso', 'sm-student-control'); ?>' : '<?php _e('Erro', 'sm-student-control'); ?>') + '</h4>';

        if (result.success) {
            if (result.message) {
                html += '<p>' + result.message + '</p>';
            }
            if (result.data) {
                html += '<pre>' + JSON.stringify(result.data, null, 2) + '</pre>';
            }
        } else {
            html += '<p class="error">' + (result.error || '<?php _e('Erro desconhecido', 'sm-student-control'); ?>') + '</p>';
        }

        html += '</div>';

        output.html(html);
    }
});
</script>