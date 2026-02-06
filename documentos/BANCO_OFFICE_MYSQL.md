# 🗄️ Banco de Dados Office - MySQL Local

## 📋 Visão Geral

Criar um banco de dados MySQL separado no mesmo servidor para armazenar dados específicos do Painel Office.

## 🎯 Vantagens

- ✅ Usa infraestrutura existente (MySQL já instalado)
- ✅ Não precisa aprender PostgreSQL
- ✅ Backup único para tudo
- ✅ Sem custos adicionais
- ✅ Sem dependência de serviços externos
- ✅ Controle total dos dados

## 🚀 Passo a Passo

### 1. Criar Banco de Dados

Conecte no MySQL e execute:

```sql
-- Criar banco de dados
CREATE DATABASE painel_office CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Criar usuário (opcional, pode usar root)
CREATE USER 'office_user'@'localhost' IDENTIFIED BY 'senha_segura';
GRANT ALL PRIVILEGES ON painel_office.* TO 'office_user'@'localhost';
FLUSH PRIVILEGES;
```

### 2. Configurar Laravel

**Adicione no `.env`:**
```env
# Banco de Dados Office (Configurações do Painel)
OFFICE_DB_CONNECTION=mysql
OFFICE_DB_HOST=127.0.0.1
OFFICE_DB_PORT=3306
OFFICE_DB_DATABASE=painel_office
OFFICE_DB_USERNAME=root
OFFICE_DB_PASSWORD=sua_senha
```

**Adicione em `config/database.php`:**
```php
'connections' => [
    // ... conexões existentes (xui, mysql) ...
    
    'office' => [
        'driver' => 'mysql',
        'url' => env('OFFICE_DATABASE_URL'),
        'host' => env('OFFICE_DB_HOST', '127.0.0.1'),
        'port' => env('OFFICE_DB_PORT', '3306'),
        'database' => env('OFFICE_DB_DATABASE', 'painel_office'),
        'username' => env('OFFICE_DB_USERNAME', 'root'),
        'password' => env('OFFICE_DB_PASSWORD', ''),
        'unix_socket' => env('OFFICE_DB_SOCKET', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'prefix_indexes' => true,
        'strict' => true,
        'engine' => null,
        'options' => extension_loaded('pdo_mysql') ? array_filter([
            PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
        ]) : [],
    ],
],
```

### 3. Criar Tabelas

Execute os scripts SQL abaixo no banco `painel_office`:

#### Tabela: `panel_settings`
```sql
CREATE TABLE panel_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(100) UNIQUE NOT NULL,
    `value` TEXT,
    `type` VARCHAR(50) DEFAULT 'string',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados iniciais
INSERT INTO panel_settings (`key`, `value`, `type`, description) VALUES
('panel_name', 'Office IPTV', 'string', 'Nome do painel'),
('panel_logo', NULL, 'string', 'URL do logo do painel'),
('primary_color', '#f97316', 'string', 'Cor primária do painel'),
('secondary_color', '#1f2937', 'string', 'Cor secundária do painel'),
('dark_mode', 'true', 'boolean', 'Modo escuro ativado'),
('timezone', 'America/Sao_Paulo', 'string', 'Fuso horário padrão'),
('language', 'pt_BR', 'string', 'Idioma padrão'),
('welcome_message', 'Bem-vindo ao Painel Office', 'string', 'Mensagem de boas-vindas'),
('smtp_host', NULL, 'string', 'Servidor SMTP'),
('smtp_port', '587', 'integer', 'Porta SMTP'),
('smtp_username', NULL, 'string', 'Usuário SMTP'),
('smtp_password', NULL, 'string', 'Senha SMTP (criptografada)'),
('backup_frequency', 'daily', 'string', 'Frequência de backup'),
('backup_retention_days', '30', 'integer', 'Dias para manter backups'),
('dropbox_token', NULL, 'string', 'Token do Dropbox (criptografado)');
```

