<?php

return [
    'paths' => ['api/*'],                           // Selecione as rotas da API para aplicar o CORS
    'allowed_methods' => ['*'],                     // Permitir todos os métodos HTTP
    'allowed_origins' => ['https://micore.com.br'], // Defina o domínio de origem permitido
    'allowed_headers' => ['*'],                     // Permitir todos os cabeçalhos
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
