OBTER INFORMAÇÕES / Ações de consulta
Documentação completa para todos os endpoints de recuperação de dados na API de administração do XUI.ONE

Visão geral
Os endpoints GET INFO permitem que você recupere informações sobre os recursos do seu painel XUI.ONE sem fazer modificações. Essas são operações somente leitura que retornam dados em formato JSON.

Pontos de extremidade disponíveis
Ponto final	Descrição	Devoluções
get_lines	Obtenha todas as linhas de assinatura.	Conjunto de objetos de linha
get_users	Obtenha todas as contas de usuário	Conjunto de objetos de usuário
get_streams	Veja todas as transmissões ao vivo	Matriz de objetos de fluxo
get_channels	Acesse todos os canais	Conjunto de objetos de canal
get_movies	Obtenha todos os filmes em VOD	Conjunto de objetos de filme
get_series_list	Veja todas as séries de TV	Conjunto de objetos de série
get_episodes	Veja todos os episódios	Conjunto de objetos de episódio
get_mags	Adquira todos os dispositivos MAG	Conjunto de objetos MAG
get_enigmas	Obtenha todos os dispositivos Enigma2	Conjunto de objetos Enigma2
get_stations	Ouça todas as estações de rádio.	Conjunto de objetos de estação
get_packages	Obtenha todos os pacotes de assinatura.	Matriz de objetos de pacote
🔍 Padrões de Uso Comuns
Consulta básica
curl "http://your-server.com/cSbuFLhp/?api_key=8D3135D30437F86EAE2FA4A2A8345000&action=ACTION_NAME"
Com paginação (onde suportada)
curl "http://your-server.com/cSbuFLhp/?api_key=8D3135D30437F86EAE2FA4A2A8345000&action=ACTION_NAME&limit=100&offset=0"
Com filtragem (onde disponível)
curl "http://your-server.com/cSbuFLhp/?api_key=8D3135D30437F86EAE2FA4A2A8345000&action=get_episodes&series_id=123"
📝 Documentação detalhada do endpoint
1. Obtenha todas as linhas de assinatura
Ação: get_lines

Descrição: Recupera todas as linhas de assinatura (contas M3U/Xtream Codes) no seu painel.

Solicitar:

curl "http://your-server.com/cSbuFLhp/?api_key=8D3135D30437F86EAE2FA4A2A8345000&action=get_lines"
Exemplo de resposta:

{
  "status": "STATUS_SUCCESS",
  "data": [
    {
      "id": "123",
      "username": "testuser",
      "password": "testpass123",
      "exp_date": "1735689600",
      "max_connections": "1",
      "is_trial": "0",
      "enabled": "1",
      "is_restreamer": "0",
      "bouquet": "[1,2,3]",
      "notes": "Premium customer",
      "created_at": "1609459200"
    }
  ]
}
Casos de uso:

Monitorar assinaturas ativas
Verifique as datas de validade
Lista de clientes para exportação
Exibir limites de conexão
contas de auditoria
2. Obter todos os usuários
Ação: get_users

Descrição: Recupera todas as contas de usuário (administradores, revendedores, usuários) em seu painel.

Solicitar:

curl "http://your-server.com/cSbuFLhp/?api_key=8D3135D30437F86EAE2FA4A2A8345000&action=get_users"
Exemplo de resposta:

{
  "status": "STATUS_SUCCESS",
  "data": [
    {
      "id": "1",
      "username": "admin",
      "member_group_id": "1",
      "email": "admin@example.com",
      "date_registered": "1609459200",
      "status": "1"
    }
  ]
}
Casos de uso:

Listar todos os usuários do painel
Auditar permissões de usuário
Veja as contas de revendedor.
Exportar dados do usuário
3. Veja todas as transmissões ao vivo
Ação: get_streams

Descrição: Recupera todas as transmissões de TV ao vivo configuradas no seu painel.

Solicitar:

curl "http://your-server.com/cSbuFLhp/?api_key=8D3135D30437F86EAE2FA4A2A8345000&action=get_streams"
Exemplo de resposta:

{
  "status": "STATUS_SUCCESS",
  "data": [
    {
      "id": "456",
      "stream_display_name": "CNN HD",
      "stream_source": "http://source.example.com/stream.m3u8",
      "category_id": "1",
      "stream_icon": "http://example.com/icon.png",
      "enabled": "1"
    }
  ]
}
Casos de uso:

Listar todos os fluxos
Verificar o estado da transmissão
Monitorar fontes de fluxo
Exportar configurações de fluxo
Ver categorias de transmissão
4. Obter todos os canais
Ação: get_channels

Descrição: Recupera todas as configurações de canal.

Solicitar:

curl "http://your-server.com/cSbuFLhp/?api_key=8D3135D30437F86EAE2FA4A2A8345000&action=get_channels"
Casos de uso:

Catálogo de canais da lista
Visualizar mapeamentos EPG
Lista de canais de exportação
Confira as categorias do canal
5. Obtenha todos os filmes
Ação: get_movies

Descrição: Recupera todos os filmes VOD no seu painel.

Solicitar:

curl "http://your-server.com/cSbuFLhp/?api_key=8D3135D30437F86EAE2FA4A2A8345000&action=get_movies"
Exemplo de resposta:

{
  "status": "STATUS_SUCCESS",
  "data": [
    {
      "id": "789",
      "name": "The Matrix",
      "stream_type": "movie",
      "added": "1609459200",
      "category_id": "5",
      "cover": "http://example.com/poster.jpg",
      "plot": "A computer hacker learns..."
    }
  ]
}
Casos de uso:

Catálogo de filmes em lista
Ver metadados do filme
Monitorar o uso de armazenamento
Exportar lista de filmes
Confira as categorias
6. Obtenha todas as séries
Ação: get_series_list

Descrição: Recupera todas as séries de TV no seu painel.

Solicitar:

curl "http://your-server.com/cSbuFLhp/?api_key=8D3135D30437F86EAE2FA4A2A8345000&action=get_series_list"
Exemplo de resposta:

{
  "status": "STATUS_SUCCESS",
  "data": [
    {
      "id": "101",
      "name": "Breaking Bad",
      "category_id": "3",
      "cover": "http://example.com/cover.jpg",
      "plot": "A high school chemistry teacher...",
      "num_seasons": "5"
    }
  ]
}
Casos de uso:

Catálogo da série List
Ver metadados da série
Verificar a quantidade de episódios
Lista de séries de exportação
7. Obtenha todos os episódios
Ação: get_episodes

Descrição: Recupera todos os episódios. Pode ser filtrado por série.

Solicitar (Todos os Episódios):

curl "http://your-server.com/cSbuFLhp/?api_key=8D3135D30437F86EAE2FA4A2A8345000&action=get_episodes"
Solicitação (Série Específica):

curl "http://your-server.com/cSbuFLhp/?api_key=8D3135D30437F86EAE2FA4A2A8345000&action=get_episodes&series_id=101"
Parâmetros:

series_id(opcional) - Filtrar por ID da série
Casos de uso:

Listar todos os episódios
Ver metadados do episódio
Filtrar por série
Verificar disponibilidade
8. Obtenha todos os dispositivos MAG
Ação: get_mags

Descrição: Recupera todas as assinaturas de decodificadores MAG.

Solicitar:

curl "http://your-server.com/cSbuFLhp/?api_key=8D3135D30437F86EAE2FA4A2A8345000&action=get_mags"
Exemplo de resposta:

{
  "status": "STATUS_SUCCESS",
  "data": [
    {
      "id": "202",
      "mac": "00:1A:79:XX:XX:XX",
      "exp_date": "1735689600",
      "enabled": "1",
      "bouquet": "[1,2,3]"
    }
  ]
}
Casos de uso:

Lista de dispositivos MAG
Ver endereços MAC
Verifique as datas de validade
Monitorar o status do dispositivo
9. Obtenha todos os dispositivos Enigma2
Ação: get_enigmas

Descrição: Recupera todas as assinaturas do decodificador Enigma2.

Solicitar:

curl "http://your-server.com/cSbuFLhp/?api_key=8D3135D30437F86EAE2FA4A2A8345000&action=get_enigmas"
Casos de uso:

Lista de dispositivos Enigma2
Ver estado do dispositivo
Verifique as datas de validade
Monitorar conexões
10. Obtenha todas as estações de rádio.
Ação: get_stations

Descrição: Recupera todos os canais de rádio online.

Solicitar:

curl "http://your-server.com/cSbuFLhp/?api_key=8D3135D30437F86EAE2FA4A2A8345000&action=get_stations"
Casos de uso:

Lista de estações de rádio
Ver metadados da estação
Verifique as fontes de fluxo
Lista de estações de exportação
11. Obtenha todos os pacotes
Ação: get_packages

Descrição: Recupera todos os pacotes de assinatura (buquês).

Solicitar:

curl "http://your-server.com/cSbuFLhp/?api_key=8D3135D30437F86EAE2FA4A2A8345000&action=get_packages"
Exemplo de resposta:

{
  "status": "STATUS_SUCCESS",
  "data": [
    {
      "id": "1",
      "package_name": "Premium Package",
      "streams": "[1,2,3,4,5]",
      "bouquet_channels": "100"
    }
  ]
}

API do Line - Gerenciamento de Assinaturas
Documentação completa para gerenciamento de linhas de assinatura na API de administração do XUI.ONE.

📋 Visão geral
A API Line oferece controle total sobre as linhas de assinatura (contas M3U/Xtream Codes). Você pode criar, modificar, ativar, desativar e excluir linhas de assinatura programaticamente.

