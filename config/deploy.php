<?php

return [
    'github' => [
        'secret' => env('DEPLOY_GITHUB_SECRET'),
    ],
    /**
     * Run command deploy with method
     * Support: sync, queue, cron
     */
    'method' => env('DEPLOY_METHOD', 'sync'),
];
