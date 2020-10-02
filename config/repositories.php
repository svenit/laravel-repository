<?php

/**
 *@author : Lê Quang Vỹ
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Repository namespace
    |--------------------------------------------------------------------------
    |
    | The namespace for the repository classes.
    |
    */
    'repository_namespace' => 'App\Repositories\Eloquent',
    /*
    |--------------------------------------------------------------------------
    | Criteria namespace
    |--------------------------------------------------------------------------
    |
    | The namespace for the criteria classes.
    |
    */
    'criteria_namespace' => 'App\Repositories\Criteria',

    /*
    |--------------------------------------------------------------------------
    | Formatter namespace
    |--------------------------------------------------------------------------
    |
    | The namespace for the formatter classes.
    |
    */
    'formatter_namespace' => 'App\Repositories\Formatter',

    /*
    |--------------------------------------------------------------------------
    | Model
    |--------------------------------------------------------------------------
    |
    | The namespace for the criteria classes.
    |
    */
    'default_model' => 'App', // Example : App\Models

    /*
    |--------------------------------------------------------------------------
    | Config
    |--------------------------------------------------------------------------
    |
    | Pagination, ...
    |
    */

    'pagination' => [
        'limit' => 15
    ],

];