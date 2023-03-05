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
use Illuminate\Support\Str;
use Juzaweb\AutoDeploy\Commands\AutoDeployCommand;
use Juzaweb\AutoDeploy\Contrasts\AutoDeploy as AutoDeployContrast;
use Juzaweb\AutoDeploy\Exceptions\AutoDeployException;
use Juzaweb\AutoDeploy\Models\DeployToken;
use Noodlehaus\Config;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Process\Process;

class AutoDeploy implements AutoDeployContrast
{
    public function run(string $action, array $params = []): bool
    {
        $config = Config::load(base_path('.deploy.yml'));
        $commands = $config->get("{$action}.commands", []);

        throw_unless($commands, new AutoDeployException("Action commands not found."));

        $params = collect($params)->mapWithKeys(fn($item, $key) => ["{{$key}}" => $item])->toArray();

        foreach ($commands as $command) {
            $cmd = explode(' ', trim($command));

            foreach ($cmd as $index => $value) {
                $cmd[$index] = Str::replace(array_keys($params), array_values($params), $value);
            }

            echo "Running '". implode(' ', $cmd) ."' \n";

            if ($cmd[0] == 'php' && $cmd[1] == 'artisan') {
                unset($cmd[0]);
                unset($cmd[1]);
                Artisan::call(implode(' ', $cmd));
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

    public function webhook(Request $request, string $action, string $token, array $params = []): Response
    {
        Log::info("Auto Deploy Webhook: ". json_encode($request->all()));

        $this->verifyWebhook($request, $token);

        switch (config('deploy.method')) {
            case 'cron':
                $schedule = get_config('deploy_schedules', []);
                $schedule[] = [
                    'action' => $action,
                    'token' => $token,
                    'params' => $params,
                    'time' => date('Y-m-d H:i:s')
                ];
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

    protected function verifyWebhook(Request $request, string $token)
    {
        if (config('deploy.github.verify')) {
            $githubPayload = $request->getContent();
            $githubHash = $request->header('X-Hub-Signature');
            $localToken = config('deploy.github.secret');
            $localHash = 'sha1='.hash_hmac('sha1', $githubPayload, $localToken);

            if (!hash_equals($githubHash, $localHash)) {
                throw new AutoDeployException("Signature invalid");
            }
        }

        if (!DeployToken::where(['uuid' => $token])->exists()) {
            throw new AutoDeployException("Token invalid");
        }
    }
}
