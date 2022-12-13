<?php

namespace Juzaweb\AutoDeploy\Providers;

use Juzaweb\AutoDeploy\Commands\AutoDeployCommand;
use Juzaweb\CMS\Support\ServiceProvider;

class AutoDeployServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->commands([AutoDeployCommand::class]);
    }
    
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ .'/../../config/deploy.php', 'deploy');
    }
}
