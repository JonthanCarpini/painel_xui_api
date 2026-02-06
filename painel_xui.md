📑 Documentação Técnica: Fase 1 (Alicerce de Dados)Objetivo: Implementar a camada de conexão com a API e recuperar os dados vitais (Pacotes e Buquês) necessários para popular os formulários de criação de linha.1. Credenciais & ConexãoServidor: http://192.168.100.209Access Code (API): kIzFSjQuAPI Key: DFE74ECCBA19D32DCD758C4D3D5AF0F61.1. Função Helper (api.php)Esta função é o coração do sistema. Todas as chamadas passarão por ela.PHP<?php
// Arquivo: api.php

function xui_api($action, $params = []) {
    // Configurações Fixas
    $base_url = "http://192.168.100.209/kIzFSjQu/";
    $api_key  = "DFE74ECCBA19D32DCD758C4D3D5AF0F6";

    // Injeta a chave e a ação nos parâmetros
    $params['api_key'] = $api_key;
    $params['action']  = $action;

    // Inicializa cURL
    $ch = curl_init();

    // Configura URL e POST
    curl_setopt($ch, CURLOPT_URL, $base_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params)); // Formato x-www-form-urlencoded
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Timeout de 30seg para não travar o painel

    $response = curl_exec($ch);
    $error    = curl_error($ch);
    curl_close($ch);

    // Tratamento de erro de conexão
    if ($error) {
        return ['result' => false, 'message' => 'Erro Conexão: ' . $error];
    }

    // Retorna Array Associativo
    return json_decode($response, true);
}
?>
2. Recuperação de Pacotes (Planos)Recupera as regras de cobrança e duração para exibir no formulário de venda.Endpoint: get_packagesArquivo Sugerido: controller_pacotes.phpLógica de ImplementaçãoO sistema deve iterar sobre a lista e separar o que é "Teste" do que é "Oficial".PHP<?php
require_once 'api.php';

$resp = xui_api('get_packages');
$pacotes = $resp['data'];

// Exemplo de como montar o <select> no HTML
echo '<select name="package_id" class="form-control">';
foreach ($pacotes as $pct) {
    // Filtro: Apenas pacotes ativos e oficiais
    if ($pct['enabled'] == 0) continue;

    $preco = ($pct['official_credits'] > 0) ? $pct['official_credits'] . ' Créditos' : 'Grátis';
    $nome  = $pct['package_name'] . ' - ' . $pct['official_duration'] . ' Dias (' . $preco . ')';

    echo '<option value="' . $pct['id'] . '">' . $nome . '</option>';
}
echo '</select>';
?>
3. Recuperação de Buquês (Canais)Recupera as categorias de conteúdo. Baseado na extração do JSON do servidor, foi identificada a necessidade de filtragem rigorosa para não exibir lixo (testes e backups) ao revendedor.Endpoint: get_bouquetsIDs Ignorados (Blacklist): 34 (teste), 35 (test-import), 10 (Todos Não Usar).Lógica de ImplementaçãoPHP<?php
require_once 'api.php';

$resp = xui_api('get_bouquets');
$buques = $resp['data'];

// IDs que não devem aparecer para o revendedor
$blacklist = [34, 35, 10];

foreach ($buques as $bq) {
    if (in_array($bq['id'], $blacklist)) continue; // Pula os indesejados

    // Checkbox para o formulário
    echo '<div class="check-item">';
    echo '<input type="checkbox" name="bouquet_ids[]" value="' . $bq['id'] . '"> ';
    echo '<label>' . $bq['bouquet_name'] . '</label>';
    echo '</div>';
}
?>
4. Dicionário de Dados (Mapeamento)Este é o mapa oficial dos IDs extraídos do seu servidor em 03/02/2026. Use isso para lógica interna (ex: se quiser forçar que todo teste tenha o ID 1).Grupos Principais (Oficiais)IDNome no PainelConteúdoObservação1FHD + HD + SDCanais de TVPrincipal (Use este como padrão)2VODFilmesPrincipal3SERIESSéries de TVPrincipal5CANAIS ADULTOSConteúdo +18Deve vir desmarcado por padrão6FILMES ADULTOSVOD +18Deve vir desmarcado por padrão7RADIOSEstações de RádioOpcionalGrupos de Nicho / EspecíficosIDNome no PainelUso Recomendado4CANAIS 24HApenas canais que passam a mesma coisa 24h224KApenas para clientes com TV 4K/Internet boa32VODS LITEPacote leve para aparelhos antigos33Inter P2pCanais específicos para tecnologia P2PGrupos Alternativos / Backup (Marcados com ¹)Estes parecem ser rotas alternativas. Só exibir se o principal falhar ou para Admin.23: FHD + HD + SD¹25: 24Horas¹26: CANAIS ADULTOS¹27: Internacional¹28: FILMES¹31: FILMES Adultos¹✅ Checklist de Encerramento da Fase 1[x] Conexão cURL testada e validada com a API Key correta.[x] Endpoint get_packages mapeado.[x] Endpoint get_bouquets mapeado e JSON extraído.[x] IDs de "lixo/teste" identificados para ocultação.Próximo Passo: Implementar Fase 2 (Login).Criar tela HTML de login.Conectar com get_users para validar usuário e senha.Criar sessão PHP.


