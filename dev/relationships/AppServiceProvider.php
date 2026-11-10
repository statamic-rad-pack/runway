<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        config([
            'statamic.editions.pro' => true,
            'statamic.api.resources.collections' => true,
            'statamic.api.resources.taxonomies' => true,
            'statamic.api.resources.globals' => true,
            'statamic.api.resources.assets' => true,
            'statamic.api.resources.users' => true,
            'statamic.api.resources.runway' => ['authors', 'notebooks'],
            'statamic.graphql.resources.collections' => true,
            'statamic.graphql.resources.taxonomies' => true,
            'statamic.graphql.resources.globals' => true,
            'statamic.graphql.resources.assets' => true,
            'statamic.graphql.resources.users' => true,
        ]);
    }
}
