<?php
use App\Models\Client;

/**
 * Verifica se o domínio está disponível e gera um novo se necessário.
 *
 * @param  string  $domain
 * @return string
 */
if (!function_exists('verifyIfAllow')) {
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

        while (Client::where('domain', $domain . '.micore.com.br')->exists()) {
            // Adiciona um número incremental ao domínio
            $domain = $originalDomain . '-' . $counter;
            $counter++;
        }

        return $domain;
    }
}

if (!function_exists('cleanString')) {
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
}

if (!function_exists('toDecimal')) {
    function toDecimal($value) {
        // Remove espaços extras e "R$" (ou qualquer caractere não numérico, exceto ponto e vírgula)
        $value = trim($value);
        $value = preg_replace('/[^\d,\.]/', '', $value);

        // Se o número tiver separador de milhar (ex: 1.000,00), removemos os pontos
        if (strpos($value, ',') !== false) {
            $value = str_replace('.', '', $value); // Remove separadores de milhar
            $value = str_replace(',', '.', $value); // Troca vírgula decimal por ponto
        }
        // Converte para float e formata com 2 casas decimais
        return number_format(floatval($value), 2, '.', '');
    }
}

if (!function_exists('generateShortName')) {
    function generateShortName($fullName) {
        $parts = explode(' ', trim($fullName));
        if (count($parts) > 1) {
            return $parts[0] . ' ' . end($parts); // Primeiro nome + último sobrenome
        }
        return $parts[0]; // Caso só tenha um nome
    }
}

if (! function_exists('onlyNumbers')) {
    function onlyNumbers($number){
        return preg_replace('/\D/', '', $number);
    }
}