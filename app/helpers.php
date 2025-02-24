<?php
use App\Models\Client;

/**
 * Verifica se o domínio está disponível e gera um novo se necessário.
 *
 * @param  string  $domain
 * @return string
 */
function verifyIfAllow($domain)
{
    // Remover "www." caso o usuário tenha inserido
    $domain = preg_replace('/^www\./', '', strtolower($domain));

    // Gera um nome de tabela permitido
    $domain = str_replace(' ', '-', $domain);

    // Verifica se já existe no banco de dados
    $originalDomain = $domain;
    $counter = 1;

    while (Client::where('domain', $domain)->exists()) {
        // Adiciona um número incremental ao domínio
        $domain = $originalDomain . '-' . $counter;
        $counter++;
    }

    return $domain;
}


function generateShortName($fullName) {
    $parts = explode(' ', trim($fullName));
    if (count($parts) > 1) {
        return $parts[0] . ' ' . end($parts); // Primeiro nome + último sobrenome
    }
    return $parts[0]; // Caso só tenha um nome
}