Operações disponíveis
Operação	Ação	Método	Descrição
Visualizar	get_line	PEGAR	Obtenha detalhes específicos da linha
Criar	create_line	PUBLICAR	Criar nova assinatura
Atualizar	edit_line	PUBLICAR	Modificar linha existente
Excluir	delete_line	PUBLICAR	Remover linha permanentemente
Habilitar	enable_line	PUBLICAR	Ativar linha desativada
Desativar	disable_line	PUBLICAR	Suspender temporariamente a linha
Proibir	ban_line	PUBLICAR	Bloquear conta abusiva
Desbanir	unban_line	PUBLICAR	Remover proibição da linha
🔍 Obtenha uma linha específica
Ação:get_line
Obtenha informações detalhadas sobre uma linha de assinatura específica.

Parâmetros:

id(obrigatório) - ID da linha
Solicitar:

curl "http://your-server.com/cSbuFLhp/?api_key=8D3135D30437F86EAE2FA4A2A8345000&action=get_line&id=123"
Resposta:

{
  "status": "STATUS_SUCCESS",
  "data": {
    "id": "123",
    "username": "testuser",
    "password": "testpass123",
    "exp_date": "1735689600",
    "max_connections": "1",
    "is_trial": "0",
    "enabled": "1",
    "is_restreamer": "0",
    "bouquet": "[1,2,3]",
    "notes": "Premium customer",
    "created_at": "1609459200"
  }
}
➕ Criar nova linha
Ação:create_line
Crie uma nova linha de assinatura com as configurações especificadas.

Parâmetros obrigatórios:

username- Nome de usuário para a linha
password- Senha da linha
Parâmetros opcionais:

max_connections- Número máximo de conexões simultâneas (padrão: 1)
exp_date- Data de validade (AAAA-MM-DD, data e hora ou formato relativo como "1 mês")
is_trial- Status do teste (0 = Não, 1 = Sim)
is_restreamer- Status de revendedor (0 = Não, 1 = Sim)
bouquets_selected[]- Conjunto de IDs de buquês/pacotes
notes- Notas do administrador
contact- Informações de contato
isp_lock- Bloquear para um endereço IP específico
Solicitar:

curl -X POST "http://your-server.com/cSbuFLhp/?api_key=8D3135D30437F86EAE2FA4A2A8345000&action=create_line" \
  -d "username=newuser" \
  -d "password=securepass123" \
  -d "max_connections=1" \
  -d "exp_date=2025-12-31" \
  -d "bouquets_selected[]=1" \
  -d "bouquets_selected[]=2" \
  -d "notes=Premium customer"
Resposta:

{
  "status": "STATUS_SUCCESS",
  "data": {
    "line_id": "456",
    "username": "newuser",
    "password": "securepass123"
  }
}
✏️ Editar linha existente
Ação:edit_line
Modificar as configurações de uma linha de assinatura existente.

Parâmetros obrigatórios:

id- ID da linha a ser editada
Parâmetros opcionais:

username- Novo nome de usuário
password- Nova Senha
max_connections- Atualizar limite de conexões
exp_date- Atualizar data de validade
is_trial- Atualizar status do teste
bouquets_selected[]- Atualizar pacotes atribuídos
notes- Notas de atualização
Solicitar:

curl -X POST "http://your-server.com/cSbuFLhp/?api_key=8D3135D30437F86EAE2FA4A2A8345000&action=edit_line" \
  -d "id=123" \
  -d "password=newpassword456" \
  -d "max_connections=2" \
  -d "exp_date=2026-06-30"
Resposta:

{
  "status": "STATUS_SUCCESS",
  "data": {
    "message": "Line updated successfully"
  }
}
🗑️ Excluir linha
Ação:delete_line
Remover permanentemente uma linha de assinatura do painel.

⚠️ Aviso: Esta ação não pode ser desfeita!

Parâmetros obrigatórios:

id- ID da linha a ser excluída
Solicitar:

curl -X POST "http://your-server.com/cSbuFLhp/?api_key=8D3135D30437F86EAE2FA4A2A8345000&action=delete_line" \
  -d "id=123"
Resposta:

{
  "status": "STATUS_SUCCESS",
  "data": {
    "message": "Line deleted successfully"
  }
}
✅ Ativar linha
Ação:enable_line
Ativar uma linha previamente desativada.

Parâmetros obrigatórios:

id- ID da linha para ativar
Solicitar:

curl -X POST "http://your-server.com/cSbuFLhp/?api_key=8D3135D30437F86EAE2FA4A2A8345000&action=enable_line" \
  -d "id=123"
⛔ Desativar linha
Ação:disable_line
Suspender temporariamente uma linha sem excluí-la.

Parâmetros obrigatórios:

id- ID da linha a ser desativada
Solicitar:

curl -X POST "http://your-server.com/cSbuFLhp/?api_key=8D3135D30437F86EAE2FA4A2A8345000&action=disable_line" \
  -d "id=123"
🚫 Linha de proibição
Ação:ban_line
Bloqueie permanentemente uma conta abusiva ou fraudulenta.

Parâmetros obrigatórios:

id- ID do Line para banir
Solicitar:

curl -X POST "http://your-server.com/cSbuFLhp/?api_key=8D3135D30437F86EAE2FA4A2A8345000&action=ban_line" \
  -d "id=123"
✓ Linha de desbloqueio
Ação:unban_line
Remover a proibição de uma linha previamente banida.

Parâmetros obrigatórios:

id- ID do Line para desbloquear
Solicitar:

curl -X POST "http://your-server.com/cSbuFLhp/?api_key=8D3135D30437F86EAE2FA4A2A8345000&action=unban_line" \
  -d "id=123"
💻 Exemplos de código
Python
import requests
from datetime import datetime, timedelta

class XUILineManager:
    def __init__(self, base_url, api_key):
        self.base_url = base_url
        self.api_key = api_key

    def _make_request(self, action, data=None):
        """Make API request"""
        params = {
            "api_key": self.api_key,
            "action": action
        }

        if data:
            response = requests.post(self.base_url, params=params, data=data)
        else:
            response = requests.get(self.base_url, params=params)

        result = response.json()
        if result["status"] != "STATUS_SUCCESS":
            raise Exception(f"API Error: {result.get('error', 'Unknown error')}")

        return result["data"]

    def get_line(self, line_id):
        """Get specific line details"""
        return self._make_request("get_line", {"id": line_id})

    def create_line(self, username, password, **kwargs):
        """Create new subscription line"""
        data = {
            "username": username,
            "password": password,
            **kwargs
        }
        return self._make_request("create_line", data)

    def edit_line(self, line_id, **kwargs):
        """Edit existing line"""
        data = {"id": line_id, **kwargs}
        return self._make_request("edit_line", data)

    def delete_line(self, line_id):
        """Delete line"""
        return self._make_request("delete_line", {"id": line_id})

    def enable_line(self, line_id):
        """Enable line"""
        return self._make_request("enable_line", {"id": line_id})

    def disable_line(self, line_id):
        """Disable line"""
        return self._make_request("disable_line", {"id": line_id})

    def ban_line(self, line_id):
        """Ban line"""
        return self._make_request("ban_line", {"id": line_id})

    def unban_line(self, line_id):
        """Unban line"""
        return self._make_request("unban_line", {"id": line_id})

# Usage Example
manager = XUILineManager(
    base_url="http://your-server.com/cSbuFLhp/",
    api_key="8D3135D30437F86EAE2FA4A2A8345000"
)

# Create new line
new_line = manager.create_line(
    username="customer001",
    password="secure123",
    max_connections=1,
    exp_date="2025-12-31",
    bouquets_selected=[1, 2, 3],
    notes="Premium customer"
)
print(f"Created line ID: {new_line['line_id']}")

# Get line details
line = manager.get_line(123)
print(f"Line: {line['username']} - Expires: {line['exp_date']}")

# Edit line
manager.edit_line(
    line_id=123,
    password="newpassword",
    max_connections=2
)
print("Line updated successfully")

# Disable line
manager.disable_line(123)
print("Line disabled")
PHP
<?php
class XUILineManager {
    private $baseUrl;
    private $apiKey;

    public function __construct($baseUrl, $apiKey) {
        $this->baseUrl = $baseUrl;
        $this->apiKey = $apiKey;
    }

    private function makeRequest($action, $data = null) {
        $url = $this->baseUrl . "?api_key=" . $this->apiKey . "&action=" . $action;

        if ($data) {
            $options = [
                'http' => [
                    'method' => 'POST',
                    'header' => 'Content-type: application/x-www-form-urlencoded',
                    'content' => http_build_query($data)
                ]
            ];
            $context = stream_context_create($options);
            $response = file_get_contents($url, false, $context);
        } else {
            $response = file_get_contents($url);
        }

        $result = json_decode($response, true);

        if ($result['status'] !== 'STATUS_SUCCESS') {
            throw new Exception("API Error: " . ($result['error'] ?? 'Unknown error'));
        }

        return $result['data'];
    }

    public function getLine($lineId) {
        return $this->makeRequest("get_line", ["id" => $lineId]);
    }

    public function createLine($username, $password, $options = []) {
        $data = array_merge([
            "username" => $username,
            "password" => $password
        ], $options);

        return $this->makeRequest("create_line", $data);
    }

    public function editLine($lineId, $updates) {
        $data = array_merge(["id" => $lineId], $updates);
        return $this->makeRequest("edit_line", $data);
    }

    public function deleteLine($lineId) {
        return $this->makeRequest("delete_line", ["id" => $lineId]);
    }

    public function enableLine($lineId) {
        return $this->makeRequest("enable_line", ["id" => $lineId]);
    }

    public function disableLine($lineId) {
        return $this->makeRequest("disable_line", ["id" => $lineId]);
    }
}

