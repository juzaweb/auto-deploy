<?php
/**
 * JUZAWEB CMS - The Best CMS for Laravel Project
 *
 * @package    juzaweb/juzacms
 * @author     Juzaweb Team <admin@juzaweb.com>
 * @link       https://juzaweb.com
 * @license    GNU General Public License v2.0
 */

namespace Juzaweb\AutoDeploy\Support;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Juzaweb\AutoDeploy\Commands\AutoDeployCommand;
use Juzaweb\AutoDeploy\Contrasts\AutoDeploy as AutoDeployContrast;
use Juzaweb\AutoDeploy\Exceptions\AutoDeployException;
use Noodlehaus\Config;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Process\Process;

class AutoDeploy implements AutoDeployContrast
{
    public function run(string $action): bool
    {
        if (config('deploy.method') == 'cron') {
            $schedule = get_config('deploy_schedules', []);
            if (empty($schedule)) {
                return true;
            }

            $action = array_key_first($schedule);
            unset($schedule[$action]);
            set_config('deploy_schedules', $schedule);

            $this->runAction($action);

            return true;
        }

        $this->runAction($action);

        return true;
    }

    public function webhook(Request $request, string $action, string $token, array $params = []): Response
    {
        Log::info("Auto Deploy Webhook: ". json_encode($request->all()));

        if (config('deploy.github.verify')) {
            $githubPayload = $request->getContent();
            $githubHash = $request->header('X-Hub-Signature');
            $localToken = config('deploy.github.secret');
            $localHash = 'sha1='.hash_hmac('sha1', $githubPayload, $localToken);

            if (!hash_equals($githubHash, $localHash)) {
                throw new AutoDeployException("Signature invalid");
            }
        }

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
                $outputLog = new BufferedOutput();
                Artisan::call(AutoDeployCommand::class, ['action' => $action], $outputLog);
                return response($outputLog->fetch());
        }
    }

    protected function runAction(string $action): int
    {
        $config = Config::load(base_path('.deploy.yml'));
        $commands = $config->get("{$action}.commands", []);

        throw_unless($commands, new AutoDeployException("Action commands not found."));

        foreach ($commands as $command) {
            echo "Running '{$command}' \n";
            $cmd = explode(' ', trim($command));
            if ($cmd[0] == 'php' && $cmd[1] == 'artisan') {
                Artisan::call($cmd[2]);
            } else {
                $process = new Process($cmd);

                $process->run(
                    function ($type, $buffer) {
                        echo $buffer . "\n";
                    }
                );
            }
        }

        Log::info("Run success action {$action}");

        return true;
    }
}
