# Console Logs - SM Student Control Plugin

# Console Logs - SM Student Control Plugin

Este documento mapeia todos os pontos onde foram inseridos console logs para facilitar a remoção quando a fase de testes for concluída.

## 🧹 Remoção Automática de Logs

### Script de Remoção Automática

Um script PHP foi criado para remover automaticamente todos os console logs:

**Arquivo:** `remove-console-logs.php`

**Como executar:**
```bash
cd /caminho/para/plugin
php remove-console-logs.php
```

**O que o script faz:**
- ✅ Remove todas as linhas `error_log()` com prefixo `[SM-SC-*]`
- ✅ Remove todas as linhas `console.log()` com prefixo `[SM-SC-*]`
- ✅ Remove os arquivos `CONSOLE-LOGS.md` e `remove-console-logs.php`
- ✅ Mostra relatório detalhado das remoções

**⚠️ IMPORTANTE:**
- **Faça backup** dos arquivos antes de executar o script!
- O script remove permanentemente os logs
- Para restaurar, será necessário refazer as modificações manuais

### Remoção Manual (Alternativa)

Se preferir remover manualmente, use as seguintes buscas no VS Code:

**Para PHP (error_log):**
```
error_log\(.*\[SM-SC.*
```

**Para JavaScript (console.log):**
```
console\.log\(.*\[SM-SC.*
```

**Comandos de busca e substituição:**
- Buscar: `^\s*error_log\(.*\[SM-SC.*\);\s*$`
- Substituir: (deixar vazio)
- Usar regex: ✅
- Em todos os arquivos: ✅

---

## 📋 Estratégia de Logging

- **PHP**: Utiliza `error_log()` que aparece nos logs do WordPress (`wp-content/debug.log`)
- **JavaScript**: Utiliza `console.log()` que aparece no console do navegador
- **Prefixos**: Todos os logs usam prefixos padronizados para fácil identificação
- **Modo Debug**: Alguns logs só aparecem quando o modo debug está ativado

## 🔍 Arquivos com Console Logs

### 1. `sm-student-control.php` (Arquivo Principal)
**Localização**: Raiz do plugin
**Linhas**: 15-25, 35-50, 60-75

```php
// Inicialização do plugin
error_log('[SM-SC] 🚀 Plugin inicializado - Versão: ' . SM_STUDENT_CONTROL_VERSION);
error_log('[SM-SC] 📁 Diretório do plugin: ' . SM_STUDENT_CONTROL_DIR);
error_log('[SM-SC] 🌐 URL do plugin: ' . SM_STUDENT_CONTROL_URL);

// Ativação
error_log('[SM-SC] 🔧 Iniciando ativação do plugin');
error_log('[SM-SC] 🗄️ Tabelas criadas/atualizadas');
error_log('[SM-SC] ⚙️ Configuração padrão definida: ' . $key . ' = ' . $value);
error_log('[SM-SC] 🔄 Rewrite rules atualizadas');
error_log('[SM-SC] ✅ Plugin ativado com sucesso');

// Desativação
error_log('[SM-SC] 🛑 Iniciando desativação do plugin');
error_log('[SM-SC] 🧹 Cache limpo durante desativação');
error_log('[SM-SC] ✅ Plugin desativado com sucesso');
```

### 2. `includes/class-loader.php` (Loader Principal)
**Localização**: `includes/class-loader.php`
**Linhas**: 15-45, 55-75

```php
// Inicialização
error_log('[SM-SC-LOADER] 🎯 Inicializando loader do plugin');
error_log('[SM-SC-LOADER] 📦 Carregando dependências...');
error_log('[SM-SC-LOADER] 🔧 Carregando classes core...');
error_log('[SM-SC-LOADER] ✅ class-data.php carregada');
error_log('[SM-SC-LOADER] ✅ class-external-api.php carregada');
// ... (mais logs de carregamento de classes)

// Inicialização de hooks
error_log('[SM-SC-LOADER] 🔗 Inicializando hooks...');
error_log('[SM-SC-LOADER] 🔗 Hook textdomain registrado');
error_log('[SM-SC-LOADER] 🔗 Hooks de assets registrados');
error_log('[SM-SC-LOADER] 🔗 Handlers AJAX registrados');
error_log('[SM-SC-LOADER] 🔗 Todos os hooks inicializados');
```

### 3. `includes/class-settings.php` (Configurações)
**Localização**: `includes/class-settings.php`
**Linhas**: 45-50

```php
// Apenas quando debug_mode está ativado
if (self::get('debug_mode')) {
    error_log('[SM-SC-SETTINGS] 📖 Configuração lida: ' . $key . ' = ' . $value);
}
```

### 4. `includes/class-data.php` (Acesso a Dados)
**Localização**: `includes/class-data.php`
**Linhas**: 20-25

```php
error_log('[SM-SC-DATA] 👥 Iniciando busca de estudantes - Professor ID: ' . $professor_id . ', School ID: ' . $school_id);
error_log('[SM-SC-DATA] 🔍 Parâmetros da busca: ' . json_encode($args));
```

### 5. `includes/class-jwt.php` (Autenticação JWT)
**Localização**: `includes/class-jwt.php`
**Linhas**: 25-35, 45-50

```php
// Inicialização
error_log('[SM-SC-JWT] 🔐 Inicializando configurações JWT');
error_log('[SM-SC-JWT] 🔑 Nova chave secreta JWT gerada');
error_log('[SM-SC-JWT] ✅ Chave secreta JWT carregada');

// Validação
error_log('[SM-SC-JWT] 🔍 Iniciando validação de token JWT');
```

