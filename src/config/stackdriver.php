<?php

/*
|--------------------------------------------------------------------------
| Google Stackdriver logging
|--------------------------------------------------------------------------
|
| A log channel that can be used as a Monolog logger to log to GCP Stackdriver
| More information: https://github.com/wonderkind/stackdriver-logging
*/

return [
    /*
     * Your Google Cloud Platform project ID
     */
    'project_id' => env('GOOGLE_STACKDRIVER_PROJECT_ID'),

    /*
     * The name of the log to write to
     */
    'log'        => env('GOOGLE_STACKDRIVER_LOG'),
];
