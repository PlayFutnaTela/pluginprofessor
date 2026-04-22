<div class="wrap">
    <h1><?php _e('Gerador de Shortcode - Painel Professor', 'sm-student-control'); ?></h1>

    <div class="sm-sc-shortcode-generator">
        <div class="sm-sc-generator-form">
            <h3><?php _e('Configurar Shortcode', 'sm-student-control'); ?></h3>

            <form id="shortcode-form">
                <div class="sm-sc-form-section">
                    <h4><?php _e('Aparência', 'sm-student-control'); ?></h4>

                    <div class="form-row">
                        <label for="template"><?php _e('Template:', 'sm-student-control'); ?></label>
                        <select id="template" name="template">
                            <option value="default"><?php _e('Padrão', 'sm-student-control'); ?></option>
                            <option value="modern"><?php _e('Moderno', 'sm-student-control'); ?></option>
                            <option value="compact"><?php _e('Compacto', 'sm-student-control'); ?></option>
                        </select>
                    </div>

                    <div class="form-row">
                        <label for="theme-color"><?php _e('Cor do Tema:', 'sm-student-control'); ?></label>
                        <input type="color" id="theme-color" name="theme_color" value="#4CAF50">
                    </div>

                    <div class="form-row">
                        <label for="width"><?php _e('Largura:', 'sm-student-control'); ?></label>
                        <select id="width" name="width">
                            <option value="100%"><?php _e('100% (Largura Total)', 'sm-student-control'); ?></option>
                            <option value="90%"><?php _e('90%', 'sm-student-control'); ?></option>
                            <option value="80%"><?php _e('80%', 'sm-student-control'); ?></option>
                            <option value="1200px"><?php _e('1200px (Centralizado)', 'sm-student-control'); ?></option>
                            <option value="1000px"><?php _e('1000px (Centralizado)', 'sm-student-control'); ?></option>
                        </select>
                    </div>
                </div>

                <div class="sm-sc-form-section">
                    <h4><?php _e('Funcionalidades', 'sm-student-control'); ?></h4>

                    <div class="form-row checkbox-row">
                        <input type="checkbox" id="show-filters" name="show_filters" checked>
                        <label for="show-filters"><?php _e('Mostrar filtros de busca', 'sm-student-control'); ?></label>
                    </div>

                    <div class="form-row checkbox-row">
                        <input type="checkbox" id="show-search" name="show_search" checked>
                        <label for="show-search"><?php _e('Mostrar campo de busca', 'sm-student-control'); ?></label>
                    </div>

                    <div class="form-row checkbox-row">
                        <input type="checkbox" id="show-pagination" name="show_pagination" checked>
                        <label for="show-pagination"><?php _e('Mostrar paginação', 'sm-student-control'); ?></label>
                    </div>

                    <div class="form-row">
                        <label for="items-per-page"><?php _e('Itens por página:', 'sm-student-control'); ?></label>
                        <select id="items-per-page" name="items_per_page">
                            <option value="10">10</option>
                            <option value="20" selected>20</option>
                            <option value="30">30</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>

                <div class="sm-sc-form-section">
                    <h4><?php _e('Campos Visíveis', 'sm-student-control'); ?></h4>

                    <div class="checkbox-grid">
                        <div class="checkbox-row">
                            <input type="checkbox" id="show-name" name="show_name" checked disabled>
                            <label for="show-name"><?php _e('Nome completo', 'sm-student-control'); ?> <em>(<?php _e('obrigatório', 'sm-student-control'); ?>)</em></label>
                        </div>

                        <div class="checkbox-row">
                            <input type="checkbox" id="show-email" name="show_email" checked>
                            <label for="show-email"><?php _e('Email', 'sm-student-control'); ?></label>
                        </div>

                        <div class="checkbox-row">
                            <input type="checkbox" id="show-courses" name="show_courses" checked>
                            <label for="show-courses"><?php _e('Cursos inscritos', 'sm-student-control'); ?></label>
                        </div>

                        <div class="checkbox-row">
                            <input type="checkbox" id="show-progress" name="show_progress" checked>
                            <label for="show-progress"><?php _e('Progresso', 'sm-student-control'); ?></label>
                        </div>

                        <div class="checkbox-row">
                            <input type="checkbox" id="show-last-access" name="show_last_access">
                            <label for="show-last-access"><?php _e('Último acesso', 'sm-student-control'); ?></label>
                        </div>

                        <div class="checkbox-row">
                            <input type="checkbox" id="show-enrollment-date" name="show_enrollment_date">
                            <label for="show-enrollment-date"><?php _e('Data de inscrição', 'sm-student-control'); ?></label>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" id="generate-shortcode" class="button button-primary button-large">
                        <?php _e('Gerar Shortcode', 'sm-student-control'); ?>
                    </button>

                    <button type="button" id="preview-shortcode" class="button button-secondary button-large">
                        <?php _e('Visualizar', 'sm-student-control'); ?>
                    </button>

                    <button type="button" id="reset-form" class="button button-secondary">
                        <?php _e('Redefinir', 'sm-student-control'); ?>
                    </button>
                </div>
            </form>
        </div>

        <div class="sm-sc-generator-output">
            <h3><?php _e('Shortcode Gerado', 'sm-student-control'); ?></h3>

            <div class="shortcode-display">
                <div id="generated-shortcode" class="shortcode-code">
                    [painel_professor]
                </div>

                <button type="button" id="copy-shortcode" class="button button-secondary">
                    <span class="dashicons dashicons-clipboard"></span>
                    <?php _e('Copiar', 'sm-student-control'); ?>
                </button>
            </div>

            <div class="shortcode-info">
                <h4><?php _e('Como usar:', 'sm-student-control'); ?></h4>
                <ol>
                    <li><?php _e('Copie o shortcode acima', 'sm-student-control'); ?></li>
                    <li><?php _e('Cole em qualquer página ou post do WordPress', 'sm-student-control'); ?></li>
                    <li><?php _e('Ou use em templates PHP: <code>&lt;?php echo do_shortcode(\'[painel_professor]\'); ?&gt;</code>', 'sm-student-control'); ?></li>
                </ol>

                <div class="shortcode-preview" id="shortcode-preview" style="display: none;">
                    <h4><?php _e('Pré-visualização:', 'sm-student-control'); ?></h4>
                    <div id="preview-content">
                        <!-- Preview será inserido aqui -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Preview -->
    <div id="sm-sc-preview-modal" class="sm-sc-modal" style="display: none;">
        <div class="sm-sc-modal-overlay"></div>
        <div class="sm-sc-modal-content large">
            <div class="sm-sc-modal-header">
                <h3><?php _e('Pré-visualização do Shortcode', 'sm-student-control'); ?></h3>
                <button type="button" class="sm-sc-modal-close">&times;</button>
            </div>
            <div class="sm-sc-modal-body" id="modal-preview-content">
                <!-- Conteúdo do preview -->
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    var generatedShortcode = '[painel_professor]';

    // Gerar shortcode
    $('#generate-shortcode').on('click', function() {
        var attributes = [];

        // Template
        var template = $('#template').val();
        if (template !== 'default') {
            attributes.push('template="' + template + '"');
        }

        // Cor do tema
        var themeColor = $('#theme-color').val();
        if (themeColor !== '#4CAF50') {
            attributes.push('theme_color="' + themeColor + '"');
        }

        // Largura
        var width = $('#width').val();
        if (width !== '100%') {
            attributes.push('width="' + width + '"');
        }

        // Funcionalidades
        if (!$('#show-filters').is(':checked')) {
            attributes.push('show_filters="no"');
        }

        if (!$('#show-search').is(':checked')) {
            attributes.push('show_search="no"');
        }

        if (!$('#show-pagination').is(':checked')) {
            attributes.push('show_pagination="no"');
        }

        // Itens por página
        var itemsPerPage = $('#items-per-page').val();
        if (itemsPerPage !== '20') {
            attributes.push('items_per_page="' + itemsPerPage + '"');
        }

        // Campos visíveis
        var visibleFields = [];
        if ($('#show-email').is(':checked')) visibleFields.push('email');
        if ($('#show-courses').is(':checked')) visibleFields.push('courses');
        if ($('#show-progress').is(':checked')) visibleFields.push('progress');
        if ($('#show-last-access').is(':checked')) visibleFields.push('last_access');
        if ($('#show-enrollment-date').is(':checked')) visibleFields.push('enrollment_date');

        if (visibleFields.length > 0) {
            attributes.push('visible_fields="' + visibleFields.join(',') + '"');
        }

        // Montar shortcode
        generatedShortcode = '[painel_professor';
        if (attributes.length > 0) {
            generatedShortcode += ' ' + attributes.join(' ');
        }
        generatedShortcode += ']';

        $('#generated-shortcode').text(generatedShortcode);
        $('#shortcode-preview').show();
    });

    // Preview
    $('#preview-shortcode').on('click', function() {
        $('#generate-shortcode').click(); // Gera o shortcode primeiro

        $('#modal-preview-content').html('<div class="sm-sc-loading"><?php _e('Carregando preview...', 'sm-student-control'); ?></div>');
        $('#sm-sc-preview-modal').show();

        $.post(ajaxurl, {
            action: 'sm_sc_preview_shortcode',
            nonce: smscAdmin.nonce,
            shortcode: generatedShortcode
        })
        .done(function(response) {
            if (response.success) {
                $('#modal-preview-content').html(response.html);
            } else {
                $('#modal-preview-content').html('<div class="error">' + response.error + '</div>');
            }
        })
        .fail(function() {
            $('#modal-preview-content').html('<div class="error"><?php _e('Erro ao carregar preview', 'sm-student-control'); ?></div>');
        });
    });

    // Copiar shortcode
    $('#copy-shortcode').on('click', function() {
        var shortcodeText = $('#generated-shortcode').text();

        // Usar Clipboard API se disponível
        if (navigator.clipboard) {
            navigator.clipboard.writeText(shortcodeText).then(function() {
                showCopyFeedback('<?php _e('Shortcode copiado!', 'sm-student-control'); ?>');
            });
        } else {
            // Fallback para método antigo
            var textArea = document.createElement('textarea');
            textArea.value = shortcodeText;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            showCopyFeedback('<?php _e('Shortcode copiado!', 'sm-student-control'); ?>');
        }
    });

    function showCopyFeedback(message) {
        var button = $('#copy-shortcode');
        var originalText = button.html();

        button.html('<span class="dashicons dashicons-yes"></span> ' + message);
        button.addClass('copied');

        setTimeout(function() {
            button.html(originalText);
            button.removeClass('copied');
        }, 2000);
    }

    // Reset form
    $('#reset-form').on('click', function() {
        $('#shortcode-form')[0].reset();
        $('#generated-shortcode').text('[painel_professor]');
        $('#shortcode-preview').hide();
        generatedShortcode = '[painel_professor]';
    });

    // Fechar modal
    $('.sm-sc-modal-close, .sm-sc-modal-overlay').on('click', function() {
        $('#sm-sc-preview-modal').hide();
    });

    // Gerar shortcode inicial
    $('#generate-shortcode').click();
});
</script>