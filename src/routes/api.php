<?php
/**
 * JUZAWEB CMS - The Best CMS for Laravel Project
 *
 * @package    juzaweb/juzacms
 * @author     Juzaweb Team <admin@juzaweb.com>
 * @link       https://juzaweb.com
 * @license    GNU General Public License v2.0
 */

use Juzaweb\AutoDeploy\Http\Controllers\DeployController;

Route::post('auto-deploy/webhook/github/{action}', [DeployController::class, 'github']);
