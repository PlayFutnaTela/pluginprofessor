# SM Student Control

Plugin WordPress para controle e monitoramento de estudantes por professores.

## Funcionalidades

### Backend Admin
- **Dashboard Administrativo**: Interface completa para configuração do plugin
- **Gerenciamento de Cache**: Controle de cache de dados dos estudantes
- **Gerador de Shortcode**: Ferramenta para gerar shortcodes personalizados
- **Logs e Debug**: Sistema de logs para monitoramento e depuração

### Frontend
- **Shortcode [painel_professor]**: Interface responsiva para professores visualizarem seus alunos
- **Lista de Estudantes**: Visualização paginada com filtros e busca
- **Detalhes do Estudante**: Informações completas sobre progresso, cursos e atividades
- **Estatísticas**: Métricas em tempo real sobre alunos e cursos

### API REST
- Endpoints para integração com sistemas externos
- Autenticação JWT para segurança
- Suporte a filtros e paginação

## Instalação

1. Faça upload da pasta `plugin-shortcode` para `/wp-content/plugins/`
2. Ative o plugin através do menu 'Plugins' no WordPress
3. Configure as opções na página de administração do plugin
4. Use o shortcode `[painel_professor]` em qualquer página

## Configuração

### Configurações Gerais
- **URL da API Externa**: Endpoint da API do LMS
- **Chave da API**: Token de autenticação
- **Modo Debug**: Ativar logs detalhados
- **Cache TTL**: Tempo de vida do cache em segundos

### Configurações do Shortcode
- **Template**: Tema visual do painel
- **Cor Primária**: Cor base da interface
- **Itens por Página**: Número de alunos por página
- **Largura**: Largura do container

## Uso do Shortcode

### Básico
```
[painel_professor]
```

### Com Parâmetros
```
[painel_professor template="dark" theme_color="#007cba" per_page="10"]
```

### Parâmetros Disponíveis
- `template`: Tema visual (default, dark, light, minimal)
- `theme_color`: Cor primária em hexadecimal
- `per_page`: Alunos por página (5-100)
- `width`: Largura do container (auto, 100%, 1200px, etc.)
- `show_stats`: Mostrar estatísticas (true/false)
- `show_filters`: Mostrar filtros (true/false)
- `title`: Título personalizado do painel

## Estrutura de Arquivos

```
plugin-shortcode/
├── sm-student-control.php          # Arquivo principal
├── includes/                       # Classes core
│   ├── class-loader.php           # Carregamento de dependências
│   ├── class-settings.php         # Configurações
│   ├── class-data.php             # Acesso a dados
│   ├── class-external-api.php     # API externa
│   ├── class-jwt.php              # Autenticação JWT
│   ├── class-cache.php            # Sistema de cache
│   ├── class-professor-students.php # Lógica de negócio
│   ├── class-helpers.php          # Funções utilitárias
│   └── class-rest-api.php         # API REST
├── admin/                         # Interface administrativa
│   ├── class-admin.php            # Classe principal do admin
│   └── views/                     # Templates do admin
│       ├── settings-page.php
│       ├── cache-management.php
│       ├── shortcode-generator.php
│       └── logs-debug.php
├── frontend/                      # Interface do usuário
│   ├── class-shortcode-handler.php # Manipulador do shortcode
│   ├── templates/                 # Templates do frontend
│   │   └── theme-default/         # Tema padrão
│   │       ├── dashboard.php
│   │       ├── students-list.php
│   │       └── student-details.php
│   └── assets/                    # Assets do frontend
│       ├── css/
│       ├── js/
│       └── images/
└── languages/                     # Arquivos de tradução
```

## API REST

### Endpoints

#### GET `/wp-json/sm-student-control/v1/professor/students`
Retorna lista de estudantes do professor autenticado.

**Parâmetros:**
- `search`: Busca por nome/email
- `course_id`: Filtrar por curso
- `page`: Página atual
- `per_page`: Itens por página

#### GET `/wp-json/sm-student-control/v1/students/{id}`
Retorna detalhes de um estudante específico.

#### POST `/wp-json/sm-student-control/v1/students/{id}/cache`
Atualiza o cache de um estudante.

#### GET `/wp-json/sm-student-control/v1/professor/stats`
Retorna estatísticas do professor.

#### GET `/wp-json/sm-student-control/v1/professor/courses`
Retorna cursos do professor.

#### GET `/wp-json/sm-student-control/v1/professor/export`
Exporta dados dos estudantes (CSV/JSON).

## Segurança

- Autenticação JWT obrigatória
- Verificação de permissões em todos os endpoints
- Sanitização de todas as entradas
- Nonces para proteção CSRF
- Validação de dados de entrada

## Cache

O plugin utiliza sistema de cache avançado para otimizar performance:

- **Transients do WordPress**: Cache temporário
- **Tabela personalizada**: Cache persistente de dados complexos
- **Atualização automática**: Cron job diário
- **Refresh manual**: Via interface admin e API

## Desenvolvimento

### Hooks Disponíveis

#### Ações
- `sm_sc_before_load`: Antes do carregamento do plugin
- `sm_sc_after_load`: Após o carregamento do plugin
- `sm_sc_student_data_loaded`: Quando dados do estudante são carregados
- `sm_sc_cache_refreshed`: Quando cache é atualizado

#### Filtros
- `sm_sc_student_data`: Filtrar dados do estudante
- `sm_sc_professor_permissions`: Modificar permissões do professor
- `sm_sc_shortcode_atts`: Filtrar atributos do shortcode
- `sm_sc_api_response`: Filtrar respostas da API

### Exemplo de Customização

```php
// Adicionar campo personalizado aos dados do estudante
add_filter('sm_sc_student_data', function($student_data) {
    $student_data['custom_field'] = get_user_meta($student_data['id'], 'custom_field', true);
    return $student_data;
});
```

## Suporte

Para suporte técnico, entre em contato com a equipe de desenvolvimento.

## Changelog

### Versão 1.0.0
- Lançamento inicial
- Interface admin completa
- Shortcode funcional
- API REST
- Sistema de cache
- Autenticação JWT

## Licença

Este plugin é distribuído sob a licença GPL v2 ou posterior.