// Usage Example
$manager = new XUILineManager(
    "http://your-server.com/cSbuFLhp/",
    "8D3135D30437F86EAE2FA4A2A8345000"
);

// Create new line
$newLine = $manager->createLine(
    "customer001",
    "secure123",
    [
        "max_connections" => 1,
        "exp_date" => "2025-12-31",
        "notes" => "Premium customer"
    ]
);
echo "Created line ID: " . $newLine['line_id'] . "\n";

// Edit line
$manager->editLine(123, [
    "password" => "newpassword",
    "max_connections" => 2
]);
echo "Line updated successfully\n";
?>
JavaScript (Node.js)
const fetch = require('node-fetch');

class XUILineManager {
    constructor(baseUrl, apiKey) {
        this.baseUrl = baseUrl;
        this.apiKey = apiKey;
    }

    async makeRequest(action, data = null) {
        const url = `${this.baseUrl}?api_key=${this.apiKey}&action=${action}`;

        const options = {
            method: data ? 'POST' : 'GET',
        };

        if (data) {
            const formData = new URLSearchParams();
            for (const [key, value] of Object.entries(data)) {
                if (Array.isArray(value)) {
                    value.forEach(v => formData.append(`${key}[]`, v));
                } else {
                    formData.append(key, value);
                }
            }
            options.body = formData;
        }

        const response = await fetch(url, options);
        const result = await response.json();

        if (result.status !== 'STATUS_SUCCESS') {
            throw new Error(`API Error: ${result.error || 'Unknown error'}`);
        }

        return result.data;
    }

    async getLine(lineId) {
        const url = `${this.baseUrl}?api_key=${this.apiKey}&action=get_line&id=${lineId}`;
        const response = await fetch(url);
        const result = await response.json();

        if (result.status !== 'STATUS_SUCCESS') {
            throw new Error(`API Error: ${result.error || 'Unknown error'}`);
        }

        return result.data;
    }

    async createLine(username, password, options = {}) {
        const data = { username, password, ...options };
        return this.makeRequest('create_line', data);
    }

    async editLine(lineId, updates) {
        const data = { id: lineId, ...updates };
        return this.makeRequest('edit_line', data);
    }

    async deleteLine(lineId) {
        return this.makeRequest('delete_line', { id: lineId });
    }

    async enableLine(lineId) {
        return this.makeRequest('enable_line', { id: lineId });
    }

    async disableLine(lineId) {
        return this.makeRequest('disable_line', { id: lineId });
    }

    async banLine(lineId) {
        return this.makeRequest('ban_line', { id: lineId });
    }

    async unbanLine(lineId) {
        return this.makeRequest('unban_line', { id: lineId });
    }
}

// Usage Example
(async () => {
    const manager = new XUILineManager(
        'http://your-server.com/cSbuFLhp/',
        '8D3135D30437F86EAE2FA4A2A8345000'
    );

    try {
        // Create new line
        const newLine = await manager.createLine(
            'customer001',
            'secure123',
            {
                max_connections: 1,
                exp_date: '2025-12-31',
                bouquets_selected: [1, 2, 3],
                notes: 'Premium customer'
            }
        );
        console.log('Created line ID:', newLine.line_id);

        // Get line details
        const line = await manager.getLine(123);
        console.log('Line:', line.username, '- Expires:', line.exp_date);

        // Edit line
        await manager.editLine(123, {
            password: 'newpassword',
            max_connections: 2
        });
        console.log('Line updated successfully');

    } catch (error) {
        console.error('Error:', error.message);
    }
})();
🎯 Casos de uso comuns
1. Criar linhas em massa
def bulk_create_lines(manager, count=10, package_ids=[1,2,3]):
    """Create multiple lines at once"""
    import random
    import string

    created_lines = []

    for i in range(count):
        # Generate random credentials
        username = f"user{''.join(random.choices(string.digits, k=6))}"
        password = ''.join(random.choices(string.ascii_letters + string.digits, k=12))

        try:
            result = manager.create_line(
                username=username,
                password=password,
                max_connections=1,
                exp_date="1month",  # 1 month from now
                bouquets_selected=package_ids
            )

            created_lines.append({
                'id': result['line_id'],
                'username': username,
                'password': password
            })

            print(f"✓ Created: {username}")

        except Exception as e:
            print(f"✗ Failed to create line {i+1}: {e}")

    return created_lines
2. Desativar automaticamente linhas expiradas
from datetime import datetime

def disable_expired_lines(manager):
    """Automatically disable lines that have expired"""
    # Get all lines
    response = requests.get(
        manager.base_url,
        params={"api_key": manager.api_key, "action": "get_lines"}
    )
    lines = response.json()['data']

    now = datetime.now().timestamp()
    expired_count = 0

    for line in lines:
        exp_date = int(line['exp_date'])

        # Check if expired and still enabled
        if exp_date < now and line['enabled'] == '1':
            try:
                manager.disable_line(line['id'])
                print(f"✓ Disabled expired line: {line['username']}")
                expired_count += 1
            except Exception as e:
                print(f"✗ Failed to disable {line['username']}: {e}")

    print(f"\nDisabled {expired_count} expired lines")
3. Redefinir senha da linha
import secrets
import string

def reset_line_password(manager, line_id):
    """Generate and set a new secure password"""
    # Generate secure password
    alphabet = string.ascii_letters + string.digits
    new_password = ''.join(secrets.choice(alphabet) for i in range(16))

    # Update line
    manager.edit_line(line_id, password=new_password)

    return new_password

# Usage
new_pass = reset_line_password(manager, 123)
print(f"New password: {new_pass}")
4. Prorrogar a data de validade
from datetime import datetime, timedelta

def extend_subscription(manager, line_id, months=1):
    """Extend line expiration by specified months"""
    # Get current line
    line = manager.get_line(line_id)

    # Calculate new expiration
    current_exp = datetime.fromtimestamp(int(line['exp_date']))
    new_exp = current_exp + timedelta(days=30 * months)
    new_exp_timestamp = int(new_exp.timestamp())

    # Update line
    manager.edit_line(line_id, exp_date=new_exp_timestamp)

    print(f"Extended to: {new_exp.strftime('%Y-%m-%d')}")
5. Atualize sua linha para um pacote diferente.
def upgrade_line_package(manager, line_id, new_package_ids):
    """Change line's assigned packages"""
    manager.edit_line(
        line_id,
        bouquets_selected=new_package_ids
    )

    print(f"Upgraded line {line_id} to packages: {new_package_ids}")

# Usage
upgrade_line_package(manager, 123, [1, 2, 3, 4, 5])  # Premium package
6. Credenciais da Linha de Exportação
import csv

def export_lines_to_csv(manager, filename="lines_export.csv"):
    """Export all lines to CSV file"""
    # Get all lines
    response = requests.get(
        manager.base_url,
        params={"api_key": manager.api_key, "action": "get_lines"}
    )
    lines = response.json()['data']

    # Write to CSV
    with open(filename, 'w', newline='') as csvfile:
        fieldnames = ['id', 'username', 'password', 'exp_date', 'max_connections', 'enabled']
        writer = csv.DictWriter(csvfile, fieldnames=fieldnames)

        writer.writeheader()
        for line in lines:
            writer.writerow({
                'id': line['id'],
                'username': line['username'],
                'password': line['password'],
                'exp_date': datetime.fromtimestamp(int(line['exp_date'])).strftime('%Y-%m-%d'),
                'max_connections': line['max_connections'],
                'enabled': 'Yes' if line['enabled'] == '1' else 'No'
            })

    print(f"Exported {len(lines)} lines to {filename}")
📊 Formatos de data de validade
XUI.ONE aceita vários formatos para datas de expiração:

1. Data específica (AAAA-MM-DD)
-d "exp_date=2025-12-31"
2. Timestamp Unix
-d "exp_date=1735689600"
3. Tempo Relativo
# Hours
-d "exp_date=24hours"

# Days
-d "exp_date=7days"

# Months
-d "exp_date=1month"
-d "exp_date=3months"

# Years
-d "exp_date=1year"
Função auxiliar em Python
from datetime import datetime, timedelta

def format_exp_date(days=None, months=None, years=None, date=None):
    """Convert various formats to Unix timestamp"""
    if date:
        # Specific date
        dt = datetime.strptime(date, '%Y-%m-%d')
    else:
        # Relative date
        dt = datetime.now()
        if days:
            dt += timedelta(days=days)
        if months:
            dt += timedelta(days=30 * months)
        if years:
            dt += timedelta(days=365 * years)

    return int(dt.timestamp())

# Usage
exp_date = format_exp_date(months=3)  # 3 months from now
⚠️Notas importantes
Requisitos de nome de usuário/senha
Nome de usuário: 3 a 50 caracteres, alfanumérico
Senha: Recomenda-se no mínimo 6 caracteres.
Não utilize caracteres especiais no nome de usuário (use apenas letras, números e sublinhado).
Seleção de buquês
Ao atribuir buquês, use a notação de matriz:

# cURL
-d "bouquets_selected[]=1" \
-d "bouquets_selected[]=2"

# Python
bouquets_selected=[1, 2, 3]

# PHP
["bouquets_selected" => [1, 2, 3]]
Conexões máximas
Mínimo: 1
Máximo: Ilimitado (mas recomenda-se o uso de limites razoáveis)
Valores comuns: 1-5 para usuários domésticos, 10-50 para revendedores.
Linhas de teste vs. linhas regulares
As linhas de teste podem ser convertidas automaticamente em linhas regulares:

# Create trial
manager.create_line(
    username="trial001",
    password="temp123",
    is_trial=1,
    exp_date="7days"
)

