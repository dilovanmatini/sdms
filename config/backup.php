<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Backup Disk
    |--------------------------------------------------------------------------
    |
    | Database backup zip files are stored on a private disk. Never use the
    | public disk — backups contain the full database and must be served
    | only through an authorized download route.
    |
    */

    'disk' => env('BACKUP_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Backup Directory
    |--------------------------------------------------------------------------
    |
    | Directory on the backup disk where zip files are stored. Download
    | requests reject paths outside this directory.
    |
    */

    'directory' => 'backups',

    /*
    |--------------------------------------------------------------------------
    | Job Timeout (seconds)
    |--------------------------------------------------------------------------
    |
    | Must stay below the queue connection's retry_after (90 by default)
    | so a running dump is not retried as a duplicate.
    |
    */

    'timeout' => 60,

];
