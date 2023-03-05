<?php

namespace Juzaweb\AutoDeploy\Commands;

use Illuminate\Console\Command;
use Juzaweb\AutoDeploy\Models\DeployToken;

class MakeDeployToken extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'deploy:make-token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make token for Auto deploy.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $token = DeployToken::create();

        $this->info("Webhook: ". url(route('webhook.auto-deploy.handle', ['__action__', $token->uuid])));
    }
}
