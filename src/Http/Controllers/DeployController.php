<?php
/**
 * JUZAWEB CMS - The Best CMS for Laravel Project
 *
 * @package    juzaweb/juzacms
 * @author     Juzaweb Team <admin@juzaweb.com>
 * @link       https://juzaweb.com
 * @license    GNU General Public License v2.0
 */

namespace Juzaweb\AutoDeploy\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Juzaweb\AutoDeploy\Commands\AutoDeployCommand;
use Juzaweb\CMS\Http\Controllers\ApiController;
use Symfony\Component\Console\Output\BufferedOutput;

class DeployController extends ApiController
{
    public function github(Request $request, string $action): \Illuminate\Http\Response
    {
        if (!config('deploy.enable')) {
            return response("Deploy is not enabled.", 403);
        }
        
        $outputLog = new BufferedOutput();
        $githubPayload = $request->getContent();
        $githubHash = $request->header('X-Hub-Signature');
        $localToken = config('deploy.github.secret');
        $localHash = 'sha1='.hash_hmac('sha1', $githubPayload, $localToken);
        
        if (!hash_equals($githubHash, $localHash)) {
            abort(403);
        }
        
        Log::info("Deploy: ". json_encode($request->all()));
        
        switch (config('deploy.method')) {
            case 'cron':
                $schedule = get_config('deploy_schedules', []);
                $schedule[$action] = date('Y-m-d H:i:s');
                set_config('deploy_schedules', $schedule);
                return response("Deploy command is running...");
            case 'queue':
                Artisan::queue(AutoDeployCommand::class, ['action' => $action]);
                return response("Deploy command is running...");
            default:
                Artisan::call(AutoDeployCommand::class, ['action' => $action], $outputLog);
                return response($outputLog->fetch());
        }
    }
}
