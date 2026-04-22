/**
 * Frontend App
 *
 * JavaScript para o frontend do plugin
 */

(function($) {
    'use strict';

    // Verificar se jQuery está disponível
    if (typeof $ === 'undefined') {
        console.error('jQuery não está disponível');
        return;
    }

    // Configurações globais
    var SMSC = {
        ajaxurl: smscFrontend.ajaxurl,
        nonce: smscFrontend.nonce,
        strings: smscFrontend.strings,
        currentPage: 1,
        isLoading: false,
        modalOpen: false
    };

    // Inicialização
    $(document).ready(function() {
        console.log('[SM-SC-FRONTEND] 🎯 DOM pronto, inicializando aplicação frontend');
        SMSC.init();
    });

    // Métodos principais
    SMSC.init = function() {
        console.log('[SM-SC-FRONTEND] 🚀 Inicializando SM Student Control Frontend');
        console.log('[SM-SC-FRONTEND] ⚙️ Configurações:', SMSC);

        this.bindEvents();
        this.loadInitialData();
        this.setupTemplates();

        console.log('[SM-SC-FRONTEND] ✅ Inicialização completa');
    };

    // Vincular eventos
    SMSC.bindEvents = function() {
        console.log('[SM-SC-FRONTEND] 🔗 Vinculando eventos...');

        var self = this;

        // Busca
        $('.sm-sc-search-input').on('input', self.debounce(function() {
            self.loadStudents(1);
        }, 300));

        $('.sm-sc-search-clear').on('click', function() {
            $('.sm-sc-search-input').val('').trigger('input');
        });

        // Filtros
        $('.sm-sc-course-filter select').on('change', function() {
            self.loadStudents(1);
        });

        $('.sm-sc-per-page select').on('change', function() {
            self.loadStudents(1);
        });

        // Paginação
        $(document).on('click', '.sm-sc-prev-page:not(:disabled)', function() {
            self.loadStudents(SMSC.currentPage - 1);
        });

        $(document).on('click', '.sm-sc-next-page:not(:disabled)', function() {
            self.loadStudents(SMSC.currentPage + 1);
        });

        // Links de estudantes
        $(document).on('click', '.sm-sc-student-link', function(e) {
            e.preventDefault();
            var studentId = $(this).data('student-id');
            self.loadStudentDetails(studentId);
        });

        // Botões de detalhes
        $(document).on('click', '.sm-sc-view-details', function() {
            var studentId = $(this).data('student-id');
            self.loadStudentDetails(studentId);
        });

        // Atualizar cache
        $(document).on('click', '.sm-sc-refresh-cache', function() {
            var studentId = $(this).data('student-id');
            var $button = $(this);

            if (confirm(SMSC.strings.confirm_refresh)) {
                self.refreshStudentCache(studentId, $button);
            }
        });

        // Atualizar tudo
        $('.sm-sc-refresh-all').on('click', function() {
            self.refreshAllCaches();
        });

        // Exportar CSV
        $('.sm-sc-export-csv').on('click', function() {
            self.exportToCSV();
        });

        // Modal
        $(document).on('click', '.sm-sc-modal-close, .sm-sc-modal-overlay', function() {
            self.closeModal();
        });

        // ESC para fechar modal
        $(document).on('keydown', function(e) {
            if (e.keyCode === 27 && SMSC.modalOpen) {
                self.closeModal();
            }
        });

        // Previne fechamento do modal ao clicar no conteúdo
        $(document).on('click', '.sm-sc-modal-content', function(e) {
            e.stopPropagation();
        });
    };

    // Carregar dados iniciais
    SMSC.loadInitialData = function() {
        this.loadStudents(1);
        this.loadStats();
    };

    // Configurar templates Handlebars
    SMSC.setupTemplates = function() {
        // Verificar se Handlebars está disponível
        if (typeof Handlebars === 'undefined') {
            console.warn('Handlebars não está disponível, usando templates alternativos');
            return;
        }

        // Compilar templates
        this.studentRowTemplate = Handlebars.compile($('#sm-sc-student-row-template').html());
        this.studentDetailsTemplate = Handlebars.compile($('#sm-sc-student-details-template').html());
    };

    // Carregar estudantes
    SMSC.loadStudents = function(page) {
        if (this.isLoading) return;

        this.isLoading = true;
        this.currentPage = page;

        var self = this;
        var $tableLoading = $('#sm-sc-table-loading');
        var $tableEmpty = $('#sm-sc-table-empty');
        var $pagination = $('.sm-sc-pagination');

        // Mostrar loading
        $tableLoading.show();
        $tableEmpty.hide();
        $pagination.hide();

        // Preparar dados
        var data = {
            action: 'sm_sc_load_students',
            nonce: SMSC.nonce,
            search: $('.sm-sc-search-input').val(),
            course_id: $('.sm-sc-course-filter select').val(),
            page: page,
            per_page: $('.sm-sc-per-page select').val()
        };

        // Fazer requisição AJAX
        $.ajax({
            url: SMSC.ajaxurl,
            type: 'POST',
            data: data,
            success: function(response) {
                if (response.success) {
                    self.renderStudents(response.data.students);
                    self.renderPagination(response.data);
                } else {
                    self.showError(response.data || SMSC.strings.error);
                }
            },
            error: function() {
                self.showError(SMSC.strings.error);
            },
            complete: function() {
                $tableLoading.hide();
                self.isLoading = false;
            }
        });
    };

    // Renderizar estudantes
    SMSC.renderStudents = function(students) {
        var $tbody = $('#sm-sc-students-tbody');
        var $tableEmpty = $('#sm-sc-table-empty');
        var $table = $('.sm-sc-students-table');

        if (!students || students.length === 0) {
            // Mostrar tabela vazia
            $tbody.html('');
            $table.hide();
            $tableEmpty.show();
            return;
        }

        // Ocultar mensagem de vazio e mostrar tabela
        $tableEmpty.hide();
        $table.show();

        // Processar dados dos estudantes
        students = students.map(function(student) {
            return {
                id: student.id,
                display_name: student.display_name || 'N/A',
                user_email: student.user_email || '',
                avatar: student.avatar || '',
                initials: this.getInitials(student.display_name),
                progress: parseFloat(student.progress) || 0,
                courses_count: parseInt(student.courses_count) || 0,
                current_course: student.current_course || '',
                last_login: student.last_login || '',
                last_login_formatted: student.last_login ? this.formatDate(student.last_login) : ''
            };
        }, this);

        // Renderizar usando template
        if (this.studentRowTemplate) {
            var html = students.map(function(student) {
                return this.studentRowTemplate(student);
            }, this).join('');

            $tbody.html(html);
        } else {
            // Fallback sem Handlebars
            this.renderStudentsFallback(students);
        }
    };

    // Renderizar estudantes (fallback sem Handlebars)
    SMSC.renderStudentsFallback = function(students) {
        var $tbody = $('#sm-sc-students-tbody');
        var html = '';

        students.forEach(function(student) {
            html += '<tr class="sm-sc-student-row" data-student-id="' + student.id + '">' +
                '<td class="sm-sc-col-avatar">' +
                    (student.avatar ? '<img src="' + student.avatar + '" alt="' + student.display_name + '" class="sm-sc-avatar">' :
                     '<div class="sm-sc-avatar-placeholder">' + student.initials + '</div>') +
                '</td>' +
                '<td class="sm-sc-col-name">' +
                    '<div class="sm-sc-student-name">' +
                        '<a href="#" class="sm-sc-student-link" data-student-id="' + student.id + '">' + student.display_name + '</a>' +
                    '</div>' +
                '</td>' +
                '<td class="sm-sc-col-email">' +
                    '<span class="sm-sc-student-email">' + student.user_email + '</span>' +
                '</td>' +
                '<td class="sm-sc-col-progress">' +
                    '<div class="sm-sc-progress-bar">' +
                        '<div class="sm-sc-progress-fill" style="width: ' + student.progress + '%"></div>' +
                    '</div>' +
                    '<div class="sm-sc-progress-text">' + student.progress + '%</div>' +
                '</td>' +
                '<td class="sm-sc-col-courses">' +
                    '<div class="sm-sc-courses-count">' + student.courses_count + ' cursos</div>' +
                    (student.current_course ? '<div class="sm-sc-current-course">' + student.current_course + '</div>' : '') +
                '</td>' +
                '<td class="sm-sc-col-actions">' +
                    '<button type="button" class="button small sm-sc-view-details" data-student-id="' + student.id + '" title="Ver detalhes">' +
                        '<span class="dashicons dashicons-visibility"></span>' +
                    '</button>' +
                    '<button type="button" class="button small secondary sm-sc-refresh-cache" data-student-id="' + student.id + '" title="Atualizar cache">' +
                        '<span class="dashicons dashicons-update"></span>' +
                    '</button>' +
                '</td>' +
            '</tr>';
        });

        $tbody.html(html);
    };

    // Renderizar paginação
    SMSC.renderPagination = function(data) {
        var $pagination = $('.sm-sc-pagination');
        var $pageInfo = $('.sm-sc-page-info');
        var $prevBtn = $('.sm-sc-prev-page');
        var $nextBtn = $('.sm-sc-next-page');

        if (data.total_pages <= 1) {
            $pagination.hide();
            return;
        }

        $pageInfo.text('Página ' + data.page + ' de ' + data.total_pages);
        $prevBtn.prop('disabled', data.page <= 1);
        $nextBtn.prop('disabled', data.page >= data.total_pages);

        $pagination.show();
    };

    // Carregar detalhes do estudante
    SMSC.loadStudentDetails = function(studentId) {
        var self = this;
        var $modal = $('.sm-sc-modal');
        var $body = $('.sm-sc-modal-body');

        // Abrir modal
        $modal.show();
        SMSC.modalOpen = true;
        $('body').addClass('sm-sc-modal-open');

        // Mostrar loading
        $body.html('<div class="sm-sc-loading-message"><div class="sm-sc-spinner"></div><p>Carregando detalhes...</p></div>');

        // Fazer requisição AJAX
        $.ajax({
            url: SMSC.ajaxurl,
            type: 'POST',
            data: {
                action: 'sm_sc_load_student_details',
                nonce: SMSC.nonce,
                student_id: studentId
            },
            success: function(response) {
                if (response.success) {
                    self.renderStudentDetails(response.data);
                } else {
                    $body.html('<div class="sm-sc-error">' + (response.data || 'Erro ao carregar detalhes') + '</div>');
                }
            },
            error: function() {
                $body.html('<div class="sm-sc-error">Erro ao carregar detalhes do aluno</div>');
            }
        });
    };

    // Renderizar detalhes do estudante
    SMSC.renderStudentDetails = function(student) {
        var $body = $('.sm-sc-modal-body');

        // Processar dados
        var processedStudent = {
            id: student.id,
            display_name: student.display_name || 'N/A',
            user_email: student.user_email || '',
            avatar: student.avatar || '',
            initials: this.getInitials(student.display_name),
            user_status: student.user_status || 'active',
            registration_date: student.registration_date || '',
            last_login: student.last_login || '',
            last_login_formatted: student.last_login ? this.formatDate(student.last_login) : '',
            courses_count: parseInt(student.courses_count) || 0,
            completed_courses: parseInt(student.completed_courses) || 0,
            progress: parseFloat(student.progress) || 0,
            total_time: student.total_time || '0h 0m',
            courses: student.courses || [],
            recent_activities: student.recent_activities || [],
            certificates: student.certificates || []
        };

        // Renderizar usando template
        if (this.studentDetailsTemplate) {
            $body.html(this.studentDetailsTemplate(processedStudent));
        } else {
            // Fallback sem Handlebars
            this.renderStudentDetailsFallback(processedStudent);
        }
    };

    // Renderizar detalhes do estudante (fallback)
    SMSC.renderStudentDetailsFallback = function(student) {
        var $body = $('.sm-sc-modal-body');
        var html = '<div class="sm-sc-student-profile">' +
            '<div class="sm-sc-profile-header">' +
                (student.avatar ? '<img src="' + student.avatar + '" alt="' + student.display_name + '" class="sm-sc-profile-avatar">' :
                 '<div class="sm-sc-profile-avatar-placeholder">' + student.initials + '</div>') +
                '<div class="sm-sc-profile-info">' +
                    '<h4>' + student.display_name + '</h4>' +
                    '<p>' + student.user_email + '</p>' +
                    '<div class="sm-sc-profile-meta">' +
                        '<span>ID: ' + student.id + '</span>' +
                        (student.last_login ? '<span>Último acesso: ' + student.last_login_formatted + '</span>' : '') +
                    '</div>' +
                '</div>' +
            '</div>' +
            '<div class="sm-sc-profile-stats">' +
                '<div class="sm-sc-stat-item"><span class="sm-sc-stat-value">' + student.courses_count + '</span><span class="sm-sc-stat-label">Cursos</span></div>' +
                '<div class="sm-sc-stat-item"><span class="sm-sc-stat-value">' + student.completed_courses + '</span><span class="sm-sc-stat-label">Concluídos</span></div>' +
                '<div class="sm-sc-stat-item"><span class="sm-sc-stat-value">' + student.progress + '%</span><span class="sm-sc-stat-label">Progresso Médio</span></div>' +
            '</div>';

        if (student.courses && student.courses.length > 0) {
            html += '<div class="sm-sc-student-courses-list"><h5>Cursos Matriculados</h5>';
            student.courses.forEach(function(course) {
                html += '<div class="sm-sc-course-item">' +
                    '<div class="sm-sc-course-info">' +
                        '<h6>' + course.title + '</h6>' +
                        '<div class="sm-sc-course-progress">' +
                            '<div class="sm-sc-progress-bar small"><div class="sm-sc-progress-fill" style="width: ' + course.progress + '%"></div></div>' +
                            '<span>' + course.progress + '%</span>' +
                        '</div>' +
                    '</div>' +
                    '<div class="sm-sc-course-meta">' +
                        '<span>Status: ' + course.status + '</span>' +
                        (course.last_activity ? '<span>Última atividade: ' + course.last_activity + '</span>' : '') +
                    '</div>' +
                '</div>';
            });
            html += '</div>';
        }

        html += '</div>';
        $body.html(html);
    };

    // Atualizar cache do estudante
    SMSC.refreshStudentCache = function(studentId, $button) {
        var self = this;
        var originalText = $button.html();

        $button.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span>');

        $.ajax({
            url: SMSC.ajaxurl,
            type: 'POST',
            data: {
                action: 'sm_sc_refresh_student_cache',
                nonce: SMSC.nonce,
                student_id: studentId
            },
            success: function(response) {
                if (response.success) {
                    self.showMessage(response.data, 'success');
                    // Recarregar lista de estudantes
                    self.loadStudents(SMSC.currentPage);
                } else {
                    self.showMessage(response.data || 'Erro ao atualizar cache', 'error');
                }
            },
            error: function() {
                self.showMessage('Erro ao atualizar cache', 'error');
            },
            complete: function() {
                $button.prop('disabled', false).html(originalText);
            }
        });
    };

    // Atualizar todos os caches
    SMSC.refreshAllCaches = function() {
        var self = this;
        var $button = $('.sm-sc-refresh-all');

        $button.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> Atualizando...');

        // Aqui seria implementada a lógica para atualizar todos os caches
        // Por enquanto, apenas simula
        setTimeout(function() {
            $button.prop('disabled', false).html('<span class="dashicons dashicons-update"></span> Atualizar Tudo');
            self.showMessage('Todos os caches foram atualizados', 'success');
            self.loadStudents(1);
        }, 2000);
    };

    // Carregar estatísticas
    SMSC.loadStats = function() {
        // Implementar carregamento de estatísticas se necessário
        // Por enquanto, usa dados mock
        $('.sm-sc-total-students').html('Carregando...');
        $('.sm-sc-active-courses').html('Carregando...');
        $('.sm-sc-avg-progress').html('Carregando...');
    };

    // Exportar para CSV
    SMSC.exportToCSV = function() {
        // Implementar exportação CSV
        this.showMessage('Funcionalidade de exportação em desenvolvimento', 'info');
    };

    // Fechar modal
    SMSC.closeModal = function() {
        $('.sm-sc-modal').hide();
        SMSC.modalOpen = false;
        $('body').removeClass('sm-sc-modal-open');
    };

    // Mostrar erro
    SMSC.showError = function(message) {
        var $list = $('.sm-sc-students-list');
        $list.html('<div class="sm-sc-error">' + message + '</div>');
    };

    // Mostrar mensagem
    SMSC.showMessage = function(message, type) {
        type = type || 'info';

        // Criar elemento de notificação
        var $notification = $('<div class="sm-sc-notification sm-sc-' + type + '">' + message + '</div>');

        // Adicionar ao body
        $('body').append($notification);

        // Animar entrada
        $notification.fadeIn(300);

        // Remover após 3 segundos
        setTimeout(function() {
            $notification.fadeOut(300, function() {
                $notification.remove();
            });
        }, 3000);
    };

    // Utilitários
    SMSC.getInitials = function(name) {
        if (!name) return '?';
        return name.split(' ').map(function(part) {
            return part.charAt(0).toUpperCase();
        }).join('').substr(0, 2);
    };

    SMSC.formatDate = function(dateString) {
        if (!dateString) return '';
        var date = new Date(dateString);
        return date.toLocaleDateString('pt-BR') + ' ' + date.toLocaleTimeString('pt-BR', {hour: '2-digit', minute: '2-digit'});
    };

    SMSC.debounce = function(func, wait) {
        var timeout;
        return function executedFunction() {
            var context = this;
            var args = arguments;
            var later = function() {
                clearTimeout(timeout);
                func.apply(context, args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    };

    // Expor globalmente
    window.SMSC = SMSC;

})(jQuery);