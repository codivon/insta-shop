<?php

/*
|--------------------------------------------------------------------------
| InstaShop core metadata
|--------------------------------------------------------------------------
|
| App-wide constants that are NOT user-editable settings: the current
| release version (shown in the developer panel and compared against the
| GitHub release feed by the built-in updater) and the source repository.
|
*/

return [

    // Current application version — bump on every release.
    'version' => '0.8.0',

    // GitHub repo (owner/name) used by the update checker.
    'update_repo' => 'codivon/insta-shop',

    // How long (minutes) the GitHub update check result is cached.
    'update_cache_minutes' => 60,

    // A cron job is considered "active" while this many seconds have not
    // passed since the last scheduler heartbeat (schedule:run every minute).
    'cron_heartbeat_seconds' => 130,
];
