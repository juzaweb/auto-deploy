<?php

namespace Juzaweb\AutoDeploy\Commands;

use Illuminate\Console\Command;
use Juzaweb\AutoDeploy\Contrasts\AutoDeploy;
use Symfony\Component\Console\Input\InputArgument;

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

        $this->autoDeploy->run($action);

        return self::SUCCESS;
    }

    protected function getArguments(): array
    {
        return [
            ['action', InputArgument::REQUIRED, 'The action to run.'],
            ['token', InputArgument::REQUIRED, 'The token to run.'],
        ];
    }
}
