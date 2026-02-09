<?php
use App\Models\Client;
use App\Models\ClientDomain;

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

        while (ClientDomain::where('domain', $domain . '.micore.com.br')->exists()) {
            // Adiciona um número incremental ao domínio
            $domain = $originalDomain . '-' . $counter;
            $counter++;
        }

        return $domain;
    }
}


// Generate random color code HEX
if (!function_exists('randomColor')) {
    function randomColor()
    {
        $letters = '0123456789ABCDEF';
        $color = '#';
        for ($i = 0; $i < 6; $i++) {
            $color .= $letters[rand(0, 15)];
        }
        return $color;
    }
}

// PUT THE BACKGROUND IN THE TEXT COLOR
if (!function_exists('hex2rgb')) {
    function hex2rgb($colour, $opacity) {

        // REMOVE # FROM STRING
        $colour = ltrim($colour, '#');

        // EXTRACT RGB FROM HEX
        $rgb = sscanf($colour, '%2x%2x%2x');
        $rgb[] = $opacity;

        // RETURN RGBA
        return sprintf('rgb(%d, %d, %d, %d%%)', ...$rgb);

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

if (! function_exists('header_menu_items')) {
    function header_menu_items(): array
    {
        return [
            [
                'type' => 'link',
                'label' => 'Cliente',
                'route' => 'clients.index',
                'active_routes' => ['clients.index'],
            ],
            [
                'type' => 'submenu',
                'label' => 'Notícias',
                'active_routes' => ['news.index', 'news.categories.index'],
                'width' => 'w-100px',
                'children' => [
                    [
                        'label' => 'Notícias cadastradas',
                        'route' => 'news.index',
                        'active_routes' => ['news.index'],
                    ],
                    [
                        'label' => 'Categorias',
                        'route' => 'news.categories.index',
                        'active_routes' => ['news.categories.index'],
                    ],
                ],
            ],
            [
                'type' => 'link',
                'label' => 'Pacotes',
                'route' => 'packages.index',
                'active_routes' => ['packages.index'],
            ],
            [
                'type' => 'submenu',
                'label' => 'Vendas',
                'active_routes' => ['orders.index', 'orders.show', 'coupons.index', 'coupons.create', 'coupons.edit'],
                'width' => 'w-125px',
                'children' => [
                    [
                        'label' => 'Pedidos',
                        'route' => 'orders.index',
                        'active_routes' => ['orders.index', 'orders.show'],
                    ],
                    [
                        'label' => 'Cupons',
                        'route' => 'coupons.index',
                        'active_routes' => ['coupons.index', 'coupons.create', 'coupons.edit'],
                    ],
                ],
            ],
            [
                'type' => 'submenu',
                'label' => 'Módulos',
                'active_routes' => ['modules.index', 'modules.categories.index', 'groups.index', 'resources.index'],
                'width' => 'w-100px',
                'children' => [
                    [
                        'label' => 'Lista de modulos',
                        'route' => 'modules.index',
                        'active_routes' => ['modules.index'],
                    ],
                    [
                        'label' => 'Categorias de módulos',
                        'route' => 'modules.categories.index',
                        'active_routes' => ['modules.categories.index'],
                    ],
                    [
                        'label' => 'Grupo de Recursos',
                        'route' => 'groups.index',
                        'active_routes' => ['groups.index'],
                    ],
                    [
                        'label' => 'Recursos',
                        'route' => 'resources.index',
                        'active_routes' => ['resources.index'],
                    ],
                ],
            ],
            [
                'type' => 'submenu',
                'label' => 'Configuração',
                'active_routes' => [
                    'tickets.index',
                    'suggestions.index',
                    'errors.index',
                    'systems.update.all.db',
                ],
                'width' => 'w-175px',
                'children' => [
                    [
                        'label' => 'Tickets',
                        'route' => 'tickets.index',
                        'active_routes' => ['tickets.index'],
                        'icon' => ['class' => 'ki-duotone ki-notification-on fs-3', 'paths' => 5],
                    ],
                    [
                        'label' => 'Sugestões',
                        'route' => 'suggestions.index',
                        'active_routes' => ['suggestions.index'],
                        'icon' => ['class' => 'ki-duotone ki-android fs-3', 'paths' => 5],
                    ],
                    [
                        'label' => 'Errors',
                        'route' => 'errors.index',
                        'active_routes' => ['errors.index'],
                        'icon' => ['class' => 'ki-duotone ki-calendar-2 fs-3', 'paths' => 5],
                    ],
                    [
                        'label' => 'Atualizar em massa',
                        'route' => 'systems.update.all.db',
                        'active_routes' => ['systems.update.all.db'],
                        'icon' => ['class' => 'ki-duotone ki-file-added fs-3', 'paths' => 5],
                    ],
                ],
            ],
        ];
    }
}
