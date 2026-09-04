<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Resources
    |--------------------------------------------------------------------------
    |
    | Configure the resources (models) you'd like to be available in Runway.
    |
    */

    'resources' => [
        // \App\Models\Order::class => [
        //     'name' => 'Orders',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Disable Migrations?
    |--------------------------------------------------------------------------
    |
    | Runway publishes migrations to power various optional features. If
    | you're not using those features, you may disable their migrations here.
    |
    */

    'disable_migrations' => false,

    /*
    |--------------------------------------------------------------------------
    | Tables
    |--------------------------------------------------------------------------
    |
    | Runway stores model URIs (for front-end routing) and the order of models
    | (for reorderable resources) in its own tables. You may customize the
    | table names here, if needed.
    |
    */

    'tables' => [
        'uris' => 'runway_uris',
        'structures' => 'runway_structures',
    ],

    /*
    |--------------------------------------------------------------------------
    | Static Warming
    |--------------------------------------------------------------------------
    |
    | Should Runway's URIs be warmed when running `php please static:warm`?
    |
    */

    'static_warming' => true,

];
