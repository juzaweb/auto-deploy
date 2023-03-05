<?php

namespace Juzaweb\AutoDeploy\Commands;

use Illuminate\Console\Command;
use Juzaweb\AutoDeploy\Contrasts\AutoDeploy;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class AutoDeployCommand extends Command
{
    public function __construct(protected AutoDeploy $autoDeploy)
    {
        parent::__construct();
    }

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
        $params = json_decode($this->option('params'), true);

        if (config('deploy.method') == 'cron') {
            $schedule = get_config('deploy_schedules', []);
            if (empty($schedule)) {
                return true;
            }

            $index = array_key_first($schedule);
            $action = $schedule[$index];
            unset($schedule[$index]);
            //set_config('deploy_schedules', $schedule);

            if (is_array($action)) {
                $this->autoDeploy->run($action['action'], $action['params']);
            } else {
                $this->autoDeploy->run($action);
            }

            return self::SUCCESS;
        }

        $this->autoDeploy->run($action, $params);

        return self::SUCCESS;
    }

    protected function getArguments(): array
    {
        return [
            ['action', InputArgument::OPTIONAL, 'The action to run.'],
            ['token', InputArgument::OPTIONAL, 'The token to run.'],
        ];
    }

    /**
     * The array of command options.
     *
     * @return array
     */
    public function getOptions(): array
    {
        return [
            [
                'params',
                'p',
                InputOption::VALUE_OPTIONAL,
                'Custom params for commands.',
                '[]',
            ],
        ];
    }
}
