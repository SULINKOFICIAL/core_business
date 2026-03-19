<?php

namespace App\Http\Controllers;

use App\Models\ErrorMiCore;
use Illuminate\Http\Request;

class ErrorMiCoreController extends Controller
{
    protected $request;
    private $repository;

    /**
     * Prepara o controller com a requisição atual e o repositório de erros.
     * Isso mantém o mesmo padrão de injeção usado nos demais controllers do sistema.
     */
    public function __construct(Request $request, ErrorMiCore $content)
    {
        $this->request = $request;
        $this->repository = $content;
    }

    /**
     * Exibe a tela com os erros enviados pelos clientes para a central.
     * A listagem continua sendo carregada via ajax para seguir o padrão atual.
     */
    public function index()
    {
        // Retorna a página
        return view('pages.errors.index');
    }

    /**
     * Exibe a tela exclusiva do laravel.log para análise dos erros da aplicação.
     * O conteúdo é carregado já formatado para leitura, cópia e arquivamento.
     */
    public function application()
    {
        // Obtém o conteúdo atual do arquivo de log da aplicação.
        $logFileHtml = $this->getLaravelLogHtml();

        // Retorna a página
        return view('pages.errors.application')->with([
            'logFileHtml' => $logFileHtml,
        ]);
    }

    /**
     * Retorna a listagem parcial de erros registrados no banco de dados.
     * Esse método continua sendo usado pelo carregamento ajax da tabela na tela.
     */
    public function show()
    {
        // Obtém dados
        $contents = $this->repository->all();

        // Retorna a página
        return view('pages.errors.show')->with([
            'contents' => $contents,
        ]);
    }

    /**
     * Arquiva o laravel.log atual movendo o arquivo para um backup com data.
     * Em seguida, a tela volta para a listagem com uma mensagem de sucesso.
     */
    public function archiveLaravelLog()
    {
        // Define o caminho do log atual e do arquivo de backup.
        $pathToLog = storage_path('logs/laravel.log');
        $backupPath = storage_path('logs/laravel_' . date('Y-m-d_H-i-s') . '.log');

        // Move o arquivo atual para preservar o histórico antes de limpar.
        if (file_exists($pathToLog)) {
            rename($pathToLog, $backupPath);
        }

        // Redireciona com feedback para o usuário na própria tela.
        return redirect()->route('errors.application')->with('message', 'Log arquivado com sucesso.');
    }

    /**
     * Lê o laravel.log e devolve o conteúdo pronto para ser exibido em tela.
     * Quando o arquivo não existir, retorna uma mensagem amigável no lugar do conteúdo.
     */
    private function getLaravelLogHtml()
    {
        // Define o caminho padrão do arquivo de log da aplicação.
        $pathToLog = storage_path('logs/laravel.log');

        // Valida a existência do arquivo antes de tentar ler o conteúdo.
        if (!file_exists($pathToLog)) {
            return 'O Log está limpo!';
        }

        // Obtém o conteúdo bruto e converte para texto seguro na interface.
        $logContent = file_get_contents($pathToLog);

        return htmlentities($logContent);
    }
}
