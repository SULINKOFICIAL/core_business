<?php

/**
 * Configurações do Meta
 * 
 * Parametros obtidos no aplicativo:
 * https://developers.facebook.com/apps/
 * 
 */
return [
    'verify_token'                  => env('META_VERIFY_TOKEN'),
    'client_id'                     => env('META_CLIENT_ID'),
    'client_secret'                 => env('META_CLIENT_SECRET'),
    'embedded_signup_config_id'     => env('META_EMBEDDED_SIGNUP_CONFIG_ID'),
    'embedded_signup_graph_version' => env('META_EMBEDDED_SIGNUP_GRAPH_VERSION'),
    
    
    'app_instagram_id'              => env('META_APP_INSTAGRAM_ID'),
    'app_instagram_secret'          => env('META_APP_INSTAGRAM_SECRET'),
];
