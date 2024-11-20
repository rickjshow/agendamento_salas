<?php

return [
    'paths' => ['api/*', 'login'], // Adicione suas rotas que precisam de CORS aqui
    'allowed_methods' => ['*'], // Permite todos os métodos HTTP (GET, POST, etc.)
    'allowed_origins' => ['http://localhost:3000'], // Permite apenas o domínio do seu frontend Next.js
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'], // Permite todos os cabeçalhos
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true, // Habilita o envio de cookies e credenciais
];
