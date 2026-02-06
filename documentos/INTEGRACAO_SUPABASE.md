# Integração com Supabase - Painel Office

## 📋 Visão Geral

Este documento descreve o plano de integração do Supabase como banco de dados próprio do Painel Office para armazenar configurações e dados específicos do painel que não pertencem ao banco XUI.

## 🎯 Objetivo

Criar um banco de dados separado para armazenar:
- **Configurações do Painel**: Logo, nome, cores, temas
- **Mensagens do Sistema**: Avisos, notificações
- **Logs de Atividades**: Auditoria de ações dos usuários
- **Configurações de Backup**: Tokens, credenciais
- **Preferências de Usuário**: Temas, idiomas, notificações

## 🏗️ Arquitetura Proposta

```
┌─────────────────────────────────────────────────────────────┐
│                    Painel Office (Laravel)                   │
│                                                               │
│  ┌──────────────────┐              ┌──────────────────┐    │
│  │   XUI Database   │              │    Supabase DB   │    │
│  │   (Remoto)       │              │    (Cloud)       │    │
│  │                  │              │                  │    │
│  │ • users          │              │ • panel_settings │    │
│  │ • lines          │              │ • panel_logs     │    │
│  │ • packages       │              │ • panel_messages │    │
│  │ • bouquets       │              │ • user_prefs     │    │
│  │ • streams        │              │ • backup_config  │    │
│  └──────────────────┘              └──────────────────┘    │
│         ▲                                    ▲               │
│         │                                    │               │
│         └────────────────┬───────────────────┘               │
│                          │                                   │
│                   Laravel Models                             │
└─────────────────────────────────────────────────────────────┘
```

## 📊 Estrutura de Tabelas Supabase

### 1. `panel_settings`
Armazena configurações globais do painel.

```sql
CREATE TABLE panel_settings (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    key VARCHAR(100) UNIQUE NOT NULL,
    value TEXT,
    type VARCHAR(50) DEFAULT 'string', -- string, json, boolean, integer
    description TEXT,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Índices
CREATE INDEX idx_panel_settings_key ON panel_settings(key);

-- Dados iniciais
INSERT INTO panel_settings (key, value, type, description) VALUES
('panel_name', 'Office IPTV', 'string', 'Nome do painel'),
('panel_logo', NULL, 'string', 'URL do logo do painel'),
('primary_color', '#f97316', 'string', 'Cor primária do painel'),
('secondary_color', '#1f2937', 'string', 'Cor secundária do painel'),
('dark_mode', 'true', 'boolean', 'Modo escuro ativado'),
('timezone', 'America/Sao_Paulo', 'string', 'Fuso horário padrão'),
('language', 'pt_BR', 'string', 'Idioma padrão'),
('welcome_message', 'Bem-vindo ao Painel Office', 'string', 'Mensagem de boas-vindas');
```

### 2. `panel_logs`
Registra atividades e auditoria do sistema.

```sql
CREATE TABLE panel_logs (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id INTEGER NOT NULL, -- ID do usuário no banco XUI
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50), -- client, package, reseller, etc
    entity_id INTEGER,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    metadata JSONB,
    created_at TIMESTAMP DEFAULT NOW()
);

-- Índices
CREATE INDEX idx_panel_logs_user_id ON panel_logs(user_id);
CREATE INDEX idx_panel_logs_action ON panel_logs(action);
CREATE INDEX idx_panel_logs_created_at ON panel_logs(created_at DESC);
CREATE INDEX idx_panel_logs_entity ON panel_logs(entity_type, entity_id);
```

### 3. `panel_messages`
Mensagens e avisos do sistema.

```sql
CREATE TABLE panel_messages (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    type VARCHAR(50) DEFAULT 'info', -- info, warning, error, success
    target VARCHAR(50) DEFAULT 'all', -- all, admin, reseller
    is_active BOOLEAN DEFAULT true,
    start_date TIMESTAMP,
    end_date TIMESTAMP,
    created_by INTEGER, -- ID do usuário que criou
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Índices
CREATE INDEX idx_panel_messages_active ON panel_messages(is_active);
CREATE INDEX idx_panel_messages_target ON panel_messages(target);
CREATE INDEX idx_panel_messages_dates ON panel_messages(start_date, end_date);
```