#### Tabela: `panel_logs`
```sql
CREATE TABLE panel_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50),
    entity_id INT,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at),
    INDEX idx_entity (entity_type, entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Tabela: `panel_messages`
```sql
CREATE TABLE panel_messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    `type` VARCHAR(50) DEFAULT 'info',
    target VARCHAR(50) DEFAULT 'all',
    is_active BOOLEAN DEFAULT TRUE,
    start_date TIMESTAMP NULL,
    end_date TIMESTAMP NULL,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active (is_active),
    INDEX idx_target (target),
    INDEX idx_dates (start_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Tabela: `user_preferences`
```sql
CREATE TABLE user_preferences (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE NOT NULL,
    theme VARCHAR(50) DEFAULT 'dark',
    language VARCHAR(10) DEFAULT 'pt_BR',
    notifications_enabled BOOLEAN DEFAULT TRUE,
    email_notifications BOOLEAN DEFAULT TRUE,
    dashboard_layout JSON,
    preferences JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 4. Criar Models Laravel

**`app/Models/PanelSetting.php`:**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PanelSetting extends Model
{
    protected $connection = 'office';
    protected $table = 'panel_settings';
    
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

    /**
     * Obter valor de configuração
     */
    public static function get(string $key, $default = null)
    {
        return Cache::remember("panel_setting_{$key}", 3600, function() use ($key, $default) {
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
        });
    }

    /**
     * Definir valor de configuração
     */
    public static function set(string $key, $value, string $type = 'string')
    {
        if ($type === 'json' && is_array($value)) {
            $value = json_encode($value);
        }

        $result = self::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type]
        );

        Cache::forget("panel_setting_{$key}");
        
        return $result;
    }

    /**
     * Obter todas as configurações
     */
    public static function getAll(): array
    {
        return Cache::remember('panel_settings_all', 3600, function() {
            return self::all()
                ->mapWithKeys(fn($setting) => [$setting->key => $setting->value])
                ->toArray();
        });
    }
}
```

**`app/Models/PanelLog.php`:**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PanelLog extends Model
{
    protected $connection = 'office';
    protected $table = 'panel_logs';
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

    /**
     * Registrar log
     */
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

### 5. Usar nas Controllers

**Exemplo em `SettingsController.php`:**
```php
use App\Models\PanelSetting;
use App\Models\PanelLog;

public function update(Request $request)
{
    $validated = $request->validate([
        'panel_name' => 'required|string|max:100',
        'primary_color' => 'required|string|max:7',
        // ... outras validações
    ]);

    foreach ($validated as $key => $value) {
        PanelSetting::set($key, $value);
    }

    // Registrar log
    PanelLog::log('settings_updated', [
        'description' => 'Configurações do painel atualizadas',
        'metadata' => $validated,
    ]);

    return redirect()->back()->with('success', 'Configurações salvas!');
}
```

### 6. Usar nas Views

**Criar Helper Global:**

**`app/Helpers/PanelHelper.php`:**
```php
<?php

use App\Models\PanelSetting;

if (!function_exists('panel_config')) {
    function panel_config(string $key, $default = null)
    {
        return PanelSetting::get($key, $default);
    }
}
```

**Registrar em `composer.json`:**
```json
"autoload": {
    "files": [
        "app/Helpers/PanelHelper.php"
    ]
}
```

**Executar:**
```bash
composer dump-autoload
```

**Usar nas Views:**
```blade
<h1>{{ panel_config('panel_name') }}</h1>

<style>
    :root {
        --primary-color: {{ panel_config('primary_color', '#f97316') }};
    }
</style>

@if(panel_config('dark_mode'))
    <body class="dark">
@endif
```

## 🔄 Backup

**Script de Backup:**
```bash
# Backup do banco office
mysqldump -u root -p painel_office > backup_office_$(date +%Y%m%d).sql

# Backup de tudo (XUI + Office)
mysqldump -u root -p --all-databases > backup_completo_$(date +%Y%m%d).sql
```

## ✅ Vantagens desta Abordagem

1. ✅ **Simples**: Usa tecnologia que você já conhece
2. ✅ **Rápido**: Banco local = zero latência
3. ✅ **Confiável**: Sem dependência de internet
4. ✅ **Gratuito**: Sem custos mensais
5. ✅ **Integrado**: Backup único para tudo
6. ✅ **Escalável**: MySQL aguenta muito tráfego

## 📊 Comparação

| Recurso | Supabase | MySQL Local |
|---------|----------|-------------|
| Custo | $0-25/mês | $0 |
| Latência | ~50-200ms | <1ms |
| Controle | Limitado | Total |
| Backup | Automático | Manual |
| Escalabilidade | Alta | Média |
| Complexidade | Baixa | Baixa |

## 🎯 Conclusão

Para o Painel Office, **MySQL Local é a melhor escolha**:
- Você já tem a infraestrutura
- Dados sensíveis ficam no seu servidor
- Performance máxima
- Zero custos