### 6. `includes/class-cache.php` (Sistema de Cache)
**Localização**: `includes/class-cache.php`
**Linhas**: 25-35

```php
error_log('[SM-SC-CACHE] 💾 Inicializando sistema de cache');
error_log('[SM-SC-CACHE] ⏱️ Duração do cache: ' . self::$default_duration . ' segundos');
error_log('[SM-SC-CACHE] 🔄 Hook de limpeza diária registrado');
```

### 7. `frontend/class-shortcode-handler.php` (Shortcode Handler)
**Localização**: `frontend/class-shortcode-handler.php`
**Linhas**: 15-20, 25-45

```php
// Registro do shortcode
error_log('[SM-SC-SHORTCODE] 🎯 Registrando shortcode [painel_professor]');
error_log('[SM-SC-SHORTCODE] ✅ Shortcode registrado com sucesso');

// Renderização
error_log('[SM-SC-SHORTCODE] 🎨 Iniciando renderização do shortcode [painel_professor]');
error_log('[SM-SC-SHORTCODE] 📝 Atributos recebidos: ' . json_encode($atts));
error_log('[SM-SC-SHORTCODE] 🚫 Usuário não logado, mostrando tela de login');
error_log('[SM-SC-SHORTCODE] 🚫 Dados do professor não encontrados ou token inválido');
error_log('[SM-SC-SHORTCODE] ✅ Professor autenticado: ' . json_encode($professor_data));
error_log('[SM-SC-SHORTCODE] ⚙️ Atributos processados: ' . json_encode($atts));
error_log('[SM-SC-SHORTCODE] ✅ Shortcode renderizado com sucesso, tamanho: ' . strlen($output) . ' caracteres');
```

### 8. `admin/class-admin.php` (Interface Admin)
**Localização**: `admin/class-admin.php`
**Linhas**: 15-25, 35-45

```php
// Inicialização
error_log('[SM-SC-ADMIN] 👨‍💼 Inicializando interface administrativa');
error_log('[SM-SC-ADMIN] ✅ Hooks administrativos registrados');

// Menus
error_log('[SM-SC-ADMIN] 📋 Adicionando menus administrativos');
error_log('[SM-SC-ADMIN] 📄 Menu principal adicionado');
```

### 9. `frontend/assets/js/frontend-app.js` (JavaScript Frontend)
**Localização**: `frontend/assets/js/frontend-app.js`
**Linhas**: 15-25, 30-40

```javascript
// Inicialização
console.log('[SM-SC-FRONTEND] 🎯 DOM pronto, inicializando aplicação frontend');
console.log('[SM-SC-FRONTEND] 🚀 Inicializando SM Student Control Frontend');
console.log('[SM-SC-FRONTEND] ⚙️ Configurações:', SMSC);
console.log('[SM-SC-FRONTEND] ✅ Inicialização completa');

// Eventos
console.log('[SM-SC-FRONTEND] 🔗 Vinculando eventos...');
```

### 10. `admin/assets/js/admin-scripts.js` (JavaScript Admin)
**Localização**: `admin/assets/js/admin-scripts.js`
**Linhas**: 5-15

```javascript
console.log('[SM-SC-ADMIN] 🎯 Inicializando scripts administrativos');
console.log('[SM-SC-ADMIN] 💾 Inicializando gerenciamento de cache');
console.log('[SM-SC-ADMIN] 🔄 Solicitação para atualizar todo o cache');
```

## 🛠️ Como Remover os Console Logs

### Opção 1: Remoção Automática (Recomendada)

Use o script `remove-console-logs.php` incluído no plugin:

```bash
php remove-console-logs.php
```

### Opção 2: Remoção Manual

Use busca e substituição no VS Code:

**Para PHP:**
- Buscar: `^\s*error_log\(.*\[SM-SC.*\);\s*$`
- Substituir: (vazio)
- Usar regex: ✅

**Para JavaScript:**
- Buscar: `^\s*console\.log\(.*\[SM-SC.*\);\s*$`
- Substituir: (vazio)
- Usar regex: ✅

## 📊 Monitoramento dos Logs

### Logs PHP (WordPress)
- Localização: `wp-content/debug.log`
- Ativação: Adicione `define('WP_DEBUG', true);` e `define('WP_DEBUG_LOG', true);` ao `wp-config.php`

### Logs JavaScript (Navegador)
- Abra o console do navegador (F12 → Console)
- Os logs aparecerão durante a interação com o plugin

### Filtros Úteis

```bash
# Ver apenas logs do SM-SC
tail -f wp-content/debug.log | grep "\[SM-SC\]"

# Contar logs por tipo
grep "\[SM-SC\]" wp-content/debug.log | grep -o "\[SM-SC-[A-Z]*\]" | sort | uniq -c
```

## ⚠️ Notas Importantes

- **Performance**: Os logs podem impactar a performance em produção
- **Segurança**: Alguns logs podem expor dados sensíveis
- **Debug Mode**: Considere usar uma constante para ativar/desativar logs condicionalmente
- **Backup**: Sempre faça backup antes de remover logs automaticamente

## 🎯 Próximos Passos

1. Teste todas as funcionalidades com os logs ativos
2. Monitore os logs para identificar problemas
3. Quando os testes estiverem concluídos, use os scripts de remoção acima
4. Remova este arquivo `CONSOLE-LOGS.md` da pasta do projeto