# Convert to regular (when customer pays)
manager.edit_line(
    line_id=123,
    is_trial=0,
    exp_date="1year"
)
💡 Melhores Práticas
1. Sempre verifique antes de excluir
def safe_delete_line(manager, line_id):
    """Delete line with confirmation"""
    try:
        # Get line details first
        line = manager.get_line(line_id)

        # Confirm it's the right line
        print(f"About to delete: {line['username']}")
        confirm = input("Are you sure? (yes/no): ")

        if confirm.lower() == 'yes':
            manager.delete_line(line_id)
            print("Line deleted successfully")
        else:
            print("Deletion cancelled")

    except Exception as e:
        print(f"Error: {e}")
2. Validar os dados de entrada
def validate_line_data(username, password, max_connections):
    """Validate line data before creation"""
    errors = []

    if len(username) < 3 or len(username) > 50:
        errors.append("Username must be 3-50 characters")

    if not username.replace('_', '').isalnum():
        errors.append("Username must be alphanumeric")

    if len(password) < 6:
        errors.append("Password must be at least 6 characters")

    if max_connections < 1:
        errors.append("Max connections must be at least 1")

    return errors

# Usage
errors = validate_line_data("user123", "pass", 1)
if errors:
    print("Validation errors:")
    for error in errors:
        print(f"  - {error}")
else:
    # Create line
    manager.create_line("user123", "secure123", max_connections=1)
3. Implementar Limitação de Taxa
import time
from functools import wraps

def rate_limit(calls_per_second=2):
    """Limit API calls to prevent overload"""
    min_interval = 1.0 / calls_per_second
    last_called = [0.0]

    def decorator(func):
        @wraps(func)
        def wrapper(*args, **kwargs):
            elapsed = time.time() - last_called[0]
            left_to_wait = min_interval - elapsed

            if left_to_wait > 0:
                time.sleep(left_to_wait)

            result = func(*args, **kwargs)
            last_called[0] = time.time()
            return result
        return wrapper
    return decorator

# Apply to methods
XUILineManager.create_line = rate_limit()(XUILineManager.create_line)
4. Tratamento de erros
def create_line_with_retry(manager, username, password, retries=3):
    """Create line with automatic retry on failure"""
    for attempt in range(retries):
        try:
            return manager.create_line(username, password)
        except Exception as e:
            if attempt < retries - 1:
                print(f"Attempt {attempt + 1} failed: {e}. Retrying...")
                time.sleep(2 ** attempt)  # Exponential backoff
            else:
                print(f"All {retries} attempts failed")
                raise



                API de Logs e Eventos - Monitoramento e Auditoria
Documentação completa para endpoints de registro e monitoramento na API de administração do XUI.ONE.

📋 Visão geral
A API de Logs e Eventos oferece recursos abrangentes de monitoramento e auditoria para o seu painel de IPTV. Acompanhe a atividade do usuário, monitore conexões, solucione problemas e mantenha a segurança por meio de registros detalhados.

Pontos de extremidade disponíveis
Ponto final	Descrição	Filtros disponíveis
activity_logs	Ações de administrador/usuário	limite, deslocamento
live_connections	Conexões de visualizadores ativos	-
credit_logs	Transações de crédito	ID do usuário
client_logs	Tentativas de conexão do cliente	id_da_linha
user_logs	Atividade do painel do usuário	ID do usuário
stream_errors	Falhas de fluxo	id_do_fluxo
watch_output	Monitoramento de fluxo em tempo real	stream_id (obrigatório)
system_logs	Eventos do sistema	limite
login_logs	Tentativas de login	sucesso (0/1)
restream_logs	Atividade de retransmissão	-
mag_events	eventos do dispositivo MAG	mag_id
📊 Registros de atividades
Ação:activity_logs
Acompanhe todas as ações administrativas e alterações em seu painel.

Parâmetros:

limit(opcional) - Número de registros (padrão: 100)
offset(opcional) - Deslocamento de paginação (padrão: 0)
Solicitar:

curl "http://your-server.com/cSbuFLhp/?api_key=8D3135D30437F86EAE2FA4A2A8345000&action=activity_logs&limit=50"
Exemplo de resposta:

{
  "status": "STATUS_SUCCESS",
  "data": [
    {
      "id": "1",
      "user_id": "1",
      "username": "admin",
      "action": "edit_line",
      "details": "Modified line ID 123",
      "ip_address": "192.168.1.100",
      "timestamp": "1734048000"
    }
  ]
}
Casos de uso:

Registro de auditoria para fins de conformidade
Acompanhe as alterações de configuração
Monitorar ações do administrador
Investigação de segurança
Solução de problemas
🔴 Conexões ao vivo
Ação:live_connections
Veja as conexões ativas em tempo real aos seus fluxos.

Solicitar:

curl "http://your-server.com/cSbuFLhp/?api_key=8D3135D30437F86EAE2FA4A2A8345000&action=live_connections"
Exemplo de resposta:

{
  "status": "STATUS_SUCCESS",
  "data": [
    {
      "line_id": "123",
      "username": "testuser",
      "stream_id": "456",
      "stream_name": "CNN HD",
      "ip_address": "203.0.113.45",
      "user_agent": "VLC/3.0.11",
      "started_at": "1734048000",
      "duration": "3600"
    }
  ]
}
Casos de uso:

Monitorar visualizações simultâneas
Detectar compartilhamento de conexão
Acompanhe as transmissões mais populares
Veja a distribuição geográfica.
Calcular o uso da largura de banda
💰 Registros de Crédito
Ação:credit_logs
Acompanhe as transações de crédito para revendedores.

Parâmetros:

user_id(opcional) - Filtrar por usuário específico
Solicitar:

curl "http://your-server.com/cSbuFLhp/?api_key=8D3135D30437F86EAE2FA4A2A8345000&action=credit_logs&user_id=5"
Exemplo de resposta:

{
  "status": "STATUS_SUCCESS",
  "data": [
    {
      "id": "1",
      "user_id": "5",
      "username": "reseller1",
      "action": "add_credits",
      "amount": "100.00",
      "balance_after": "500.00",
      "description": "Credit purchase",
      "timestamp": "1734048000"
    }
  ]
}
Casos de uso:

Auditoria financeira
Acompanhe os gastos dos revendedores
Monitorar o fluxo de crédito
Gerar faturas
Relatórios de receita
📱 Registros do Cliente
Ação:client_logs
Monitore as tentativas de conexão do cliente e o histórico.

Parâmetros:

line_id(opcional) - Filtrar por linha específica
Solicitar:

curl "http://your-server.com/cSbuFLhp/?api_key=8D3135D30437F86EAE2FA4A2A8345000&action=client_logs&line_id=123"
Exemplo de resposta:

{
  "status": "STATUS_SUCCESS",
  "data": [
    {
      "line_id": "123",
      "username": "testuser",
      "ip_address": "203.0.113.45",
      "user_agent": "Kodi/19.4",
      "connection_type": "m3u8",
      "timestamp": "1734048000"
    }
  ]
}
Casos de uso:

Monitorar o uso do dispositivo
Detectar padrões suspeitos
Tipos de conexão do monitor
Visualizar aplicativos do cliente
Solucionar problemas de conexão
👤 Registros do usuário
Ação:user_logs
Monitore as ações do usuário dentro do painel.

Parâmetros:

user_id(opcional) - Filtrar por usuário específico
Solicitar:

curl "http://your-server.com/cSbuFLhp/?api_key=8D3135D30437F86EAE2FA4A2A8345000&action=user_logs"
Casos de uso:

Acompanhe a atividade dos revendedores
Utilização do painel do monitor
Auditar alterações do usuário
Monitoramento de segurança
⚠️Erros de fluxo
Ação:stream_errors
Visualize os registros de falhas de fluxo e informações de erro.

Parâmetros:

stream_id(opcional) - Filtrar por fluxo específico
Solicitar:

curl "http://your-server.com/cSbuFLhp/?api_key=8D3135D30437F86EAE2FA4A2A8345000&action=stream_errors&stream_id=456"
Exemplo de resposta:

{
  "status": "STATUS_SUCCESS",
  "data": [
    {
      "stream_id": "456",
      "stream_name": "CNN HD",
      "error_type": "source_offline",
      "error_message": "Connection refused to source",
      "timestamp": "1734048000",
      "duration": "120"
    }
  ]
}
Casos de uso:

Solucionar problemas de transmissão
Monitorar a confiabilidade do fluxo
Identificar fontes problemáticas
Monitore o tempo de inatividade.
Gerar relatórios de tempo de atividade
👁️ Assistir à saída de transmissão
Ação:watch_output
Monitore os detalhes e a qualidade da codificação da transmissão ao vivo.

Parâmetros:

stream_id(Obrigatório) - ID do fluxo a ser monitorado
Solicitar:

curl "http://your-server.com/cSbuFLhp/?api_key=8D3135D30437F86EAE2FA4A2A8345000&action=watch_output&stream_id=456"
Exemplo de resposta:

{
  "status": "STATUS_SUCCESS",
  "data": {
    "stream_id": "456",
    "bitrate": "4500 kbps",
    "resolution": "1920x1080",
    "fps": "30",
    "codec": "H.264",
    "audio_codec": "AAC",
    "buffer_health": "good"
  }
}
Casos de uso:

Monitorar a qualidade da codificação
Verifique as configurações de transmissão
Diagnosticar problemas de reprodução
Verifique a estabilidade da taxa de bits.
Solução de problemas em tempo real
🖥️ Registros do sistema
Ação:system_logs
Visualize eventos e erros em nível de sistema.

Parâmetros:

limit(opcional) - Número de registros (padrão: 100)
Solicitar:

