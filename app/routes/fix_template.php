<?php

use Illuminate\Support\Facades\Route;
use App\Models\AppSetting;

Route::get('/fix-message-template', function () {
    $template = "👤 USUÁRIO: {USERNAME}
🔑 SENHA: {PASSWORD}
📅 VENCIMENTO: {EXPIRATION}

🔗 Link M3U: {M3U_URL}
🔗 Link HLS: {HLS_URL}

🔗 LINK http://{DNS}


📺 BLESSED PLAYER
⬇️ DOWNLOADER: 6390937
🔗 Link Direto: https://fui.ai/blessedmobile
📱 LG, Samsung, Android TV, Roku, Play Store, IOS
🔑 Código: p2player
🟡 LOGAR COM CODIGO, USUARIO E SENHA

📺 Vizzion Play
⬇️ DOWNLOADER: 528749
🔗 Link Direto: http://bit.ly/4jbym0g
📱 LG, Samsung, Android, Roku, IOS
🔑 Código: 812337
🟡 LOGAR COM CODIGO, USUARIO E SENHA

📺 P2Player+
⬇️ DOWNLOADER: 1961174
🔗 Link Direto: https://dl.ntdown.me/53859
📱 Android, Tv Box
🟡 LOGAR COM USUARIO E SENHA

📺 Multiverse
⬇️ DOWNLOADER: 447585
🔗 Link Direto: https://dl.ntdown.me/99537
📱 Android, Tv Box
🟡 LOGAR COM USUARIO E SENHA

📺 P2PLAYER TIVIMATE
⬇️ DOWNLOADER: 9026328
🔗 Link Direto: http://aftv.news/9026328
📱 Android, Tv Box
🟡 LOGAR COM USUARIO E SENHA";

    AppSetting::set('client_message_template', $template);
    
    return "Template de mensagem atualizado com sucesso para o formato Otimizado WhatsApp! <br><br> <a href='/'>Voltar para Login</a>";
});
