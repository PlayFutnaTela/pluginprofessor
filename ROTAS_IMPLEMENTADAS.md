# Rotas Implementadas - Exemplos cURL

## Requisições para a API Externa (pmais-classes-api)

### 1️⃣ Buscar Turmas do Professor (API Externa)

**Rota Original:**
```bash
curl --location --request GET 'https://pmais-classes-api-staging.p4ed.com/api/class/classesByUser?schoolId=2003&userId=65&type=1&roleId=1' \
--header 'Authorization: Bearer eyJhbGciOiJSUzI1NiIsInR5cCIgOiAiSldUIiwia2lkIiA6ICJD...'
```

**Resposta Esperada:**
```json
[
  {
    "classId": 15458,
    "className": "Turma A",
    "schoolId": 2003,
    "studentCount": 50,
    ...outros campos
  },
  {
    "classId": 15459,
    "className": "Turma B",
    "schoolId": 2003,
    "studentCount": 45,
    ...outros campos
  }
]
```

---

### 2️⃣ Buscar Alunos da Turma (API Externa)

**Rota Original:**
```bash
curl --location --request GET 'https://pmais-classes-api-staging.p4ed.com/api/studentclass/assignedStudents/15458?limit=20&page=1' \
--header 'Authorization: Bearer eyJhbGciOiJSUzI1Ng...'
```

**Resposta Esperada:**
```json
{
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
}
```

---

## Requisições para o Plugin WordPress (AJAX)

### 1️⃣ Buscar Turmas via AJAX

**Método:** POST  
**URL:** `https://seu-wordpress.com/wp-admin/admin-ajax.php?action=get_classes_by_user`

**cURL:**
```bash
curl --location --request POST 'https://seu-wordpress.com/wp-admin/admin-ajax.php' \
--header 'Content-Type: application/x-www-form-urlencoded' \
--data-urlencode 'action=get_classes_by_user' \
--data-urlencode 'school_id=2003' \
--data-urlencode 'user_id=65' \
--data-urlencode 'type=1' \
--data-urlencode 'role_id=1' \
--data-urlencode 'security=NONCE_AQUI'
```

**JavaScript (jQuery):**
```javascript
jQuery.ajax({
    url: '/wp-admin/admin-ajax.php',
    type: 'POST',
    data: {
        action: 'get_classes_by_user',
        school_id: 2003,
        user_id: 65,
        type: 1,
        role_id: 1,
        security: nonce // Obter de wp_localize_script
    },
    success: function(response) {
        if (response.success) {
            console.log('Turmas:', response.data);
            console.log('Do cache?', response.from_cache);
        } else {
            console.error('Erro:', response.data.message);
        }
    }
});
```

**Resposta de Sucesso:**
```json
{
  "success": true,
  "data": [
    {
      "classId": 15458,
      "className": "Turma A",
      "schoolId": 2003,
      ...
    }
  ],
  "from_cache": false,
  "message": "Turmas recuperadas com sucesso da API"
}
```

---

### 2️⃣ Buscar Alunos da Turma via AJAX

**Método:** POST  
**URL:** `https://seu-wordpress.com/wp-admin/admin-ajax.php?action=get_assigned_students`

**cURL:**
```bash
curl --location --request POST 'https://seu-wordpress.com/wp-admin/admin-ajax.php' \
--header 'Content-Type: application/x-www-form-urlencoded' \
--data-urlencode 'action=get_assigned_students' \
--data-urlencode 'class_id=15458' \
--data-urlencode 'limit=20' \
--data-urlencode 'page=1' \
--data-urlencode 'security=NONCE_AQUI'
```

**JavaScript (jQuery):**
```javascript
jQuery.ajax({
    url: '/wp-admin/admin-ajax.php',
    type: 'POST',
    data: {
        action: 'get_assigned_students',
        class_id: 15458,
        limit: 20,
        page: 1,
        security: nonce // Obter de wp_localize_script
    },
    success: function(response) {
        if (response.success) {
            console.log('Alunos:', response.data.students);
            console.log('Total:', response.data.totalStudents);
            console.log('Do cache?', response.from_cache);
        } else {
            console.error('Erro:', response.data.message);
        }
    }
});
```

**Resposta de Sucesso:**
```json
{
  "success": true,
  "data": {
    "classId": 15458,
    "students": [
      {
        "id": 25540617,
        "name": "Aluno Quinto Ano Qa Doze",
        "emailp4ed": "aluno.quinto30@p4ed.com",
        "status": true,
        "cpf": "69820637066"
      }
    ],
    "totalStudents": 1,
    "page": 1,
    "limit": 20,
    "totalPages": 1
  },
  "from_cache": false,
  "message": "Alunos recuperados com sucesso da API"
}
```

---

## Fluxo Completo (Turmas → Alunos)

**JavaScript:**
```javascript
jQuery(document).ready(function($) {
    
    // Passo 1: Buscar turmas
    $.ajax({
        url: '/wp-admin/admin-ajax.php',
        type: 'POST',
        data: {
            action: 'get_classes_by_user',
            school_id: 2003,
            user_id: 65,
            security: nonce
        },
        success: function(response) {
            if (response.success && response.data.length > 0) {
                var firstClassId = response.data[0].classId;
                console.log('Primeira turma encontrada:', firstClassId);
                
                // Passo 2: Buscar alunos da primeira turma
                $.ajax({
                    url: '/wp-admin/admin-ajax.php',
                    type: 'POST',
                    data: {
                        action: 'get_assigned_students',
                        class_id: firstClassId,
                        limit: 20,
                        page: 1,
                        security: nonce
                    },
                    success: function(studentsResponse) {
                        if (studentsResponse.success) {
                            console.log('Alunos encontrados:', studentsResponse.data.students);
                            
                            // Exibir alunos
                            var students = studentsResponse.data.students;
                            var html = '<ul>';
                            students.forEach(function(student) {
                                html += '<li>' + student.name + ' (' + student.emailp4ed + ')</li>';
                            });
                            html += '</ul>';
                            
                            document.getElementById('students-list').innerHTML = html;
                        }
                    }
                });
            }
        }
    });
});
```

---

## Configuração Necessária

### 1. Configurar Token de Autenticação

O token Bearer deve ser salvo em uma opção do WordPress:

```php
// Executar uma vez no console do navegador (F12) ou via WP-CLI:
update_option('sm_student_control_api_token', 'seu_token_bearer_aqui');
```

Ou via WP-CLI:
```bash
wp option update sm_student_control_api_token 'seu_token_bearer_aqui'
```

### 2. Verificar Permissões

- Usuário deve ser **administrador** (`manage_options`)
- Usar nonce válido gerado pelo WordPress

---

## Status de Implementação

✅ **Classe de integração com API:** `includes/class-sm-student-control-external-api.php`  
✅ **Handlers AJAX:** `admin/class-sm-student-control-admin.php`  
✅ **Cache automático:** 1 hora por padrão  
✅ **Documentação:** `API_INTEGRATION.md`  
✅ **Exemplos:** `EXAMPLES.php`  

---

## Próximas Etapas Recomendadas

1. ✏️ Implementar página de configurações de token no admin
2. 🔄 Adicionar sincronização automática via cron
3. 📊 Adicionar logs detalhados de requisições
4. 🛡️ Implementar retry automático com exponential backoff
5. 🧪 Adicionar página de teste/debug para desenvolvedores
6. 🔒 Validar e sanitizar todos os dados retornados da API
