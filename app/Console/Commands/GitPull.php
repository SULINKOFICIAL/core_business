<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GitPull extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:git-pull';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Executa git pull no repositório';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Caminho do repositório
        $repoPath = base_path(); // Use o base_path() se for o repositório do Laravel

        // Executa o comando git pull
        $output = [];
        $result = null;
        exec("cd $repoPath && git pull 2>&1", $output, $result);

        // Exibe a saída no terminal
        $this->info("Comando executado com status: $result");
        foreach ($output as $line) {
            $this->line($line);
        }

        return $result;
    }
}