curl "http://your-server.com/cSbuFLhp/?api_key=8D3135D30437F86EAE2FA4A2A8345000&action=system_logs&limit=50"
Casos de uso:

Monitorar a integridade do sistema
Rastrear erros e avisos
Monitoramento de desempenho
Planejamento de capacidade
Solução de problemas
🔐 Registros de login
Ação:login_logs
Monitore as tentativas de login no painel.

Parâmetros:

success(opcional) - Filtrar por status (1 = bem-sucedido, 0 = falhou)
Solicitar:

# Failed logins only
curl "http://your-server.com/cSbuFLhp/?api_key=8D3135D30437F86EAE2FA4A2A8345000&action=login_logs&success=0"
Exemplo de resposta:

{
  "status": "STATUS_SUCCESS",
  "data": [
    {
      "username": "admin",
      "ip_address": "203.0.113.45",
      "success": "0",
      "failure_reason": "Invalid password",
      "timestamp": "1734048000"
    }
  ]
}
Casos de uso:

Detectar ataques de força bruta
Monitorar acessos não autorizados
Acompanhe os logins bem-sucedidos
Auditoria de segurança
decisões de bloqueio de IP
🔄 Registros de retransmissão
Ação:restream_logs
Monitore a atividade de retransmissão do revendedor.

Solicitar:

curl "http://your-server.com/cSbuFLhp/?api_key=8D3135D30437F86EAE2FA4A2A8345000&action=restream_logs"
Casos de uso:

Monitorar o uso de retransmissão
Monitorar a atividade dos revendedores
Análise de largura de banda
Monitoramento de desempenho
📺 Eventos MAG
Ação:mag_events
Visualizar os registros de eventos do dispositivo MAG.

Parâmetros:

mag_id(opcional) - Filtrar por dispositivo MAG específico
Solicitar:

curl "http://your-server.com/cSbuFLhp/?api_key=8D3135D30437F86EAE2FA4A2A8345000&action=mag_events&mag_id=202"
Exemplo de resposta:

{
  "status": "STATUS_SUCCESS",
  "data": [
    {
      "mag_id": "202",
      "mac": "00:1A:79:XX:XX:XX",
      "event_type": "channel_change",
      "details": "Changed to channel CNN HD",
      "ip_address": "203.0.113.45",
      "timestamp": "1734048000"
    }
  ]
}
Casos de uso:

Monitore o uso do MAG
Monitorar eventos do dispositivo
Solucionar problemas
Padrões de visualização
💻 Exemplos de código
Python - Painel de Monitoramento Abrangente
import requests
from datetime import datetime
from collections import defaultdict

