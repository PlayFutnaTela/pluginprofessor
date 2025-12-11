# Integração com API Externa - SM Student Control

## Visão Geral

Este documento descreve como integrar o plugin SM Student Control com a API externa de classes (pmais-classes-api) para buscar dados de turmas e alunos.

## Endpoints AJAX Implementados

### 1. Buscar Turmas de um Professor

**Endpoint:** `wp-admin/admin-ajax.php?action=get_classes_by_user`

**Método:** POST

**Parâmetros:**

| Parâmetro | Tipo | Obrigatório | Descrição |
|-----------|------|-------------|-----------|
| `school_id` | int | Sim | ID da escola |
| `user_id` | int | Sim | ID do professor |
| `type` | int | Não | Tipo de filtro (padrão: 1) |
| `role_id` | int | Não | ID da função (padrão: 1) |
| `security` | string | Sim | Nonce para verificação de segurança |

**Resposta de Sucesso (200):**
```json
{
  "success": true,
  "data": {
    "classId": 15458,
    "className": "Nome da Turma",
    "schoolId": 2003,
    "studentCount": 50,
    ...outros campos da turma
  },
  "from_cache": false,
  "message": "Turmas recuperadas com sucesso da API"
}
```

**Resposta de Erro:**
```json
{
  "success": false,
  "message": "Descrição do erro",
  "code": "error_code"
}
```

---

### 2. Buscar Alunos de uma Turma

**Endpoint:** `wp-admin/admin-ajax.php?action=get_assigned_students`

**Método:** POST

**Parâmetros:**

| Parâmetro | Tipo | Obrigatório | Descrição |
|-----------|------|-------------|-----------|
| `class_id` | int | Sim | ID da turma |
| `limit` | int | Não | Limite de registros por página (padrão: 20, máximo: 100) |
| `page` | int | Não | Número da página (padrão: 1) |
| `security` | string | Sim | Nonce para verificação de segurança |

**Resposta de Sucesso (200):**
```json
{
  "success": true,
  "data": {
    "classId": 15458,
    "students": [
      {
        "id": 25540617,
        "originId": null,
        "name": "Aluno Quinto Ano Qa Doze",
        "inscricao": "0170491",
        "emailp4ed": "aluno.quinto30@p4ed.com",
        "status": true,
        "enrollment": null,
        "cpf": "69820637066",
        "photoUrl": null
      },
      {
        "id": 25540611,
        "originId": null,
        "name": "Aluno Quinto Ano Qa Dez",
        "inscricao": "0170485",
        "emailp4ed": "aluno.quinto32@p4ed.com",
        "status": true,
        "enrollment": null,
        "cpf": "45731858012",
        "photoUrl": null
      }
    ],
    "totalStudents": 2,
    "page": 1,
    "limit": 20,
    "totalPages": 1
  },
  "from_cache": false,
  "message": "Alunos recuperados com sucesso da API"
}
```

**Resposta de Erro:**
```json
{
  "success": false,
  "message": "Descrição do erro",
  "code": "error_code"
}
```

---

## Exemplos de Uso (JavaScript)

### Exemplo 1: Buscar Turmas

```javascript
// Assumindo que 'sm_student_control' está disponível via wp_localize_script
jQuery(document).ready(function($) {
    
    // Buscar turmas de um professor
    $.ajax({
        url: sm_student_control.ajax_url,
        type: 'POST',
        data: {
            action: 'get_classes_by_user',
            school_id: 2003,
            user_id: 65,
            type: 1,
            role_id: 1,
            security: sm_student_control.nonce
        },
        success: function(response) {
            if (response.success) {
                console.log('Turmas recuperadas:', response.data);
                // Processar dados das turmas
                var classes = response.data;
                if (classes && Array.isArray(classes)) {
                    classes.forEach(function(item) {
                        console.log('Turma:', item.classId, '-', item.className);
                    });
                }
            } else {
                console.error('Erro:', response.data.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Erro na requisição:', error);
        }
    });
});
```

### Exemplo 2: Buscar Alunos de uma Turma

```javascript
jQuery(document).ready(function($) {
    
    // Buscar alunos de uma turma específica
    $.ajax({
        url: sm_student_control.ajax_url,
        type: 'POST',
        data: {
            action: 'get_assigned_students',
            class_id: 15458,
            limit: 20,
            page: 1,
            security: sm_student_control.nonce
        },
        success: function(response) {
            if (response.success) {
                console.log('Alunos recuperados:', response.data);
                var classData = response.data;
                
                if (classData.students && Array.isArray(classData.students)) {
                    classData.students.forEach(function(student) {
                        console.log('Aluno:', student.id, '-', student.name);
                    });
                }
                
                console.log('Total de alunos:', classData.totalStudents);
                console.log('Página', classData.page, 'de', classData.totalPages);
            } else {
                console.error('Erro:', response.data.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Erro na requisição:', error);
        }
    });
});
```

### Exemplo 3: Fluxo Completo (Turmas → Alunos)

