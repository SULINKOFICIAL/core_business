/**
 * Função responsável por gerar as tabelas do website.
 * Obs.: Tabelas server site são configuradas na própria página.
 * 
 * Metronic:  https://preview.keenthemes.com/html/metronic/docs/forms/inputmask
 * Website:   https://datatables.net/
 * 
 * Esta função também possui um extensão para o input da paginação.
 * GitHub: https://datatables.net/plug-ins/pagination/input
 * 
 * ATENÇÃO: Baixamos o arquivo da extenção e realizamos alguns ajustes
 * de tradução e usabilidade que a versão nativa não possuia.
 * Local: "/public/assets/js/datatable-input.js"
 */
function loadTables(seletor = '.datatables', items = 25, order = undefined) {
    const table = $(seletor);
    const dataTableOptions = {
        pageLength: items,
        order: order,
        aaSorting: [],
        language: {
            search: 'Pesquisar:',
            lengthMenu: 'Mostrando _MENU_ registros por página',
            zeroRecords: 'Ops, não encontramos nenhum resultado :(',
            info: 'Mostrando _START_ até _END_ de _TOTAL_ registros',
            infoEmpty: 'Nenhum registro disponível',
            infoFiltered: '(Filtrando _MAX_ registros)',
            processing: 'Filtrando dados',
            paginate: {
                previous: 'Anterior',
                next: 'Próximo',
                first: '<i class="fa-solid fa-angles-left text-gray-300 text-hover-primary cursor-pointer"></i>',
                last: '<i class="fa-solid fa-angles-right text-gray-300 text-hover-primary cursor-pointer"></i>',
            },
        },
        dom:
            "<'row'" +
                "<'col-sm-6 d-flex align-items-center justify-content-start'l>" +
                "<'col-sm-6 d-flex align-items-center justify-content-end'f>" +
            '>' +
            "<'table-responsive'tr>" +
            "<'row'" +
            "<'col-sm-12 col-md-5 d-flex align-items-center justify-content-center justify-content-md-start'i>" +
            "<'col-sm-12 col-md-7 d-flex align-items-center justify-content-center justify-content-md-end'p>" +
            '>',
    };
    table.DataTable(dataTableOptions);
}


/**
 * Função responsável por configurar as mascaras dentro dos inputs
 * Metronic: https://preview.keenthemes.com/html/metronic/docs/forms/inputmask
 * GitHub:   https://github.com/RobinHerbots/Inputmask
 */
function generateMasks(){
    Inputmask(["(99) 9999-9999", "(99) 9 9999-9999"], {
        "clearIncomplete": true,
    }).mask(".input-phone");

    Inputmask(["9999 9999 9999 9999"], {
        "placeholder": "",
        "clearIncomplete": true,
    }).mask(".input-card");

    Inputmask(["9999"], {
        "placeholder": "",
        "numericInput": true,
    }).mask(".input-year");

    Inputmask(["99/99"], {
    }).mask(".input-month-year");

    Inputmask(["999"], {
        "placeholder": "",
        "numericInput": true,
    }).mask(".input-ccv");

    Inputmask(["99.999.999/9999-99"], {
        "clearIncomplete": true,
    }).mask(".input-cnpj");

    Inputmask(["999.999.999-99"], {
        "clearIncomplete": true,
    }).mask(".input-cpf");

    Inputmask(["99999-999"], {
        "clearIncomplete": true,
    }).mask(".input-cep");

    Inputmask(["99/99/9999"], {
        "clearIncomplete": true,
    }).mask(".input-date");

    Inputmask(["99/99/9999 99:99:99"], {
        "clearIncomplete": true,
    }).mask(".input-date-time");

    Inputmask(["99:99:99"], {
        "clearIncomplete": true,
    }).mask(".input-duration");

    Inputmask(["99:99"], {
        "clearIncomplete": true,
    }).mask(".input-time");

    Inputmask(["9999.99.99"], {
        "clearIncomplete": true,
    }).mask(".input-ncm");

    Inputmask(["9.99", "99.99"], {
        "numericInput": true,
        "clearIncomplete": true,
    }).mask(".input-comission");

    Inputmask(["9.999kg", "99.999kg", "999.999kg"], {
        "numericInput": true,
        "clearIncomplete": true,
    }).mask(".input-weight");

    Inputmask(["9.99cm", "99.99cm", "999.99cm"], {
        "numericInput": true,
        "clearIncomplete": true,
    }).mask(".input-cm");

    Inputmask(["R$ 9", "R$ 99", "R$ 9,99", "R$ 99,99", "R$ 999,99", "R$ 9.999,99", "R$ 99.999,99", "R$ 999.999,99", "R$ 9.999.999,99"], {
        "numericInput": true,
        "clearIncomplete": true,
    }).mask(".input-money");

    Inputmask(["$ 9", "$ 99", "$ 9.99", "$ 99.99", "$ 999.99", "$ 9,999.99", "$ 99,999.99", "$ 999,999.99", "$ 9,999,999.99"], {
        "numericInput": true,
        "clearIncomplete": true,
    }).mask(".input-money-usd");

    Inputmask(["99"]).mask(".input-decimal");

    Inputmask(["9.99m", "99.99m", "999.99m", "9999.99m", "99999.99m"], {
        "clearIncomplete": true,
    }).mask(".input-metter");
}

$(document).ready(function(){
    loadTables();
    generateMasks();
})