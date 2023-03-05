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
use Illuminate\Http\Response;
use Juzaweb\AutoDeploy\Contrasts\AutoDeploy;
use Juzaweb\CMS\Http\Controllers\ApiController;

class DeployController extends ApiController
{
    public function __construct(protected AutoDeploy $autoDeploy)
    {
    }

    public function handle(Request $request, string $module, string $action, string $token): Response
    {
        if (!config('deploy.enable')) {
            return response("Deploy is not enabled.", 403);
        }

        return $this->autoDeploy->webhook($request, $action, $token);
    }
}
