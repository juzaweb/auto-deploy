<?php

namespace Juzaweb\AutoDeploy\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Juzaweb\AutoDeploy\Commands\AutoDeployCommand;
use Juzaweb\AutoDeploy\Commands\MakeDeployToken;
use Juzaweb\AutoDeploy\Contrasts\AutoDeploy as AutoDeployContrast;
use Juzaweb\AutoDeploy\Support\AutoDeploy;
use Juzaweb\CMS\Support\ServiceProvider;

class AutoDeployServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->commands([AutoDeployCommand::class, MakeDeployToken::class]);

        $this->app->booted(
            function () {
                $schedule = $this->app->make(Schedule::class);
                if (config('deploy.enable') && config('deploy.method') == 'cron') {
                    $schedule->command(AutoDeployCommand::class)->everyMinute();
                }
            }
        );
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/deploy.php', 'deploy');

        $this->app->singleton(AutoDeployContrast::class, AutoDeploy::class);
    }
}