📂 Estrutura de Arquivos da Fase 2
Crie (ou atualize) estes arquivos na pasta raiz do seu Painel Office:

api.php (O motor de conexão - já vimos, mas vou colocar a versão final aqui).

session.php (O segurança que protege as páginas internas).

login.php (A tela bonita de entrada).

auth.php (O script invisível que valida a senha).

1. O Motor: api.php
Este arquivo centraliza a comunicação. Se você mudar o servidor de IP, muda só aqui.

PHP
<?php
// api.php
function xui_api($action, $params = []) {
    // ⚙️ CONFIGURAÇÕES DO SERVIDOR
    $url_base = "http://192.168.100.209/kIzFSjQu/";
    $api_key  = "DFE74ECCBA19D32DCD758C4D3D5AF0F6";

    $params['api_key'] = $api_key;
    $params['action']  = $action;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url_base . "?" . http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15); // Timeout rápido

    $response = curl_exec($ch);
    $error    = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return ['status' => false, 'data' => "Erro de Conexão: $error"];
    }

    return json_decode($response, true);
}
?>
2. O Segurança: session.php
Inclua este arquivo no topo de todas as páginas internas (dashboard, criar linha, etc). Se o cara não estiver logado, ele é chutado para fora.

PHP
<?php
// session.php
session_start();

// Se não existir a sessão 'office_user_id', manda pro login
if (!isset($_SESSION['office_user_id'])) {
    header("Location: login.php");
    exit;
}

// Opcional: Atalhos para usar nas páginas
$meu_id   = $_SESSION['office_user_id'];
$meu_user = $_SESSION['office_username'];
$meu_grupo= $_SESSION['office_group']; // 1=Admin, 2=Revenda
?>
3. A Tela: login.php
Um HTML limpo e profissional.

