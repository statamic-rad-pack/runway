<?php

use StatamicRadPack\Runway\Tests\Fixtures\Models\Author;
use StatamicRadPack\Runway\Tests\Fixtures\Models\Post;
use StatamicRadPack\Runway\Tests\Fixtures\Models\User;

return [
    'resources' => [
        Author::class => [
            'name' => 'Authors',
            'listing' => [
                'columns' => [
                    'name',
                ],
                'sort' => [
                    'column' => 'name',
                    'direction' => 'asc',
                ],
            ],
        ],

        Post::class => [
            'name' => 'Posts',
            'listing' => [
                'columns' => [
                    'title',
                ],
                'sort' => [
                    'column' => 'title',
                    'direction' => 'desc',
                ],
            ],
            'route' => '/posts/{{ slug }}',
            'published' => true,
            'revisions' => true,
            'nested_field_prefixes' => [
                'values', 'external_links',
            ],
        ],

        User::class => [
            'name' => 'Users',
        ],
    ],
];
