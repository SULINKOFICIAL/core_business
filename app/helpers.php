<?php
use App\Models\Tenant;
use App\Models\TenantDomain;

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

        while (TenantDomain::where('domain', $domain . '.micore.com.br')->exists()) {
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

if (! function_exists('formatTaskJobLabel')) {
    function formatTaskJobLabel($jobName)
    {
        if (empty($jobName)) {
            return '-';
        }

        if ($jobName === 'manual_batch') {
            return 'Lote manual de tarefas';
        }

        return ucwords(str_replace('_', ' ', (string) $jobName));
    }
}

if (! function_exists('header_menu_items')) {
    function header_menu_items(): array
    {
        return [
            [
                'type' => 'link',
                'label' => 'Dashboard',
                'route' => 'dashboard',
                'active_routes' => ['dashboard', 'index'],
            ],
            [
                'type' => 'submenu',
                'label' => 'Instalações',
                'active_routes' => [
                    'tenants.index',
                    'tickets.index',
                    'suggestions.index',
                    'tenants.integrations.index',
                    'tenants.integrations.process',
                ],
                'children' => [
                    [
                        'label' => 'Listagem',
                        'route' => 'tenants.index',
                        'active_routes' => ['tenants.index'],
                        'icon' => ['class' => 'fa-solid fa-list fs-5'],
                    ],
                    [
                        'label' => 'Tickets',
                        'route' => 'tickets.index',
                        'active_routes' => ['tickets.index'],
                        'icon' => ['class' => 'fa-solid fa-ticket fs-5'],
                    ],
                    [
                        'label' => 'Sugestões',
                        'route' => 'suggestions.index',
                        'active_routes' => ['suggestions.index'],
                        'icon' => ['class' => 'fa-solid fa-lightbulb fs-5'],
                    ],
                    [
                        'label' => 'Integrações',
                        'route' => 'tenants.integrations.index',
                        'active_routes' => ['tenants.integrations.index', 'tenants.integrations.process'],
                        'icon' => ['class' => 'fa-solid fa-plug fs-5'],
                    ],
                ],
            ],
            [
                'type' => 'submenu',
                'label' => 'Vendas',
                'active_routes' => ['orders.index', 'orders.show', 'coupons.index', 'coupons.create', 'coupons.edit'],
                'children' => [
                    [
                        'label' => 'Pedidos',
                        'route' => 'orders.index',
                        'active_routes' => ['orders.index', 'orders.show'],
                        'icon' => ['class' => 'fa-solid fa-cart-shopping fs-5'],
                    ],
                    [
                        'label' => 'Cupons',
                        'route' => 'coupons.index',
                        'active_routes' => ['coupons.index', 'coupons.create', 'coupons.edit'],
                        'icon' => ['class' => 'fa-solid fa-tags fs-5'],
                    ],
                ],
            ],
            [
                'type' => 'submenu',
                'label' => 'Notícias',
                'active_routes' => ['news.index', 'news.categories.index'],
                'children' => [
                    [
                        'label' => 'Notícias cadastradas',
                        'route' => 'news.index',
                        'active_routes' => ['news.index'],
                        'icon' => ['class' => 'fa-solid fa-newspaper fs-5'],
                    ],
                    [
                        'label' => 'Categorias',
                        'route' => 'news.categories.index',
                        'active_routes' => ['news.categories.index'],
                        'icon' => ['class' => 'fa-solid fa-folder-tree fs-5'],
                    ],
                ],
            ],
            // [
            //     'type' => 'link',
            //     'label' => 'Pacotes',
            //     'route' => 'packages.index',
            //     'active_routes' => ['packages.index'],
            // ],
            [
                'type' => 'submenu',
                'label' => 'Produto',
                'active_routes' => ['modules.index', 'modules.categories.index', 'groups.index', 'resources.index'],
                'children' => [
                    [
                        'label' => 'Lista de modulos',
                        'route' => 'modules.index',
                        'active_routes' => ['modules.index'],
                        'icon' => ['class' => 'fa-solid fa-cubes fs-5'],
                    ],
                    [
                        'label' => 'Recursos',
                        'route' => 'resources.index',
                        'active_routes' => ['resources.index'],
                        'icon' => ['class' => 'fa-solid fa-toolbox fs-5'],
                    ],
                    [
                        'label' => 'Atualizar Módulos',
                        'route' => 'systems.get.resources',
                        'active_routes' => ['systems.get.resources'],
                        'icon' => ['class' => 'fa-solid fa-code-compare fs-5'],
                    ],
                ],
            ],
            [
                'type' => 'submenu',
                'label' => 'Logs',
                'active_routes' => [
                    'errors.index',
                    'errors.application',
                    'logs.apis.index',
                    'task.history.index',
                    'task.history.process',
                    'task.history.show',
                ],
                'children' => [
                    [
                        'label' => 'Erros de Tenantes',
                        'route' => 'errors.index',
                        'active_routes' => ['errors.index'],
                        'icon' => ['class' => 'fa-solid fa-circle-exclamation fs-5'],
                    ],
                    [
                        'label' => 'Erros da Aplicação',
                        'route' => 'errors.application',
                        'active_routes' => ['errors.application'],
                        'icon' => ['class' => 'fa-solid fa-file-circle-exclamation fs-5'],
                    ],
                    [
                        'label' => 'Logs APIs',
                        'route' => 'logs.apis.index',
                        'active_routes' => ['logs.apis.index'],
                        'icon' => ['class' => 'fa-solid fa-file-lines fs-5'],
                    ],
                    [
                        'label' => 'Histórico de Tarefas',
                        'route' => 'task.history.index',
                        'active_routes' => ['task.history.index', 'task.history.process', 'task.history.show'],
                        'icon' => ['class' => 'fa-solid fa-clock-rotate-left fs-5'],
                    ],
                ],
            ],
            [
                'type' => 'submenu',
                'label' => 'Configuração',
                'active_routes' => [
                    'users.index',
                    'users.create',
                    'users.edit',
                    'system.settings.mail.edit',
                    'system.settings.whatsapp.edit',
                    'systems.run.scheduled.now',
                    'systems.run.scheduled.now.client',
                    'systems.update.all.systems',
                ],
                'children' => [
                    [
                        'label' => 'Usuários',
                        'route' => 'users.index',
                        'active_routes' => ['users.index', 'users.create', 'users.edit'],
                        'icon' => ['class' => 'fa-solid fa-users fs-5'],
                    ],
                    [
                        // Direciona para a nova página de configurações SMTP do sistema.
                        'label' => 'SMTP',
                        'route' => 'system.settings.mail.edit',
                        'active_routes' => ['system.settings.mail.edit'],
                        'icon' => ['class' => 'fa-solid fa-envelope fs-5'],
                    ],
                    [
                        // Mantém a configuração do WhatsApp em uma página própria.
                        'label' => 'WhatsApp',
                        'route' => 'system.settings.whatsapp.edit',
                        'active_routes' => ['system.settings.whatsapp.edit'],
                        'icon' => ['class' => 'fa-solid fa-gear fs-5'],
                    ],
                    [
                        'label' => 'Disparar Tarefas',
                        'route' => 'systems.run.scheduled.now',
                        'active_routes' => ['systems.run.scheduled.now', 'systems.run.scheduled.now.client'],
                        'confirm_message' => 'Deseja mesmo disparar as tarefas para os clientes ativos?',
                        'icon' => ['class' => 'fa-solid fa-clock-rotate-left fs-5'],
                    ],
                    [
                        'label' => 'Atualizar Sistemas',
                        'route' => 'systems.update.all.systems',
                        'active_routes' => ['systems.update.all.systems'],
                        'open_modal' => 'update-systems',
                        'icon' => ['class' => 'fa-solid fa-rotate fs-5'],
                    ],
                ],
            ],
        ];
    }
}
