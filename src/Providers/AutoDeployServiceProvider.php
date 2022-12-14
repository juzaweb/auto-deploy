<?php

namespace Juzaweb\AutoDeploy\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Juzaweb\AutoDeploy\Commands\AutoDeployCommand;
use Juzaweb\CMS\Support\ServiceProvider;

class AutoDeployServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->commands([AutoDeployCommand::class]);
        
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
    }
}
