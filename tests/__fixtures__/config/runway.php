<?php

use StatamicRadPack\Runway\Tests\Fixtures\Models\Author;
use StatamicRadPack\Runway\Tests\Fixtures\Models\Post;

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
        ],

        Author::class => [
            'name' => 'Author',
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
    ],
];