### 4. `user_preferences`
Preferências individuais de cada usuário.

```sql
CREATE TABLE user_preferences (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id INTEGER UNIQUE NOT NULL, -- ID do usuário no banco XUI
    theme VARCHAR(50) DEFAULT 'dark',
    language VARCHAR(10) DEFAULT 'pt_BR',
    notifications_enabled BOOLEAN DEFAULT true,
    email_notifications BOOLEAN DEFAULT true,
    dashboard_layout JSONB,
    preferences JSONB,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Índices
CREATE INDEX idx_user_preferences_user_id ON user_preferences(user_id);
```

### 5. `backup_config`
Configurações de backup e integrações.

```sql
CREATE TABLE backup_config (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    provider VARCHAR(50) NOT NULL, -- dropbox, google_drive, s3
    credentials JSONB NOT NULL, -- Criptografado
    frequency VARCHAR(50) DEFAULT 'daily', -- off, daily, weekly, monthly
    retention_days INTEGER DEFAULT 30,
    last_backup_at TIMESTAMP,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Índices
CREATE INDEX idx_backup_config_provider ON backup_config(provider);
CREATE INDEX idx_backup_config_active ON backup_config(is_active);
```

## 🔧 Implementação Laravel

### 1. Instalar Supabase PHP Client

```bash
composer require supabase/supabase-php
```

### 2. Configurar `.env`

```env
# Supabase Configuration
SUPABASE_URL=https://seu-projeto.supabase.co
SUPABASE_KEY=sua-chave-anon-publica
SUPABASE_SERVICE_KEY=sua-chave-service-role
```

### 3. Adicionar Conexão em `config/database.php`

```php
'connections' => [
    // ... conexões existentes ...
    
    'supabase' => [
        'driver' => 'pgsql',
        'url' => env('SUPABASE_URL'),
        'host' => parse_url(env('SUPABASE_URL'), PHP_URL_HOST),
        'port' => env('SUPABASE_PORT', '5432'),
        'database' => env('SUPABASE_DATABASE', 'postgres'),
        'username' => env('SUPABASE_USERNAME', 'postgres'),
        'password' => env('SUPABASE_PASSWORD', ''),
        'charset' => 'utf8',
        'prefix' => '',
        'prefix_indexes' => true,
        'schema' => 'public',
        'sslmode' => 'require',
    ],
],
```

### 4. Criar Models

**`app/Models/PanelSetting.php`**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PanelSetting extends Model
{
    protected $connection = 'supabase';
    protected $table = 'panel_settings';
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public static function get(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        
        if (!$setting) {
            return $default;
        }

        return match($setting->type) {
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $setting->value,
            'json' => json_decode($setting->value, true),
            default => $setting->value,
        };
    }

    public static function set(string $key, $value, string $type = 'string')
    {
        if ($type === 'json' && is_array($value)) {
            $value = json_encode($value);
        }

        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type]
        );
    }
}
```

**`app/Models/PanelLog.php`**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PanelLog extends Model
{
    protected $connection = 'supabase';
    protected $table = 'panel_logs';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    
    protected $fillable = [
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'description',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public static function log(string $action, array $data = [])
    {
        return self::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'entity_type' => $data['entity_type'] ?? null,
            'entity_id' => $data['entity_id'] ?? null,
            'description' => $data['description'] ?? null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => $data['metadata'] ?? null,
        ]);
    }
}
```

### 5. Criar Service para Configurações

**`app/Services/PanelConfigService.php`**
```php
<?php

namespace App\Services;

use App\Models\PanelSetting;
use Illuminate\Support\Facades\Cache;

class PanelConfigService
{
    protected int $cacheTTL = 3600; // 1 hora

    public function get(string $key, $default = null)
    {
        return Cache::remember("panel_setting_{$key}", $this->cacheTTL, function() use ($key, $default) {
            return PanelSetting::get($key, $default);
        });
    }

    public function set(string $key, $value, string $type = 'string')
    {
        $result = PanelSetting::set($key, $value, $type);
        Cache::forget("panel_setting_{$key}");
        return $result;
    }

    public function all(): array
    {
        return Cache::remember('panel_settings_all', $this->cacheTTL, function() {
            return PanelSetting::all()
                ->mapWithKeys(fn($setting) => [$setting->key => $setting->value])
                ->toArray();
        });
    }

    public function clearCache()
    {
        Cache::flush();
    }
}
```

