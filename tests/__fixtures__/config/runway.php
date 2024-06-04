<?php

use StatamicRadPack\Runway\Tests\Fixtures\Models\Author;
use StatamicRadPack\Runway\Tests\Fixtures\Models\Post;
use StatamicRadPack\Runway\Tests\Fixtures\Models\User;

return [
    'resources' => [
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
        ],

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

        User::class => [
            'name' => 'Users',
        ],
    ],
];
