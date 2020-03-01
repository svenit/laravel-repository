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
    | Model
    |--------------------------------------------------------------------------
    |
    | The namespace for the criteria classes.
    |
    */
    'default_model' => 'App', // Example : App\Model

    /*
    |--------------------------------------------------------------------------
    | Cache will be clear when a action bellow was triggered
    |--------------------------------------------------------------------------
    |
    | Config cacheable
    |
    */
    'cache' => [
        'clear' => [
            'created' => true,
            'deleted' => true,
            'updated' => true
        ]
    ],

    /*
    / Something else
    */

    'pagination' => [
        'limit' => 15
    ],

    'parameters' => [
        'search' => 'search', // Define your custom search keyword
        'filter' => 'filter' // Define your custom filter keyword
    ]

];