PHP
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel Office | Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card-login { width: 100%; max-width: 400px; padding: 20px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .btn-office { background-color: #2c3e50; color: white; }
        .btn-office:hover { background-color: #1a252f; color: white; }
    </style>
</head>
<body>

<div class="card card-login bg-white">
    <div class="text-center mb-4">
        <h3>🔐 Painel Office</h3>
        <p class="text-muted">Acesso Restrito</p>
    </div>

    <?php if(isset($_GET['erro'])): ?>
        <div class="alert alert-danger text-center">Usuário ou senha incorretos!</div>
    <?php endif; ?>

    <form action="auth.php" method="POST">
        <div class="mb-3">
            <label class="form-label">Usuário</label>
            <input type="text" name="username" class="form-control" required autofocus>
        </div>
        <div class="mb-3">
            <label class="form-label">Senha</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-office w-100 py-2">Entrar no Sistema</button>
    </form>
</div>

</body>
</html>
4. O Cérebro: auth.php (Validação Real)
Aqui está a mágica. Como não temos um endpoint check_login, nós baixamos os usuários e conferimos um por um.

PHP
<?php
// auth.php
session_start();
require_once 'api.php';

// 1. Recebe os dados do formulário
$user_digitado = trim($_POST['username']);
$pass_digitada = trim($_POST['password']);

if (empty($user_digitado) || empty($pass_digitada)) {
    header("Location: login.php?erro=vazio");
    exit;
}

// 2. Busca TODOS os usuários no XUI
$response = xui_api('get_users');

if (!$response || !isset($response['data'])) {
    die("Erro Crítico: Não foi possível conectar ao servidor XUI para validar login.");
}

$encontrou = false;

// 3. Varre a lista procurando o usuário
foreach ($response['data'] as $u) {
    // Verifica se o usuário bate
    if ($u['username'] == $user_digitado) {

        // Verifica se a senha bate
        // (Nota: Alguns XUI retornam senha em texto plano. Se for hash, precisaremos ajustar)
        if ($u['password'] == $pass_digitada) {

            // Verifica se está ATIVO (Status 1)
            if ($u['status'] != 1) {
                header("Location: login.php?erro=bloqueado");
                exit;
            }

            // --- SUCESSO! LOGIN APROVADO ---
            $_SESSION['office_user_id']  = $u['id'];
            $_SESSION['office_username'] = $u['username'];
            $_SESSION['office_group']    = $u['member_group_id']; // 1 = Admin, 2 = Revenda
            $_SESSION['office_credits']  = $u['credits'];

            $encontrou = true;
            break;
        }
    }
}

if ($encontrou) {
    header("Location: dashboard.php"); // Manda para a home
} else {
    header("Location: login.php?erro=invalido"); // Volta pro login
}
?>


📑 Documentação Técnica: Fase 3 (Gestão de Revendas)Objetivo: Permitir que o Administrador do Painel Office cadastre, visualize e gerencie seus revendedores.Pré-requisitos:Sessão ativa (session.php com group_id == 1).Motor de API (api.php) configurado.1. Regras de Negócio (Lógica do Servidor)No XUI.ONE, a hierarquia é definida pelo campo member_group_id.Admin: member_group_id = 1 (Pode criar revendas).Revendedor: member_group_id = 2 (Pode criar linhas, mas não outros revendedores).Dono (Owner): Todo usuário criado deve ter um "pai" (owner_id).2. Listar Meus RevendedoresArquivo: revendas.phpEndpoint: get_usersLógica: O endpoint retorna todos os usuários. O PHP deve filtrar para mostrar apenas os "filhos" do admin logado.Snippet de Código (Listagem)PHP<?php
// revendas.php
require_once 'session.php';
require_once 'api.php';

// Apenas Admin (Grupo 1) pode ver essa página
if ($_SESSION['office_group'] != 1) {
    die("Acesso negado: Apenas Administradores.");
}

// Busca TODOS os usuários
$response = xui_api('get_users');
$meus_revendedores = [];

if ($response['result']) {
    foreach ($response['data'] as $user) {
        // FILTRO:
        // 1. O 'pai' deve ser o usuário logado
        // 2. O grupo deve ser 2 (Revendedor)
        if ($user['owner_id'] == $_SESSION['office_user_id'] && $user['member_group_id'] == 2) {
            $meus_revendedores[] = $user;
        }
    }
}
?>

<table>
    <thead>
        <tr>
            <th>Username</th>
            <th>Créditos</th>
            <th>Status</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($meus_revendedores as $revenda): ?>
        <tr>
            <td><?= htmlspecialchars($revenda['username']) ?></td>
            <td><?= $revenda['credits'] ?></td>
            <td><?= ($revenda['status'] == 1) ? 'Ativo' : 'Bloqueado' ?></td>
            <td>
                <a href="editar_revenda.php?id=<?= $revenda['id'] ?>">Editar/Recarregar</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
3. Criar Novo RevendedorArquivo: action_criar_revenda.phpEndpoint: create_userParâmetros Obrigatórios (Payload)CampoValor / LógicaDescriçãousernameInput do FormLogin únicopasswordInput do FormSenhamember_group_id2 (Fixo)Define que é uma RevendacreditsInput do FormSaldo inicialowner_id$_SESSION['office_user_id']Vincula a quem está criandostatus1Cria já ativoSnippet de Código (Criação)PHP<?php
// action_criar_revenda.php
require_once 'session.php';
require_once 'api.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Prepara os dados para a API
    $dados_novo_revenda = [
        'username'        => $_POST['username'],
        'password'        => $_POST['password'],
        'email'           => $_POST['email'], // Opcional
        'member_group_id' => 2,               // OBRIGATÓRIO: 2 = Revenda
        'owner_id'        => $_SESSION['office_user_id'], // Quem é o pai
        'credits'         => $_POST['credits'], // Créditos iniciais
        'status'          => 1,
        'reseller_dns'    => '', // Se tiver DNS personalizado, coloque aqui
        'notes'           => 'Criado pelo Painel Office'
    ];

    // Envia para o XUI
    $resultado = xui_api('create_user', $dados_novo_revenda);

    if ($resultado['result']) {
        echo "Revenda criada com sucesso!";
    } else {
        echo "Erro ao criar: " . json_encode($resultado);
    }
}
?>
4. Recarregar Revendedor (Adicionar Créditos)Arquivo: action_recarregar.phpEstratégia: Como o endpoint edit_user substitui valores (não soma), a lógica segura é: "Ler saldo atual" -> "Somar" -> "Enviar novo saldo".Alternativa: O manual mencionou add_credits, mas caso falhe, usamos a lógica abaixo.Fluxo Lógico (Seguro)GET get_user&id=10 (Descobrir que o saldo atual é 50).PHP: $novo_saldo = 50 + 10 (recarga) = 60.POST edit_user&id=10&credits=60.PHP<?php
// Exemplo de lógica de recarga segura
$id_revenda = $_POST['id_revenda'];
$valor_recarga = $_POST['valor'];

// 1. Pega dados atuais
$user_atual = xui_api('get_user', ['id' => $id_revenda]);
$saldo_atual = $user_atual['data']['credits'];

// 2. Calcula
$novo_total = $saldo_atual + $valor_recarga;

// 3. Atualiza
xui_api('edit_user', [
    'id' => $id_revenda,
    'credits' => $novo_total
]);
?>
✅ Checklist de Documentação da Fase 3[ ] Hierarquia: Definido que member_group_id deve ser sempre 2.[ ] Filtragem: O painel Office filtra visualmente (foreach) para não mostrar usuários de outros Admins.[ ] Vínculo: Uso obrigatório de owner_id pegando da $_SESSION.[ ] Recarga: Definida a lógica de "Ler antes de Gravar" para evitar erros de saldo.

📑 Documentação Técnica: Fase 4 (Vendas e Gestão de Linhas)
Objetivo: Permitir a criação de Usuários Finais (Linhas IPTV) e Testes (Trials), além de listar os clientes ativos.

Endpoint Principal: create_line Endpoint Listagem: get_lines

1. Criar Linha (Venda / Oficial)
Arquivo: action_criar_linha.php

Método: POST

Regras de Negócio Críticas (Validadas em Testes)
Formato de Data: A API XUI neste servidor EXIGE formato YYYY-MM-DD HH:MM. Timestamps numéricos causam erro ou ativam "Never Expire".

Bouquets: Devem ser enviados como uma String JSON (ex: "[1,2]"), e não como array PHP.

Member ID: Deve ser o ID da sessão ($_SESSION['office_user_id']), para debitar os créditos da revenda correta.

Snippet de Código (Processamento)
PHP
<?php
// action_criar_linha.php
require_once 'session.php';
require_once 'api.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // 1. Definição da Data de Expiração
    // Se for teste (is_trial), soma horas. Se for oficial, soma dias.
    $is_trial = isset($_POST['is_trial']) ? true : false;

    if ($is_trial) {
        $tempo = "+3 hours"; // Configuração de tempo de teste
        $nota  = "Teste gerado pelo Painel Office";
    } else {
        // Se vier do select de pacotes (Fase 1), pegue a duração de lá.
        // Aqui assumiremos 30 dias padrão para simplificar o exemplo.
        $tempo = "+30 days";
        $nota  = "Cliente Oficial";
    }

    // FORMATAÇÃO OBRIGATÓRIA: Y-m-d H:i (Ex: 2026-04-01 12:00)
    $exp_date = date('Y-m-d H:i', strtotime($tempo));

    // 2. Tratamento dos Buquês (Canais)
    // O formulário envia array: [1, 5, 22]. A API quer string: "[1,5,22]"
    $bouquets = isset($_POST['bouquet_ids']) ? $_POST['bouquet_ids'] : [];
    $bouquets_json = json_encode(array_values($bouquets)); // array_values garante chaves sequenciais

    // 3. Montagem do Payload
    $dados_linha = [
        'username'        => $_POST['username'],
        'password'        => $_POST['password'],
        'member_id'       => $_SESSION['office_user_id'], // OBRIGATÓRIO: Quem criou
        'exp_date'        => $exp_date,                    // OBRIGATÓRIO: Data Texto
        'bouquet_ids'     => $bouquets_json,               // OBRIGATÓRIO: JSON String
        'max_connections' => 1,
        'package_id'      => $_POST['package_id'],         // Opcional, mas bom para relatórios
        'admin_notes'     => $nota,
        'reseller_notes'  => 'Criado via Painel Office',
        'email'           => $_POST['email'] ?? ''         // Opcional
    ];

    // 4. Envio
    $resultado = xui_api('create_line', $dados_linha);

    if ($resultado['result']) {
        echo "✅ Linha criada com sucesso! <br>";
        echo "Expira em: " . $exp_date;
        // Aqui você pode redirecionar para a página de Download M3U
    } else {
        echo "❌ Erro ao criar: " . json_encode($resultado);
    }
}
?>
2. Listar Meus Clientes
Arquivo: clientes.php

Endpoint: get_lines

Desafio: A API retorna TUDO. Precisamos filtrar e paginar para o site não travar.

Lógica de Filtragem
O XUI não possui um filtro nativo robusto tipo WHERE member_id = X na URL pública dessa versão. A filtragem segura é feita no PHP (loop).

PHP
<?php
// clientes.php
require_once 'session.php';
require_once 'api.php';

// Busca linhas (Use limit para não travar se tiver 10k clientes)
$response = xui_api('get_lines', ['limit' => 1000, 'offset' => 0]);
$meus_clientes = [];

if ($response['result']) {
    foreach ($response['data'] as $linha) {
        // FILTRO: Só mostre se a linha pertence ao usuário logado
        if ($linha['member_id'] == $_SESSION['office_user_id']) {
            $meus_clientes[] = $linha;
        }
    }
}
?>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Usuário</th>
            <th>Senha</th>
            <th>Vencimento</th>
            <th>Status</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($meus_clientes as $cli): ?>
        <?php
            // Converter timestamp do retorno para data legível
            // Nota: O get_lines retorna TIMESTAMP unix, diferente do create_line que pede TEXTO.
            $vencimento = date('d/m/Y H:i', $cli['exp_date']);

            // Verifica se está vencido
            $vencido = ($cli['exp_date'] < time());
            $cor_status = $vencido ? 'red' : 'green';
        ?>
        <tr>
            <td><?= $cli['username'] ?></td>
            <td><?= $cli['password'] ?></td>
            <td style="color:<?= $cor_status ?>"><?= $vencimento ?></td>
            <td><?= ($cli['enabled'] == 1) ? 'Ativo' : 'Bloqueado' ?></td>
            <td>
                <a href="renovar.php?id=<?= $cli['id'] ?>" class="btn btn-sm btn-success">Renovar</a>
                <a href="gerar_lista.php?user=<?= $cli['username'] ?>&pass=<?= $cli['password'] ?>" class="btn btn-sm btn-info">Lista M3U</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
3. Gerador de Links (M3U)
Não precisamos chamar a API para baixar o arquivo. Basta construir a URL correta.

Padrão M3U: http://URL_DO_PAINEL:PORTA/get.php?username=USER&password=PASS&type=m3u_plus&output=ts

Snippet PHP (Modal de Download)
PHP
<?php
$protocolo = "http"; // ou https
$servidor  = "192.168.100.209"; // Seu IP ou Domínio DNS
$porta     = "80"; // Porta do streaming (geralmente 80 ou 8080)

$link_m3u = "{$protocolo}://{$servidor}:{$porta}/get.php?username={$username}&password={$password}&type=m3u_plus&output=ts";
$link_hls = "{$protocolo}://{$servidor}:{$porta}/get.php?username={$username}&password={$password}&type=m3u_plus&output=m3u8";

echo "<input type='text' value='{$link_m3u}' readonly>";
?>
4. Renovar Linha (Manutenção)
Arquivo: action_renovar.php

Endpoint: edit_line

Lógica: Ler data atual -> Adicionar 30 dias -> Enviar Update.

PHP
<?php
// ... includes ...

$id_linha = $_POST['id_linha'];

// 1. Busca dados atuais da linha para saber quando vence
// Infelizmente get_line (singular) as vezes não existe, usa-se get_lines filtrando ou mysql_query se falhar.
// Assumindo que temos a data atual do banco:
$data_atual_timestamp = time(); // Ou data de vencimento antiga se quiser acumular

$nova_data = date('Y-m-d H:i', strtotime('+30 days', $data_atual_timestamp));

// 2. Envia Renovação
$dados = [
    'line_id'  => $id_linha, // Note que em edição o nome pode ser line_id ou id
    'exp_date' => $nova_data,
    'enabled'  => 1 // Garante que desbloqueia se estava vencido
];

$res = xui_api('edit_line', $dados);
// ... trata resposta ...
?>
✅ Checklist da Fase 4
[ ] Formulário de Criação: Deve conter Checkboxes de canais (Fase 1) e Select de Pacotes.

[ ] Conversão de Data: Garantir que o PHP envia string Y-m-d H:i na criação.

[ ] JSON Encoding: Garantir que bouquet_ids vai como string JSON.

[ ] Filtro de Visualização: Garantir que revendedor A não veja clientes do revendedor B no PHP.

Com a Fase 4 (Vendas) garantindo o dinheiro no caixa, vamos para a Fase 5: A Visão de Comando.Nesta fase, transformamos dados brutos em inteligência. O objetivo é que, ao logar, você ou seu revendedor saibam imediatamente:Quanto dinheiro (créditos) tem.Quantos clientes estão ativos.Quem está assistindo agora (Monitoramento).Salve este arquivo como DOC_FASE_5.md.📑 Documentação Técnica: Fase 5 (Dashboard & Monitoramento)Objetivo: Criar um painel visual ("Cockpit") que resume a saúde do negócio e permite monitorar conexões em tempo real.Arquivos: dashboard.php (Home) e live.php (Monitoramento).1. Mapeamento de Endpoints (Inteligência)Para montar o painel, cruzaremos dados de três fontes:Widget / CardFonte de DadosLógica PHPSaldo Atualget_userLer o campo credits do usuário logado.Total Clientesget_linesContar o número de itens no array retornado (count($data)).Online Agoralive_connectionsListar quem está conectado no servidor.Vencendo Hojeget_linesFiltrar linhas onde exp_date está entre hoje 00:00 e hoje 23:59.2. A Tela Principal: dashboard.phpEste script carrega rápido, mostra os números grandes e atalhos para as ações principais.PHP<?php
// dashboard.php
require_once 'session.php';
require_once 'api.php';

// 1. Atualizar Saldo (Sempre fresco)
$meus_dados = xui_api('get_user', ['id' => $_SESSION['office_user_id']]);
$saldo = $meus_dados['data']['credits'] ?? 0;
$_SESSION['office_credits'] = $saldo; // Atualiza a sessão

// 2. Buscar estatísticas de clientes
// Limit 1000 apenas para contar (otimização)
$clientes_api = xui_api('get_lines', ['member_id' => $_SESSION['office_user_id']]);
$total_clientes = 0;
$ativos = 0;
$vencidos = 0;

if ($clientes_api['result']) {
    $total_clientes = count($clientes_api['data']);
    foreach ($clientes_api['data'] as $c) {
        if ($c['exp_date'] > time()) {
            $ativos++;
        } else {
            $vencidos++;
        }
    }
}

// 3. Buscar Conexões Ativas (Quem está assistindo?)
$live_api = xui_api('live_connections');
$online_agora = 0;
if ($live_api['result']) {
    // Filtra apenas os meus clientes
    foreach ($live_api['data'] as $conn) {
        // A API live_connections geralmente retorna o ID do revendedor (member_id)
        if ($conn['member_id'] == $_SESSION['office_user_id']) {
            $online_agora++;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Painel Office | Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>🚀 Painel de Controle</h2>
        <div>
            <span class="badge bg-warning text-dark fs-5">💰 Saldo: <?= $saldo ?></span>
            <a href="logout.php" class="btn btn-outline-danger ms-2">Sair</a>
        </div>
    </div>

    <div class="row text-center mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white mb-3">
                <div class="card-body">
                    <h1><?= $total_clientes ?></h1>
                    <h6>Total Clientes</h6>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white mb-3">
                <div class="card-body">
                    <h1><?= $ativos ?></h1>
                    <h6>Ativos</h6>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white mb-3">
                <div class="card-body">
                    <h1><?= $vencidos ?></h1>
                    <h6>Vencidos</h6>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white mb-3">
                <div class="card-body">
                    <h1><?= $online_agora ?></h1>
                    <h6>Online Agora 🟢</h6>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <a href="criar_linha.php" class="btn btn-lg btn-success w-100 py-4 mb-3">
                ➕ Criar Novo Cliente
            </a>
        </div>
        <div class="col-md-6">
            <a href="criar_teste.php" class="btn btn-lg btn-secondary w-100 py-4 mb-3">
                ⏱️ Gerar Teste Rápido
            </a>
        </div>
    </div>

</div>

</body>
</html>
3. Monitoramento em Tempo Real: monitor.phpEste arquivo detalha quem está assistindo e o quê. É essencial para suporte técnico (saber se o canal travou) ou segurança (detectar compartilhamento de senha).Endpoint: live_connectionsPHP<?php
// monitor.php
require_once 'session.php';
require_once 'api.php';

$resp = xui_api('live_connections');
$conexoes = [];

if ($resp['result']) {
    foreach ($resp['data'] as $conn) {
        // Filtro de Segurança: Só vejo meus clientes
        if ($conn['member_id'] == $_SESSION['office_user_id']) {
            $conexoes[] = $conn;
        }
    }
}
?>

<div class="container mt-4">
    <h3>📡 Monitoramento Ao Vivo</h3>
    <table class="table table-hover">
        <thead class="table-dark">
            <tr>
                <th>Usuário</th>
                <th>Canal / Filme</th>
                <th>IP</th>
                <th>Duração</th>
                <th>Ação</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($conexoes as $row): ?>
            <tr>
                <td><strong><?= $row['user'] ?></strong></td>
                <td>
                    <?= $row['stream_name'] ?? ('Stream ID: ' . $row['stream_id']) ?>
                </td>
                <td><?= $row['ip'] ?></td>
                <td><?= gmdate("H:i:s", $row['duration']) ?></td>
                <td>
                    <a href="action_derrubar.php?pid=<?= $row['pid'] ?>" class="btn btn-sm btn-danger">Derrubar</a>
                </td>
            </tr>
            <?php endforeach; ?>

            <?php if(empty($conexoes)): ?>
                <tr><td colspan="5" class="text-center">Ninguém assistindo no momento.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
4. O Botão de Pânico: Derrubar ConexãoSe um cliente travar ou estiver sendo hackeado, o revendedor precisa derrubar a conexão.Arquivo: action_derrubar.phpEndpoint: kill_connection ou kill_line (Depende da versão, vamos testar o mais comum).PHP<?php
// action_derrubar.php
// OBS: Alguns XUI usam 'pid' (Process ID) para matar conexão específica.
// Outros exigem matar a linha inteira.

require_once 'api.php';
$pid = $_GET['pid'];

// Tenta matar pelo PID (Sessão única)
$res = xui_api('kill_connection', ['pid' => $pid]);

header("Location: monitor.php?msg=derrubado");
?>
✅ Resumo Final do ProjetoCom a conclusão da Fase 5, você tem um SaaS (Software as a Service) completo em mãos:Backend: Conexão robusta com XUI via API.Dados: Mapeamento inteligente de pacotes e buquês (removendo lixo).Segurança: Sistema de Login próprio (sem expor o painel original).Vendas: Criação de linhas e testes automatizados.Gestão: Dashboard financeiro e monitoramento técnico.





APIS XUI
CRIA REVENDA:
curl --location --request POST 'http://192.168.100.209/kIzFSjQu/?api_key=DFE74ECCBA19D32DCD758C4D3D5AF0F6&action=create_user' \
--header 'Content-Type: application/x-www-form-urlencoded' \
--data-urlencode 'username=revenda_office_01' \
--data-urlencode 'password=senha123' \
--data-urlencode 'email=teste@office.com' \
--data-urlencode 'member_group_id=2' \
--data-urlencode 'owner_id=1' \
--data-urlencode 'credits=10' \
--data-urlencode 'notes=Criado via Postman'


CRIA CLIENTE:
curl --location --request POST 'http://192.168.100.209/kIzFSjQu/?api_key=DFE74ECCBA19D32DCD758C4D3D5AF0F6&action=create_line' \
--header 'Content-Type: application/x-www-form-urlencoded' \
--data-urlencode 'username=TesteFinal_API_Office' \
--data-urlencode 'password=21995009140' \
--data-urlencode 'member_id=1' \
--data-urlencode 'bouquet_ids=[2,3,5,6,7,9,11,23]' \
--data-urlencode 'exp_date=2026-04-01 12:00' \
--data-urlencode 'max_connections=1' \
--data-urlencode 'admin_notes=Criado via Painel Office' \
--data-urlencode 'reseller_notes=Integracao API' \
--data-urlencode 'email=admin@9tv.us'
