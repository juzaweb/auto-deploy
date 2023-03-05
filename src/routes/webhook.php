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

Route::match(
    ['GET', 'POST'],
    'auto-deploy/{action}/{token}',
    [DeployController::class, 'handle']
)->name('auto-deploy.handle');
