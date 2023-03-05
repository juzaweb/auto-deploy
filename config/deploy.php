<?php

return [
    'enable' => env('DEPLOY_ENABLE', false),

    'github' => [
        'secret' => env('DEPLOY_GITHUB_SECRET'),

        'verify' => env('DEPLOY_GITHUB_VERIFY_SECRET', true),
    ],

    /**
     * Run command deploy with method
     * Support: queue, cron
     */
    'method' => env('DEPLOY_METHOD', 'cron'),
];