class XUIMonitor:
    def __init__(self, base_url, api_key):
        self.base_url = base_url
        self.api_key = api_key

    def _make_request(self, action, params=None):
        """Make API request"""
        request_params = {
            "api_key": self.api_key,
            "action": action
        }
        if params:
            request_params.update(params)

        response = requests.get(self.base_url, params=request_params)
        result = response.json()

        if result["status"] != "STATUS_SUCCESS":
            raise Exception(f"API Error: {result.get('error', 'Unknown')}")

        return result["data"]

    def get_live_connections(self):
        """Get current live connections"""
        return self._make_request("live_connections")

    def get_activity_logs(self, limit=100):
        """Get recent activity logs"""
        return self._make_request("activity_logs", {"limit": limit})

    def get_stream_errors(self, stream_id=None):
        """Get stream error logs"""
        params = {}
        if stream_id:
            params["stream_id"] = stream_id
        return self._make_request("stream_errors", params)

    def get_failed_logins(self):
        """Get failed login attempts"""
        return self._make_request("login_logs", {"success": 0})

    def generate_dashboard(self):
        """Generate monitoring dashboard"""
        print("=" * 60)
        print("XUI.ONE Monitoring Dashboard")
        print("=" * 60)
        print(f"Generated: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
        print()

        # Live Connections
        connections = self.get_live_connections()
        print(f"🔴 Live Connections: {len(connections)}")

        # Group by stream
        streams = defaultdict(int)
        for conn in connections:
            streams[conn.get('stream_name', 'Unknown')] += 1

        print("\nTop Streams:")
        for stream, count in sorted(streams.items(), key=lambda x: x[1], reverse=True)[:5]:
            print(f"  • {stream}: {count} viewers")

        # Failed Logins
        failed_logins = self.get_failed_logins()
        if failed_logins:
            print(f"\n⚠️  Failed Login Attempts: {len(failed_logins)}")

            # Group by IP
            ips = defaultdict(int)
            for login in failed_logins:
                ips[login['ip_address']] += 1

            suspicious = [(ip, count) for ip, count in ips.items() if count > 5]
            if suspicious:
                print("\n🚨 Suspicious IPs (5+ failed attempts):")
                for ip, count in sorted(suspicious, key=lambda x: x[1], reverse=True):
                    print(f"  • {ip}: {count} attempts")

        # Stream Errors
        errors = self.get_stream_errors()
        if errors:
            print(f"\n⚠️  Stream Errors: {len(errors)}")

            # Recent errors
            recent = sorted(errors, key=lambda x: x['timestamp'], reverse=True)[:5]
            print("\nRecent Errors:")
            for error in recent:
                time = datetime.fromtimestamp(int(error['timestamp']))
                print(f"  • {error['stream_name']}: {error['error_message']} ({time.strftime('%H:%M:%S')})")

        print("\n" + "=" * 60)

# Usage
monitor = XUIMonitor(
    base_url="http://your-server.com/cSbuFLhp/",
    api_key="8D3135D30437F86EAE2FA4A2A8345000"
)

# Generate dashboard
monitor.generate_dashboard()
Python - Detecção de Compartilhamento de Conexão
from collections import defaultdict
from datetime import datetime

def detect_connection_sharing(monitor):
    """Detect potential connection sharing by analyzing concurrent connections"""
    connections = monitor.get_live_connections()

    # Group by line_id
    lines = defaultdict(list)
    for conn in connections:
        lines[conn['line_id']].append(conn)

    # Find violations
    violations = []
    for line_id, conns in lines.items():
        if len(conns) > 1:
            # Check if from different IPs
            ips = set(c['ip_address'] for c in conns)
            if len(ips) > 1:
                violations.append({
                    'line_id': line_id,
                    'username': conns[0]['username'],
                    'connection_count': len(conns),
                    'unique_ips': len(ips),
                    'ips': list(ips)
                })

    if violations:
        print(f"⚠️  Found {len(violations)} potential sharing violations:")
        for v in violations:
            print(f"\n  Line: {v['username']} (ID: {v['line_id']})")
            print(f"  Connections: {v['connection_count']}")
            print(f"  Unique IPs: {v['unique_ips']}")
            print(f"  IPs: {', '.join(v['ips'])}")
    else:
        print("✓ No connection sharing detected")

    return violations
Python - Monitoramento da Saúde do Stream
from datetime import datetime, timedelta

def monitor_stream_health(monitor):
    """Monitor stream reliability and generate health report"""
    errors = monitor.get_stream_errors()

    # Group by stream
    stream_errors = defaultdict(list)
    for error in errors:
        stream_errors[error['stream_id']].append(error)

    print("Stream Health Report")
    print("=" * 50)

    for stream_id, error_list in stream_errors.items():
        if not error_list:
            continue

        stream_name = error_list[0].get('stream_name', f'Stream {stream_id}')
        error_count = len(error_list)

        # Calculate total downtime
        total_downtime = sum(int(e.get('duration', 0)) for e in error_list)

        # Recent errors
        recent = [e for e in error_list
                 if int(e['timestamp']) > (datetime.now().timestamp() - 86400)]

        print(f"\n📺 {stream_name}")
        print(f"  Total Errors: {error_count}")
        print(f"  Errors (24h): {len(recent)}")
        print(f"  Total Downtime: {total_downtime // 60} minutes")

        if error_count > 10:
            print(f"  🚨 HIGH ERROR COUNT - Investigate source!")
Python - Sistema de Alerta de Segurança
def security_monitor(monitor):
    """Monitor for security issues"""
    alerts = []

    # Check failed logins
    failed = monitor.get_failed_logins()

    # Group by IP
    ip_attempts = defaultdict(int)
    for login in failed:
        ip_attempts[login['ip_address']] += 1

    # Alert on brute force attempts
    for ip, count in ip_attempts.items():
        if count >= 5:
            alerts.append({
                'type': 'brute_force',
                'severity': 'high' if count >= 10 else 'medium',
                'ip': ip,
                'attempts': count,
                'message': f"Brute force detected from {ip} ({count} attempts)"
            })

    # Check activity logs for suspicious actions
    activity = monitor.get_activity_logs(limit=100)

    # Alert on mass deletions
    deletes = [a for a in activity if 'delete' in a.get('action', '').lower()]
    if len(deletes) > 10:
        alerts.append({
            'type': 'mass_deletion',
            'severity': 'high',
            'count': len(deletes),
            'message': f"Mass deletion detected: {len(deletes)} items deleted recently"
        })

    # Display alerts
    if alerts:
        print("🚨 Security Alerts")
        print("=" * 50)
        for alert in alerts:
            severity_icon = "🔴" if alert['severity'] == 'high' else "🟡"
            print(f"\n{severity_icon} {alert['type'].upper()}")
            print(f"  {alert['message']}")
    else:
        print("✅ No security issues detected")

    return alerts
PHP - Monitor de Conexão em Tempo Real
<?php
class XUIMonitor {
    private $baseUrl;
    private $apiKey;

    public function __construct($baseUrl, $apiKey) {
        $this->baseUrl = $baseUrl;
        $this->apiKey = $apiKey;
    }

    private function makeRequest($action, $params = []) {
        $url = $this->baseUrl . "?api_key=" . $this->apiKey . "&action=" . $action;

        if (!empty($params)) {
            $url .= "&" . http_build_query($params);
        }

        $response = file_get_contents($url);
        $result = json_decode($response, true);

        if ($result['status'] !== 'STATUS_SUCCESS') {
            throw new Exception("API Error: " . ($result['error'] ?? 'Unknown'));
        }

        return $result['data'];
    }

    public function getLiveConnections() {
        return $this->makeRequest('live_connections');
    }

    public function getConnectionStats() {
        $connections = $this->getLiveConnections();

        $stats = [
            'total' => count($connections),
            'by_stream' => [],
            'by_user' => [],
            'unique_ips' => []
        ];

        foreach ($connections as $conn) {
            // Count by stream
            $stream = $conn['stream_name'] ?? 'Unknown';
            $stats['by_stream'][$stream] = ($stats['by_stream'][$stream] ?? 0) + 1;

            // Count by user
            $user = $conn['username'] ?? 'Unknown';
            $stats['by_user'][$user] = ($stats['by_user'][$user] ?? 0) + 1;

            // Track IPs
            $stats['unique_ips'][] = $conn['ip_address'];
        }

        $stats['unique_ips'] = array_unique($stats['unique_ips']);

        return $stats;
    }
}

// Usage
$monitor = new XUIMonitor(
    "http://your-server.com/cSbuFLhp/",
    "8D3135D30437F86EAE2FA4A2A8345000"
);

$stats = $monitor->getConnectionStats();

echo "Live Connection Statistics\n";
echo "==========================\n";
echo "Total Connections: " . $stats['total'] . "\n";
echo "Unique IPs: " . count($stats['unique_ips']) . "\n\n";

echo "Top Streams:\n";
arsort($stats['by_stream']);
foreach (array_slice($stats['by_stream'], 0, 5) as $stream => $count) {
    echo "  • $stream: $count viewers\n";
}
?>
JavaScript - Painel de controle ao vivo (Node.js)
const fetch = require('node-fetch');

class XUIMonitor {
    constructor(baseUrl, apiKey) {
        this.baseUrl = baseUrl;
        this.apiKey = apiKey;
    }

    async makeRequest(action, params = {}) {
        const url = new URL(this.baseUrl);
        url.searchParams.append('api_key', this.apiKey);
        url.searchParams.append('action', action);

        for (const [key, value] of Object.entries(params)) {
            url.searchParams.append(key, value);
        }

        const response = await fetch(url);
        const result = await response.json();

        if (result.status !== 'STATUS_SUCCESS') {
            throw new Error(`API Error: ${result.error || 'Unknown'}`);
        }

        return result.data;
    }

    async getLiveConnections() {
        return this.makeRequest('live_connections');
    }

    async getStreamErrors() {
        return this.makeRequest('stream_errors');
    }

    async displayDashboard() {
        console.clear();
        console.log('═'.repeat(60));
        console.log('XUI.ONE Live Dashboard');
        console.log('═'.repeat(60));
        console.log(`Updated: ${new Date().toLocaleString()}\n`);

        // Live connections
        const connections = await this.getLiveConnections();
        console.log(`🔴 Live Connections: ${connections.length}`);

        // Stream breakdown
        const streams = connections.reduce((acc, conn) => {
            const name = conn.stream_name || 'Unknown';
            acc[name] = (acc[name] || 0) + 1;
            return acc;
        }, {});

        const topStreams = Object.entries(streams)
            .sort((a, b) => b[1] - a[1])
            .slice(0, 5);

        if (topStreams.length > 0) {
            console.log('\nTop Streams:');
            topStreams.forEach(([stream, count]) => {
                console.log(`  • ${stream}: ${count} viewers`);
            });
        }

        // Stream errors
        const errors = await this.getStreamErrors();
        const recentErrors = errors.filter(e =>
            parseInt(e.timestamp) > (Date.now() / 1000 - 3600)
        );

        if (recentErrors.length > 0) {
            console.log(`\n⚠️  Recent Errors (1h): ${recentErrors.length}`);
        }

        console.log('\n' + '═'.repeat(60));
    }

    startAutoRefresh(interval = 10000) {
        this.displayDashboard();
        setInterval(() => this.displayDashboard(), interval);
    }
}

// Usage
const monitor = new XUIMonitor(
    'http://your-server.com/cSbuFLhp/',
    '8D3135D30437F86EAE2FA4A2A8345000'
);

// Auto-refresh every 10 seconds
monitor.startAutoRefresh(10000);
🎯 Casos de uso comuns
1. Relatório Diário de Segurança
def generate_security_report(monitor):
    """Generate daily security summary"""
    from datetime import datetime, timedelta

    yesterday = datetime.now() - timedelta(days=1)

    print("Daily Security Report")
    print("=" * 50)
    print(f"Date: {datetime.now().strftime('%Y-%m-%d')}")
    print()

    # Failed logins
    failed = monitor.get_failed_logins()
    print(f"Failed Login Attempts: {len(failed)}")

    # Activity summary
    activity = monitor.get_activity_logs(limit=1000)
    print(f"Admin Actions: {len(activity)}")

    # Connection sharing
    violations = detect_connection_sharing(monitor)
    print(f"Connection Violations: {len(violations)}")
2. Relatório de desempenho do fluxo
def stream_performance_report(monitor):
    """Generate stream uptime and performance report"""
    errors = monitor.get_stream_errors()

    # Calculate uptime percentage
    total_time = 24 * 3600  # 24 hours
    downtime = sum(int(e.get('duration', 0)) for e in errors)
    uptime_pct = ((total_time - downtime) / total_time) * 100

    print(f"Overall Uptime: {uptime_pct:.2f}%")
    print(f"Total Downtime: {downtime // 60} minutes")
3. Alertas em tempo real
import time

def monitor_realtime_alerts(monitor):
    """Monitor for issues and send alerts"""
    last_check = {}

    while True:
        # Check for new stream errors
        errors = monitor.get_stream_errors()
        for error in errors:
            error_id = f"{error['stream_id']}_{error['timestamp']}"
            if error_id not in last_check:
                print(f"🚨 ALERT: Stream {error['stream_name']} - {error['error_message']}")
                last_check[error_id] = True
                # Send email/webhook here

        time.sleep(60)  # Check every minute

        API do usuário - Gerenciamento de contas
Documentação completa para gerenciamento de contas de usuário na API de administração do XUI.ONE.

📋 Visão geral
A API de Usuário oferece controle total sobre contas de administrador, revendedor e usuário. Gerencie permissões, créditos e status da conta programaticamente.

Operações disponíveis
Ação	Método	Descrição
create_user	PUBLICAR	Criar nova conta de usuário
edit_user	PUBLICAR	Modificar configurações do usuário
delete_user	PUBLICAR	Remover conta de usuário
enable_user	PUBLICAR	Ativar conta de usuário
disable_user	PUBLICAR	Suspender conta de usuário
add_credits	PUBLICAR	Adicionar créditos ao revendedor
👤 Criar usuário
Ação:create_user
Crie uma nova conta de administrador, revendedor ou usuário.

Parâmetros obrigatórios:

username- Nome de usuário da conta
password- Senha da conta
Parâmetros opcionais:

email- Endereço de email
member_group_id- Função do usuário (1=Administrador, 2=Revendedor, 3=Usuário)
Solicitar:

curl -X POST "http://your-server.com/cSbuFLhp/?api_key=8D3135D30437F86EAE2FA4A2A8345000&action=create_user" \
  -d "username=newreseller" \
  -d "password=secure123" \
  -d "email=reseller@example.com" \
  -d "member_group_id=2"
Resposta:

{
  "status": "STATUS_SUCCESS",
  "data": {
    "user_id": "10",
    "username": "newreseller"
  }
}
✏️ Editar usuário
Ação:edit_user
Modifique as configurações da conta de usuário existente.

Parâmetros obrigatórios:

id- ID do usuário para editar
Parâmetros opcionais:

username- Novo nome de usuário
password- Nova Senha
email- Novo e-mail
member_group_id- Alterar função
Solicitar:

curl -X POST "http://your-server.com/cSbuFLhp/?api_key=8D3135D30437F86EAE2FA4A2A8345000&action=edit_user" \
  -d "id=10" \
  -d "password=newpassword456" \
  -d "email=newemail@example.com"
🗑️ Excluir usuário
Ação:delete_user
Remover permanentemente a conta de usuário.

⚠️ Aviso: Esta ação não pode ser desfeita!

Parâmetros obrigatórios:

id- ID do usuário a ser excluído
Solicitar:

curl -X POST "http://your-server.com/cSbuFLhp/?api_key=8D3135D30437F86EAE2FA4A2A8345000&action=delete_user" \
  -d "id=10"
✅ Habilitar usuário
Ação:enable_user
Ativar uma conta de usuário desativada.

Parâmetros obrigatórios:

id- ID do usuário para habilitar
Solicitar:

curl -X POST "http://your-server.com/cSbuFLhp/?api_key=8D3135D30437F86EAE2FA4A2A8345000&action=enable_user" \
  -d "id=10"
⛔ Desativar usuário
Ação:disable_user
Suspender temporariamente a conta do usuário sem excluí-la.

Parâmetros obrigatórios:

id- ID do usuário para desativar
Solicitar:

curl -X POST "http://your-server.com/cSbuFLhp/?api_key=8D3135D30437F86EAE2FA4A2A8345000&action=disable_user" \
  -d "id=10"
💰 Adicionar Créditos
Ação:add_credits
Adicione créditos ao saldo da conta do revendedor.

Parâmetros:

user_id- ID de usuário revendedor
amount- Valor do crédito a adicionar
Solicitar:

curl -X POST "http://your-server.com/cSbuFLhp/?api_key=8D3135D30437F86EAE2FA4A2A8345000&action=add_credits" \
  -d "user_id=10" \
  -d "amount=100.00"
Resposta:

{
  "status": "STATUS_SUCCESS",
  "data": {
    "user_id": "10",
    "credits_added": "100.00",
    "new_balance": "500.00"
  }
}
💻 Exemplos de código
Python
import requests

class XUIUserManager:
    def __init__(self, base_url, api_key):
        self.base_url = base_url
        self.api_key = api_key

    def _request(self, action, data):
        response = requests.post(
            self.base_url,
            params={'api_key': self.api_key, 'action': action},
            data=data
        )
        return response.json()

    def create_user(self, username, password, email=None, role=2):
        """Create new user account"""
        data = {
            'username': username,
            'password': password,
            'member_group_id': role
        }
        if email:
            data['email'] = email

        return self._request('create_user', data)

    def edit_user(self, user_id, **updates):
        """Edit user account"""
        data = {'id': user_id, **updates}
        return self._request('edit_user', data)

    def delete_user(self, user_id):
        """Delete user account"""
        return self._request('delete_user', {'id': user_id})

    def enable_user(self, user_id):
        """Enable user account"""
        return self._request('enable_user', {'id': user_id})

    def disable_user(self, user_id):
        """Disable user account"""
        return self._request('disable_user', {'id': user_id})

    def add_credits(self, user_id, amount):
        """Add credits to reseller"""
        return self._request('add_credits', {
            'user_id': user_id,
            'amount': amount
        })

# Usage
manager = XUIUserManager(
    'http://your-server.com/cSbuFLhp/',
    '8D3135D30437F86EAE2FA4A2A8345000'
)

# Create reseller
result = manager.create_user(
    username='newreseller',
    password='secure123',
    email='reseller@example.com',
    role=2  # Reseller
)
print(f"Created user ID: {result['data']['user_id']}")

# Add credits
manager.add_credits(user_id=10, amount=100.00)
print("Credits added successfully")
PHP
<?php
class XUIUserManager {
    private $baseUrl;
    private $apiKey;

    public function __construct($baseUrl, $apiKey) {
        $this->baseUrl = $baseUrl;
        $this->apiKey = $apiKey;
    }

    private function request($action, $data) {
        $url = $this->baseUrl . "?api_key=" . $this->apiKey . "&action=" . $action;

        $options = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => http_build_query($data)
            ]
        ];

        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        return json_decode($response, true);
    }

    public function createUser($username, $password, $email = null, $role = 2) {
        $data = [
            'username' => $username,
            'password' => $password,
            'member_group_id' => $role
        ];

        if ($email) {
            $data['email'] = $email;
        }

        return $this->request('create_user', $data);
    }

    public function addCredits($userId, $amount) {
        return $this->request('add_credits', [
            'user_id' => $userId,
            'amount' => $amount
        ]);
    }
}