```javascript
jQuery(document).ready(function($) {
    
    // Função para buscar turmas
    function getTeacherClasses(schoolId, userId) {
        return $.ajax({
            url: sm_student_control.ajax_url,
            type: 'POST',
            data: {
                action: 'get_classes_by_user',
                school_id: schoolId,
                user_id: userId,
                security: sm_student_control.nonce
            }
        });
    }
    
    // Função para buscar alunos
    function getClassStudents(classId, page = 1) {
        return $.ajax({
            url: sm_student_control.ajax_url,
            type: 'POST',
            data: {
                action: 'get_assigned_students',
                class_id: classId,
                limit: 20,
                page: page,
                security: sm_student_control.nonce
            }
        });
    }
    
    // Executar fluxo completo
    getTeacherClasses(2003, 65)
        .done(function(response) {
            if (response.success) {
                var classes = response.data;
                console.log('Total de turmas:', Array.isArray(classes) ? classes.length : 1);
                
                // Buscar alunos da primeira turma
                if (classes && classes[0]) {
                    var firstClassId = classes[0].classId;
                    
                    return getClassStudents(firstClassId);
                }
            }
        })
        .done(function(response) {
            if (response.success) {
                var students = response.data.students;
                console.log('Alunos da turma:', students.length);
                students.forEach(function(student) {
                    console.log('- ' + student.name + ' (' + student.emailp4ed + ')');
                });
            }
        })
        .fail(function(error) {
            console.error('Erro no fluxo:', error);
        });
});
```

---

## Configuração do Token de Autenticação

### Via WordPress Options

Para configurar o token de autenticação da API, execute o seguinte comando:

```php
// Adicione isto em functions.php ou em um hook de inicialização
update_option('sm_student_control_api_token', 'seu_token_bearer_aqui');
```

Ou use a interface de administração para adicionar uma página de configurações:

```php
// Adicionar página de configurações ao menu
add_menu_page(
    'SM Student Control Settings',
    'Student Control Settings',
    'manage_options',
    'sm-student-control-settings',
    'sm_student_control_settings_page'
);

// Renderizar página de configurações
function sm_student_control_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php _e('Configurações - Integração com API Externa'); ?></h1>
        <form method="post" action="options.php">
            <?php settings_fields('sm_student_control_settings'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="sm_student_control_api_token"><?php _e('Token de Autenticação'); ?></label>
                    </th>
                    <td>
                        <input type="password" 
                               id="sm_student_control_api_token" 
                               name="sm_student_control_api_token" 
                               value="<?php echo esc_attr(get_option('sm_student_control_api_token')); ?>" 
                               class="regular-text" />
                        <p class="description"><?php _e('Insira o token Bearer para autenticação na API'); ?></p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
```

---

## Cache

Os dados dos endpoints AJAX são automaticamente cacheados por **1 hora** após a primeira requisição:

- **Turmas:** Cacheadas por chave `sm_student_control_classes_{school_id}_{user_id}`
- **Alunos:** Cacheados por chave `sm_student_control_students_{class_id}`

Para limpar o cache manualmente:

```php
// Limpar cache de turmas
delete_transient('sm_student_control_classes_2003_65');

// Limpar cache de alunos
delete_transient('sm_student_control_students_15458');
```

---

## Estrutura de Arquivos

- **[includes/class-sm-student-control-external-api.php](includes/class-sm-student-control-external-api.php)** - Classe principal de integração com API
- **[admin/class-sm-student-control-admin.php](admin/class-sm-student-control-admin.php)** - Handlers AJAX
- **[includes/class-sm-student-control-loader.php](includes/class-sm-student-control-loader.php)** - Loader que carrega a classe de integração

---

## Tratamento de Erros

Todos os endpoints retornam erros estruturados:

```json
{
  "success": false,
  "message": "Descrição do erro",
  "code": "codigo_do_erro"
}
```

Códigos de erro possíveis:
- `missing_auth_token` - Token de autenticação não configurado
- `api_error` - Erro na chamada da API
- `json_decode_error` - Erro ao decodificar resposta JSON
- `permission_denied` - Usuário sem permissão para acessar
- `missing_parameters` - Parâmetros obrigatórios não fornecidos

---

## Notas Importantes

1. **Segurança:** Todos os endpoints requerem permissão `manage_options` (administrador)
2. **CORS:** A API externa pode ter restrições de CORS; a requisição é feita do servidor WordPress
3. **Timeout:** O timeout padrão para requisições é de 30 segundos
4. **SSL:** As requisições usam verificação SSL ativada por padrão

---

## Troubleshooting

### Token não configurado
Se receber erro `missing_auth_token`, configure o token via:
```php
update_option('sm_student_control_api_token', 'seu_token_aqui');
```

### Erro "Permissão insuficiente"
Certifique-se de que o usuário logado é administrador (tem permissão `manage_options`)

### Dados do cache desatualizado
Force a atualização limpando o cache:
```php
delete_transient('sm_student_control_classes_2003_65');
delete_transient('sm_student_control_students_15458');
```

---

## Próximos Passos

1. Implementar página de configurações no admin
2. Adicionar sincronização automática via cron
3. Adicionar logging detalhado de requisições
4. Implementar retry automático em caso de falha
5. Adicionar página de teste/debug para desenvolvedores