### 6. Usar nas Views

**Blade Directive**
```php
// app/Providers/AppServiceProvider.php
use App\Services\PanelConfigService;
use Illuminate\Support\Facades\Blade;

public function boot()
{
    Blade::directive('panelConfig', function ($expression) {
        return "<?php echo app(App\Services\PanelConfigService::class)->get($expression); ?>";
    });
}
```

**Uso nas Views**
```blade
<h1>@panelConfig('panel_name')</h1>

@if(@panelConfig('dark_mode'))
    <body class="dark-theme">
@else
    <body class="light-theme">
@endif
```

## 🚀 Passos de Implementação

### Fase 1: Setup Inicial
1. ✅ Criar conta no Supabase
2. ✅ Criar novo projeto
3. ✅ Executar scripts SQL para criar tabelas
4. ✅ Configurar credenciais no `.env`
5. ✅ Instalar dependências

### Fase 2: Models e Services
1. ✅ Criar models (PanelSetting, PanelLog, etc)
2. ✅ Criar PanelConfigService
3. ✅ Criar middleware de logging
4. ✅ Adicionar blade directives

### Fase 3: Migração de Dados
1. ✅ Migrar configurações atuais para Supabase
2. ✅ Atualizar SettingsController para usar Supabase
3. ✅ Implementar sistema de logs

### Fase 4: Features Avançadas
1. ✅ Sistema de mensagens do painel
2. ✅ Preferências de usuário
3. ✅ Backup automático para Dropbox
4. ✅ Dashboard de logs e auditoria

## 🔒 Segurança

### Row Level Security (RLS)
```sql
-- Habilitar RLS
ALTER TABLE panel_settings ENABLE ROW LEVEL SECURITY;
ALTER TABLE panel_logs ENABLE ROW LEVEL SECURITY;

-- Políticas de acesso
CREATE POLICY "Allow authenticated users to read settings"
ON panel_settings FOR SELECT
TO authenticated
USING (true);

CREATE POLICY "Allow admins to modify settings"
ON panel_settings FOR ALL
TO authenticated
USING (auth.jwt() ->> 'role' = 'admin');
```

### Criptografia de Dados Sensíveis
```php
use Illuminate\Support\Facades\Crypt;

// Salvar credenciais criptografadas
$encrypted = Crypt::encryptString(json_encode($credentials));
BackupConfig::create(['credentials' => $encrypted]);

// Recuperar credenciais
$decrypted = json_decode(Crypt::decryptString($config->credentials), true);
```

## 📊 Vantagens do Supabase

✅ **PostgreSQL Gerenciado**: Banco robusto e escalável
✅ **API REST Automática**: Endpoints gerados automaticamente
✅ **Realtime**: WebSockets para atualizações em tempo real
✅ **Storage**: Armazenamento de arquivos (logos, imagens)
✅ **Auth**: Sistema de autenticação integrado (opcional)
✅ **Dashboard**: Interface web para gerenciar dados
✅ **Backups Automáticos**: Backup diário incluído
✅ **Free Tier Generoso**: 500MB storage, 2GB bandwidth

## 💰 Custos

**Free Tier:**
- 500 MB de banco de dados
- 1 GB de armazenamento de arquivos
- 2 GB de largura de banda
- Ideal para começar

**Pro Plan ($25/mês):**
- 8 GB de banco de dados
- 100 GB de armazenamento
- 250 GB de largura de banda
- Backups point-in-time

## 📝 Próximos Passos

1. Criar conta no Supabase
2. Executar scripts SQL
3. Configurar credenciais
4. Implementar models e services
5. Migrar página de Configurações para usar Supabase
6. Implementar sistema de logs
7. Adicionar upload de logo do painel

## 🔗 Links Úteis

- [Supabase Dashboard](https://app.supabase.com)
- [Documentação Supabase](https://supabase.com/docs)
- [Supabase PHP Client](https://github.com/supabase-community/supabase-php)
- [PostgreSQL Docs](https://www.postgresql.org/docs/)
