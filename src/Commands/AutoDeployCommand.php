<?php

namespace Juzaweb\AutoDeploy\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Noodlehaus\Config;

class AutoDeployCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'deploy:run {action}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run deploy.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $config = Config::load(base_path('.deploy.yml'));
        $action = $this->argument('action');
        
        foreach ($config->get("{$action}.commands", []) as $command) {
            $this->info("Running '{$command}'");
            $cmd = explode(' ', trim($command));
            if ($cmd[0] == 'php' && $cmd[1] == 'artisan') {
                $this->call($cmd[2]);
            } else {
                $process = new Process($cmd);
    
                $process->run(
                    function ($type, $buffer) {
                        $this->info($buffer);
                    }
                );
            }
        }
    }
}
