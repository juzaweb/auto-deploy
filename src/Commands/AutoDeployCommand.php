<?php

namespace Juzaweb\AutoDeploy\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Process\Process;
use Noodlehaus\Config;

class AutoDeployCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'deploy:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run command deploy of action.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $action = $this->argument('action');
        if (config('deploy.method') == 'cron') {
            $schedule = get_config('deploy_schedules', []);
            if (empty($schedule)) {
                return self::SUCCESS;
            }
    
            $action = array_key_first($schedule);
            unset($schedule[$action]);
            set_config('deploy_schedules', $schedule);
            
            $this->runAction($action);
            return self::SUCCESS;
        }
        
        $this->runAction($action);
        
        return self::SUCCESS;
    }
    
    protected function runAction(string $action): int
    {
        $config = Config::load(base_path('.deploy.yml'));
        $commands = $config->get("{$action}.commands", []);
        if (empty($commands)) {
            $this->error("Action not found.");
            return self::SUCCESS;
        }
    
        foreach ($commands as $command) {
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
    
        Log::info("Deploy success {$action}");
    
        return self::SUCCESS;
    }
    
    protected function getArguments(): array
    {
        return [
            ['action', InputArgument::OPTIONAL, 'The action to run.'],
        ];
    }
}
