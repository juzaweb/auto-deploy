<?php

namespace Juzaweb\AutoDeploy\Models;

use Juzaweb\CMS\Models\Model;
use Juzaweb\CMS\Traits\UseUUIDColumn;

class DeployToken extends Model
{
    use UseUUIDColumn;

    protected $table = 'deploy_deploy_tokens';

    protected $fillable = [
        'params',
    ];

    protected $casts = ['params' => 'array'];
}
