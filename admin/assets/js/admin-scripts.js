/**
 * SM Student Control - Admin Scripts
 */

(function($) {
    'use strict';

    console.log('[SM-SC-ADMIN] 🎯 Inicializando scripts administrativos');

    // Cache Management
    function initCacheManagement() {
        console.log('[SM-SC-ADMIN] 💾 Inicializando gerenciamento de cache');

        // Refresh all cache
        $(document).on('click', '#refresh-all-cache', function(e) {
            console.log('[SM-SC-ADMIN] 🔄 Solicitação para atualizar todo o cache');
            e.preventDefault();

            if (!confirm(smscAdmin.strings.confirm_clear_cache)) {
                return;
            }

            executeCacheAction('refresh_all_cache');
        });

        // Clear all cache
        $(document).on('click', '#clear-all-cache', function(e) {
            e.preventDefault();

            if (!confirm(smscAdmin.strings.confirm_clear_cache)) {
                return;
            }

            executeCacheAction('clear_all_cache');
        });

        // Refresh student cache
        $(document).on('submit', '#refresh-student-cache-form', function(e) {
            e.preventDefault();

            var studentId = $('#student-id').val();
            if (!studentId) {
                alert('Por favor, informe o ID do aluno.');
                return;
            }

            executeCacheAction('refresh_student_cache', { student_id: studentId });
        });
    }

    function executeCacheAction(action, params = {}) {
        showProgress('Executando operação...');

        $.post(ajaxurl, {
            action: 'sm_sc_' + action,
            nonce: smscAdmin.nonce,
            ...params
        })
        .done(function(response) {
            if (response.success) {
                showProgress('Operação concluída com sucesso!', 100);
                setTimeout(function() {
                    hideProgress();
                    // Reload cache stats if available
                    if (typeof loadCacheStats === 'function') {
                        loadCacheStats();
                    }
                }, 2000);
            } else {
                showProgress(response.error || 'Erro na operação', 0);
                setTimeout(hideProgress, 3000);
            }
        })
        .fail(function() {
            showProgress('Erro na requisição', 0);
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

    // Settings Page
    function initSettingsPage() {
        // Test JWT
        $(document).on('click', '#test-jwt', function(e) {
            e.preventDefault();
            runTest('jwt');
        });

        // Test API
        $(document).on('click', '#test-api', function(e) {
            e.preventDefault();
            runTest('api');
        });

        // Check LMS Tables
        $(document).on('click', '#check-lms-tables', function(e) {
            e.preventDefault();
            runTest('lms_tables');
        });
    }

    function runTest(testType) {
        var button = $('#test-' + testType.replace('_', '-'));
        var originalText = button.html();

        button.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> Testando...');

        $.post(ajaxurl, {
            action: 'sm_sc_run_test',
            nonce: smscAdmin.nonce,
            test_type: testType
        })
        .done(function(response) {
            displayTestResult(testType, response);
        })
        .fail(function() {
            displayTestResult(testType, {success: false, error: 'Erro na requisição'});
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
        html += '<h4>' + testName + ': ' + (result.success ? 'Sucesso' : 'Erro') + '</h4>';

        if (result.success) {
            html += '<p>' + (result.message || 'Teste executado com sucesso') + '</p>';
            if (result.data) {
                html += '<pre>' + JSON.stringify(result.data, null, 2) + '</pre>';
            }
        } else {
            html += '<p class="error">' + (result.error || 'Erro desconhecido') + '</p>';
        }

        html += '</div>';

        if (output.find('.sm-sc-test-result').length === 0) {
            output.html(html);
        } else {
            output.append(html);
        }

        // Scroll to result
        output[0].scrollIntoView({ behavior: 'smooth' });
    }

    function getTestName(testType) {
        var names = {
            'jwt': 'JWT',
            'api': 'API Externa',
            'lms_tables': 'Tabelas LMS'
        };
        return names[testType] || testType;
    }

    // Shortcode Generator
    function initShortcodeGenerator() {
        // Generate shortcode
        $(document).on('click', '#generate-shortcode', function(e) {
            e.preventDefault();
            generateShortcode();
        });

        // Preview shortcode
        $(document).on('click', '#preview-shortcode', function(e) {
            e.preventDefault();
            generateShortcode();
            previewShortcode();
        });

        // Copy shortcode
        $(document).on('click', '#copy-shortcode', function(e) {
            e.preventDefault();
            copyShortcode();
        });

        // Reset form
        $(document).on('click', '#reset-form', function(e) {
            e.preventDefault();
            resetForm();
        });
    }

    function generateShortcode() {
        var attributes = [];

        // Template
        var template = $('#template').val();
        if (template !== 'default') {
            attributes.push('template="' + template + '"');
        }

        // Theme color
        var themeColor = $('#theme-color').val();
        if (themeColor !== '#4CAF50') {
            attributes.push('theme_color="' + themeColor + '"');
        }

        // Width
        var width = $('#width').val();
        if (width !== '100%') {
            attributes.push('width="' + width + '"');
        }

        // Features
        if (!$('#show-filters').is(':checked')) {
            attributes.push('show_filters="no"');
        }

        if (!$('#show-search').is(':checked')) {
            attributes.push('show_search="no"');
        }

        if (!$('#show-pagination').is(':checked')) {
            attributes.push('show_pagination="no"');
        }

        // Items per page
        var itemsPerPage = $('#items-per-page').val();
        if (itemsPerPage !== '20') {
            attributes.push('items_per_page="' + itemsPerPage + '"');
        }

        // Visible fields
        var visibleFields = [];
        if ($('#show-email').is(':checked')) visibleFields.push('email');
        if ($('#show-courses').is(':checked')) visibleFields.push('courses');
        if ($('#show-progress').is(':checked')) visibleFields.push('progress');
        if ($('#show-last-access').is(':checked')) visibleFields.push('last_access');
        if ($('#show-enrollment-date').is(':checked')) visibleFields.push('enrollment_date');

        if (visibleFields.length > 0) {
            attributes.push('visible_fields="' + visibleFields.join(',') + '"');
        }

        // Build shortcode
        var shortcode = '[painel_professor';
        if (attributes.length > 0) {
            shortcode += ' ' + attributes.join(' ');
        }
        shortcode += ']';

        $('#generated-shortcode').text(shortcode);
        $('#shortcode-preview').show();

        return shortcode;
    }

    function previewShortcode() {
        var shortcode = $('#generated-shortcode').text();

        $('#modal-preview-content').html('<div class="sm-sc-loading">Carregando preview...</div>');
        $('#sm-sc-preview-modal').show();

        $.post(ajaxurl, {
            action: 'sm_sc_preview_shortcode',
            nonce: smscAdmin.nonce,
            shortcode: shortcode
        })
        .done(function(response) {
            if (response.success) {
                $('#modal-preview-content').html(response.html);
            } else {
                $('#modal-preview-content').html('<div class="error">' + response.error + '</div>');
            }
        })
        .fail(function() {
            $('#modal-preview-content').html('<div class="error">Erro ao carregar preview</div>');
        });
    }

    function copyShortcode() {
        var shortcodeText = $('#generated-shortcode').text();

        // Try modern clipboard API first
        if (navigator.clipboard) {
            navigator.clipboard.writeText(shortcodeText).then(function() {
                showCopyFeedback('Shortcode copiado!');
            });
        } else {
            // Fallback to old method
            var textArea = document.createElement('textarea');
            textArea.value = shortcodeText;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            showCopyFeedback('Shortcode copiado!');
        }
    }

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

    function resetForm() {
        $('#shortcode-form')[0].reset();
        $('#generated-shortcode').text('[painel_professor]');
        $('#shortcode-preview').hide();
    }

    // Modal handling
    $(document).on('click', '.sm-sc-modal-close, .sm-sc-modal-overlay', function(e) {
        e.preventDefault();
        $('.sm-sc-modal').hide();
    });

    // Settings Page
    function initSettingsPage() {
        console.log('[SM-SC-ADMIN] ⚙️ Inicializando página de configurações');

        // Generate JWT Secret
        $(document).on('click', '#generate-jwt-secret', function(e) {
            e.preventDefault();

            if (!confirm('Tem certeza que deseja gerar uma nova chave secreta JWT? Isso invalidará todos os tokens existentes.')) {
                return;
            }

            var newSecret = generateRandomString(64);
            $('input[name="sm_sc_jwt_secret"]').val(newSecret);

            alert('Nova chave secreta JWT gerada com sucesso!');
        });
    }

    function generateRandomString(length) {
        var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()_+-=[]{}|;:,.<>?';
        var result = '';
        for (var i = 0; i < length; i++) {
            result += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return result;
    }

    // Initialize based on current page
    $(document).ready(function() {
        var body = $('body');

        if (body.hasClass('sm-student-control_page_sm-student-control-cache')) {
            initCacheManagement();
        }

        if (body.hasClass('sm-student-control_page_sm-student-control-settings')) {
            initSettingsPage();
        }

        if (body.hasClass('sm-student-control_page_sm-student-control-shortcode')) {
            initShortcodeGenerator();
        }
    });

})(jQuery);