// Usage
$manager = new XUIUserManager(
    "http://your-server.com/cSbuFLhp/",
    "8D3135D30437F86EAE2FA4A2A8345000"
);

$result = $manager->createUser(
    "newreseller",
    "secure123",
    "reseller@example.com",
    2
);

echo "Created user ID: " . $result['data']['user_id'];
?>
🎯 Casos de uso comuns
1. Criar revendedores em massa
def bulk_create_resellers(manager, count=10, initial_credits=100):
    """Create multiple reseller accounts"""
    resellers = []

    for i in range(count):
        username = f"reseller{i+1:03d}"
        password = generate_secure_password()
        email = f"{username}@example.com"

        try:
            # Create reseller
            result = manager.create_user(
                username=username,
                password=password,
                email=email,
                role=2
            )

            user_id = result['data']['user_id']

            # Add initial credits
            manager.add_credits(user_id, initial_credits)

            resellers.append({
                'id': user_id,
                'username': username,
                'password': password,
                'email': email,
                'credits': initial_credits
            })

            print(f"✓ Created: {username}")

        except Exception as e:
            print(f"✗ Failed to create {username}: {e}")

    return resellers
2. Sistema de Gestão de Crédito
def manage_reseller_credits(manager):
    """Monitor and manage reseller credits"""
    # Get all users (assuming you have get_users)
    users = manager._request('get_users', {})

    for user in users['data']:
        if user['member_group_id'] == '2':  # Resellers only
            credits = float(user.get('credits', 0))

            # Auto-add credits if balance is low
            if credits < 10:
                manager.add_credits(user['id'], 50)
                print(f"Added 50 credits to {user['username']}")
3. Relatório de Auditoria do Usuário
def generate_user_report(manager):
    """Generate user account report"""
    users = manager._request('get_users', {})

    report = {
        'total': len(users['data']),
        'admins': 0,
        'resellers': 0,
        'users': 0,
        'active': 0,
        'disabled': 0
    }

    for user in users['data']:
        role = int(user['member_group_id'])
        if role == 1:
            report['admins'] += 1
        elif role == 2:
            report['resellers'] += 1
        else:
            report['users'] += 1

        if user['status'] == '1':
            report['active'] += 1
        else:
            report['disabled'] += 1

    print("User Account Report")
    print("=" * 50)
    for key, value in report.items():
        print(f"{key.capitalize()}: {value}")
💡 Melhores Práticas
1. Segurança de Senhas
import secrets
import string

def generate_secure_password(length=16):
    """Generate cryptographically secure password"""
    alphabet = string.ascii_letters + string.digits + "!@#$%^&*"
    return ''.join(secrets.choice(alphabet) for _ in range(length))
2. Controle de acesso baseado em funções
# User roles
ROLE_ADMIN = 1
ROLE_RESELLER = 2
ROLE_USER = 3

def create_user_with_role(manager, username, password, role_name):
    """Create user with role name instead of ID"""
    roles = {
        'admin': ROLE_ADMIN,
        'reseller': ROLE_RESELLER,
        'user': ROLE_USER
    }

    role_id = roles.get(role_name.lower(), ROLE_USER)

    return manager.create_user(
        username=username,
        password=password,
        role=role_id
    )
3. Validação de e-mail
import re

def validate_email(email):
    """Validate email format"""
    pattern = r'^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$'
    return re.match(pattern, email) is not None

def create_user_safe(manager, username, password, email):
    """Create user with validation"""
    if not validate_email(email):
        raise ValueError("Invalid email format")

    if len(password) < 8:
        raise ValueError("Password must be at least 8 characters")

    return manager.create_user(username, password, email)
⚠️Notas importantes
Requisitos de nome de usuário
3-50 caracteres
Somente caracteres alfanuméricos
Deve ser único
Requisitos de senha
Mínimo de 6 caracteres (8+ recomendados)
Recomenda-se uma combinação de letras, números e símbolos.
Funções do usuário
1 = Administrador - Acesso total ao painel
2 = Revendedor - Pode criar/gerenciar linhas
3 = Usuário - Acesso limitado
Sistema de Crédito
Os créditos são valores decimais (ex.: 100,00).
Utilizado por revendedores para criar assinaturas.
Pode ser adicionado, mas não deduzido via API.

API de Streams - Gerenciamento de Transmissões ao Vivo
Documentação completa para gerenciamento de transmissões de TV ao vivo na API de administração do XUI.ONE.

📋 Visão geral
Gerencie transmissões de TV ao vivo, incluindo criação, edição, controle de codificação e exclusão.

Operações disponíveis
Ação	Método	Descrição
create_stream	PUBLICAR	Criar nova transmissão ao vivo
edit_stream	PUBLICAR	Editar configurações de transmissão
delete_stream	PUBLICAR	Remover fluxo
start_stream	PUBLICAR	Iniciar codificação de fluxo
stop_stream	PUBLICAR	Interrompa a codificação do fluxo
get_stream	PEGAR	Obtenha detalhes da transmissão
Exemplos rápidos
Criar fluxo
curl -X POST "http://your-server.com/cSbuFLhp/?api_key=API_KEY&action=create_stream" \
  -d "stream_display_name=CNN HD" \
  -d "stream_source=http://source.example.com/stream.m3u8" \
  -d "category_id=1" \
  -d "stream_icon=http://example.com/icon.png"
Iniciar/Parar Transmissão
# Start
curl -X POST "http://your-server.com/cSbuFLhp/?api_key=API_KEY&action=start_stream" \
  -d "id=456"

# Stop
curl -X POST "http://your-server.com/cSbuFLhp/?api_key=API_KEY&action=stop_stream" \
  -d "id=456"
Exemplo em Python
class XUIStreamsManager:
    def create_stream(self, name, source, category_id=1, icon=None):
        data = {
            'stream_display_name': name,
            'stream_source': source,
            'category_id': category_id
        }
        if icon:
            data['stream_icon'] = icon

        response = requests.post(
            self.base_url,
            params={'api_key': self.api_key, 'action': 'create_stream'},
            data=data
        )
        return response.json()

    def start_stream(self, stream_id):
        response = requests.post(
            self.base_url,
            params={'api_key': self.api_key, 'action': 'start_stream'},
            data={'id': stream_id}
        )
        return response.json()
Formatos de origem suportados
HLS (m3u8)
RTMP
HTTP/HTTPS
UDP/MPEG-TS
RTSP

API de canal - Gerenciamento de canais
Pontos de extremidade para configuração e gerenciamento de canais.

