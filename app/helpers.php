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

    // Remove os caracteres especiais
    $domain = cleanString($domain);

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

function cleanString($text) {
    $utf8 = array(
        '/[áàâãªä]/u'   =>   'a',
        '/[ÁÀÂÃÄ]/u'    =>   'A',
        '/[ÍÌÎÏ]/u'     =>   'I',
        '/[íìîï]/u'     =>   'i',
        '/[éèêë]/u'     =>   'e',
        '/[ÉÈÊË]/u'     =>   'E',
        '/[óòôõºö]/u'   =>   'o',
        '/[ÓÒÔÕÖ]/u'    =>   'O',
        '/[úùûü]/u'     =>   'u',
        '/[ÚÙÛÜ]/u'     =>   'U',
        '/ç/'           =>   'c',
        '/Ç/'           =>   'C',
        '/ñ/'           =>   'n',
        '/Ñ/'           =>   'N',
        '/–/'           =>   '-', // UTF-8 hyphen to "normal" hyphen
        '/[’‘‹›‚]/u'    =>   ' ', // Literally a single quote
        '/[“”«»„]/u'    =>   ' ', // Double quote
        '/ /'           =>   ' ', // nonbreaking space (equiv. to 0x160)
    );
    return preg_replace(array_keys($utf8), array_values($utf8), $text);
}



function generateShortName($fullName) {
    $parts = explode(' ', trim($fullName));
    if (count($parts) > 1) {
        return $parts[0] . ' ' . end($parts); // Primeiro nome + último sobrenome
    }
    return $parts[0]; // Caso só tenha um nome
}