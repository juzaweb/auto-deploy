<?php

namespace Juzaweb\AutoDeploy\Commands;

use Illuminate\Console\Command;
use Juzaweb\AutoDeploy\Models\DeployToken;
use Symfony\Component\Console\Input\InputOption;

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
        $params = collect(explode(',', $this->option('params')))
            ->map(
                function ($item) {
                    $item = explode('=', trim($item));
                    if (isset($item[1])) {
                        return [$item[0] => $item[1]];
                    }
                    return $item[0];
                }
            )
            ->filter(fn($item) => !empty($item))
            ->toArray();

        $token = DeployToken::create(['params' => $params]);

        $this->info("Webhook: ". url(route('webhook.auto-deploy.handle', ['__action__', $token->uuid])));
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
                null,
            ],
        ];
    }
}