📋 Operações disponíveis
Ação	Método	Descrição
create_channel	PUBLICAR	Criar novo canal
edit_channel	PUBLICAR	Editar configurações do canal
delete_channel	PUBLICAR	Remover canal
enable_channel	PUBLICAR	Ativar canal
disable_channel	PUBLICAR	Suspender canal
get_channel	PEGAR	Obtenha detalhes do canal
Exemplo rápido
curl -X POST "http://your-server.com/cSbuFLhp/?api_key=API_KEY&action=create_channel" \
  -d "channel_name=CNN HD" \
  -d "epg_id=cnn-hd" \
  -d "category_id=1"

  API de Filmes - Gerenciamento de Filmes VOD
Pontos de extremidade para gerenciamento de filmes VOD.

📋 Operações disponíveis
Ação	Método	Descrição
create_movie	PUBLICAR	Criar filme VOD
edit_movie	PUBLICAR	Editar configurações do filme
delete_movie	PUBLICAR	Remover filme
enable_movie	PUBLICAR	Ativar filme
disable_movie	PUBLICAR	Filme suspenso
get_movie	PEGAR	Confira os detalhes do filme
Exemplo rápido
curl -X POST "http://your-server.com/cSbuFLhp/?api_key=API_KEY&action=create_movie" \
  -d "name=The Matrix" \
  -d "stream_source=http://cdn.example.com/matrix.mp4" \
  -d "category_id=5" \
  -d "plot=A computer hacker learns..." \
  -d "year=1999" \
  -d "cover=http://example.com/poster.jpg"
Campos de metadados do filme
name- Título do filme
stream_source- URL do arquivo de vídeo
category_id- Atribuição de categoria
plot- Descrição
year- Ano de lançamento
cover- URL da imagem do pôster
genre- Gênero cinematográfico
cast- Elenco
director- Nome do diretor
rating- Classificação IMDB/TMDB

API de Séries - Gerenciamento de Séries de TV
Pontos finais de gerenciamento de séries de TV.

📋 Operações disponíveis
Ação	Método	Descrição
create_series	PUBLICAR	Criar séries de TV
edit_series	PUBLICAR	Editar configurações da série
delete_series	PUBLICAR	Remover série
get_series	PEGAR	Confira os detalhes da série
Exemplo rápido
curl -X POST "http://your-server.com/cSbuFLhp/?api_key=API_KEY&action=create_series" \
  -d "name=Breaking Bad" \
  -d "category_id=3" \
  -d "cover=http://example.com/cover.jpg" \
  -d "plot=A high school chemistry teacher..." \
  -d "year=2008"
Metadados da série
name- Título da série
category_id- Atribuição de categoria
cover- URL da imagem de capa
plot- Descrição
year- Ano em que foi ao ar pela primeira vez
cast- Elenco
director- Diretor/Criador
genre- Gênero da série
rating- Avaliação

API de Episódios - Gerenciamento de Episódios de TV
Pontos finais de gerenciamento de episódios de séries de TV.

📋 Operações disponíveis
Ação	Método	Descrição
create_episode	PUBLICAR	Criar episódio
edit_episode	PUBLICAR	Editar configurações do episódio
delete_episode	PUBLICAR	Remover episódio
enable_episode	PUBLICAR	Ativar episódio
disable_episode	PUBLICAR	Episódio suspenso
get_episode	PEGAR	Veja os detalhes do episódio
Exemplo rápido
curl -X POST "http://your-server.com/cSbuFLhp/?api_key=API_KEY&action=create_episode" \
  -d "series_id=101" \
  -d "season_num=1" \
  -d "episode_num=1" \
  -d "title=Pilot" \
  -d "stream_source=http://cdn.example.com/s01e01.mp4" \
  -d "plot=The beginning..." \
  -d "duration=45"
Campos do episódio
series_id- ID da série principal (obrigatório)
season_num- Número da temporada
episode_num- Número do episódio
title- Título do episódio
stream_source- URL do arquivo de vídeo
plot- Descrição do episódio
duration- Duração em minutos
air_date- Data de exibição original
Exemplo em Python
def add_season(manager, series_id, season_num, episodes):
    """Add complete season to series"""
    results = []

    for ep_num, ep_data in enumerate(episodes, 1):
        result = manager.create_episode(
            series_id=series_id,
            season_num=season_num,
            episode_num=ep_num,
            title=ep_data['title'],
            stream_source=ep_data['source'],
            plot=ep_data.get('plot', ''),
            duration=ep_data.get('duration', 45)
        )
        results.append(result)

    return results

    API do servidor - Gerenciamento de servidores e balanceadores de carga
Pontos de extremidade para gerenciamento da infraestrutura do servidor.

📋 Operações disponíveis
Ação	Método	Descrição
install_server	PUBLICAR	Instalar novo servidor
edit_server	PUBLICAR	Editar configurações do servidor
delete_server	PUBLICAR	Remover servidor
restart_server	PUBLICAR	Reinicie os serviços do servidor.
get_server_info	PEGAR	Obter informações do servidor
get_server_stats	PEGAR	Obtenha as estatísticas do servidor.
install_load_balancer	PUBLICAR	Instalar balanceador de carga
edit_load_balancer	PUBLICAR	Editar configurações do balanceador de carga
Exemplos rápidos
Obter estatísticas do servidor
curl "http://your-server.com/cSbuFLhp/?api_key=API_KEY&action=get_server_stats"
Resposta:

{
  "status": "STATUS_SUCCESS",
  "data": {
    "cpu_usage": "45%",
    "memory_usage": "60%",
    "disk_usage": "75%",
    "network_in": "150 Mbps",
    "network_out": "200 Mbps",
    "active_connections": 1500
  }
}
Reiniciar o servidor
curl -X POST "http://your-server.com/cSbuFLhp/?api_key=API_KEY&action=restart_server" \
  -d "server_id=1"
Exemplo de monitoramento em Python
def monitor_servers(manager):
    """Monitor all servers"""
    stats = manager.get_server_stats()

    alerts = []
    if float(stats['cpu_usage'].rstrip('%')) > 80:
        alerts.append("High CPU usage")
    if float(stats['memory_usage'].rstrip('%')) > 80:
        alerts.append("High memory usage")
    if float(stats['disk_usage'].rstrip('%')) > 85:
        alerts.append("Low disk space")

    if alerts:
        print("⚠️ Alerts:", ", ".join(alerts))
    else:
        print("✓ All servers healthy")

        Configurações e API do Sistema - Configuração do Painel
Configurações do painel e pontos de extremidade de gerenciamento do sistema.

📋 Operações disponíveis
Ação	Método	Descrição
get_settings	PEGAR	Obter configurações do painel
edit_settings	PUBLICAR	Atualizar configurações do painel
reload_nginx	PUBLICAR	Recarregar configuração do nginx
clear_cache	PUBLICAR	Limpar cache do sistema
backup_database	PUBLICAR	Banco de dados de backup
restore_database	PUBLICAR	Restaurar banco de dados
get_categories	PEGAR	Veja todas as categorias
create_category	PUBLICAR	Criar categoria
edit_category	PUBLICAR	Editar categoria
delete_category	PUBLICAR	Excluir categoria
get_bouquets	PEGAR	Adquira todos os buquês/pacotes
create_bouquet	PUBLICAR	Criar buquê
edit_bouquet	PUBLICAR	Editar buquê
delete_bouquet	PUBLICAR	Apagar buquê
Exemplos rápidos
Manutenção do sistema
# Backup database
curl -X POST "http://your-server.com/cSbuFLhp/?api_key=API_KEY&action=backup_database"

# Clear cache
curl -X POST "http://your-server.com/cSbuFLhp/?api_key=API_KEY&action=clear_cache"

# Reload nginx
curl -X POST "http://your-server.com/cSbuFLhp/?api_key=API_KEY&action=reload_nginx"
Gestão de Categorias
# Create category
curl -X POST "http://your-server.com/cSbuFLhp/?api_key=API_KEY&action=create_category" \
  -d "category_name=Sports" \
  -d "category_type=live"

# Get all categories
curl "http://your-server.com/cSbuFLhp/?api_key=API_KEY&action=get_categories"
Gestão de buquês
# Create bouquet
curl -X POST "http://your-server.com/cSbuFLhp/?api_key=API_KEY&action=create_bouquet" \
  -d "package_name=Premium Package" \
  -d "streams=[1,2,3,4,5]" \
  -d "price=29.99"

# Get all bouquets
curl "http://your-server.com/cSbuFLhp/?api_key=API_KEY&action=get_bouquets"
Exemplo de automação em Python
def daily_maintenance(manager):
    """Automated daily maintenance tasks"""
    print("Starting daily maintenance...")

    # Backup database
    print("- Backing up database...")
    manager._request('backup_database', method='POST')

    # Clear cache
    print("- Clearing cache...")
    manager._request('clear_cache', method='POST')

    # Get system status
    print("- Checking system status...")
    settings = manager._request('get_settings', method='GET')

    print("✓ Daily maintenance completed")
    return settings

def organize_content(manager):
    """Organize content into categories"""
    # Create categories
    categories = [
        {'name': 'Sports', 'type': 'live'},
        {'name': 'News', 'type': 'live'},
        {'name': 'Movies', 'type': 'vod'},
        {'name': 'TV Shows', 'type': 'series'}
    ]

    for cat in categories:
        try:
            manager._request('create_category', {
                'category_name': cat['name'],
                'category_type': cat['type']
            }, method='POST')
            print(f"✓ Created category: {cat['name']}")
        except Exception as e:
            print(f"✗ Failed to create {cat['name']}: {e}")
Tipos de categoria
live- Canais de TV ao vivo
vod- Filmes
series- Série de TV
radio- Estações de rádio
Estrutura do buquê/pacote
{
  "package_name": "Premium Package",
  "streams": [1, 2, 3, 4, 5],
  "price": 29.99,
  "duration_days": 30,
  "description": "Full access to all channels"
}
