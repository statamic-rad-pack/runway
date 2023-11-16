<?php

use DoubleThreeDigital\Runway\Tests\Fixtures\Models\Author;
use DoubleThreeDigital\Runway\Tests\Fixtures\Models\Post;

return [
    'resources' => [
        Post::class => [
            'name' => 'Posts',
            'blueprint' => [
                'sections' => [
                    'main' => [
                        'fields' => [
                            [
                                'handle' => 'title',
                                'field' => [
                                    'type' => 'text',
                                    'listable' => true,
                                ],
                            ],
                            [
                                'handle' => 'slug',
                                'field' => [
                                    'type' => 'slug',
                                ],
                            ],
                            [
                                'handle' => 'body',
                                'field' => [
                                    'type' => 'textarea',
                                ],
                            ],
                            [
                                'handle' => 'values->alt_title',
                                'field' => [
                                    'type' => 'text',
                                ],
                            ],
                            [
                                'handle' => 'values->alt_body',
                                'field' => [
                                    'type' => 'markdown',
                                ],
                            ],
                            [
                                'handle' => 'external_links->links',
                                'field' => [
                                    'type' => 'grid',
                                    'fields' => [
                                        [
                                            'handle' => 'label',
                                            'field' => ['type' => 'text'],
                                        ],
                                        [
                                            'handle' => 'url',
                                            'field' => ['type' => 'text'],
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'handle' => 'excerpt',
                                'field' => [
                                    'type' => 'textarea',
                                    'read_only' => true,
                                ],
                            ],
                            [
                                'handle' => 'author_id',
                                'field' => [
                                    'type' => 'belongs_to',
                                    'resource' => 'author',
                                    'max_items' => 1,
                                    'mode' => 'default',
                                ],
                            ],
                            [
                                'handle' => 'age',
                                'field' => [
                                    'type' => 'integer',
                                    'visibility' => 'computed',
                                ],
                            ],
                            [
                                'handle' => 'start_date',
                                'field' => [
                                    'type' => 'date',
                                    'time_enabled' => true,
                                    'validate' => [
                                        'before:end_date',
                                    ],
                                ],
                            ],
                            [
                                'handle' => 'end_date',
                                'field' => [
                                    'type' => 'date',
                                    'time_enabled' => true,
                                ],
                            ],
                            [
                                'handle' => 'dont_save',
                                'field' => [
                                    'type' => 'text',
                                    'save' => false,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
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
            'blueprint' => [
                'sections' => [
                    'main' => [
                        'fields' => [
                            [
                                'handle' => 'name',
                                'field' => [
                                    'type' => 'text',
                                    'listable' => true,
                                ],
                            ],
                            // [
                            //     'handle' => 'posts',
                            //     'field' => [
                            //         'type' => 'has_many',
                            //         'resource' => 'post',
                            //         'mode' => 'select',
                            //     ],
                            // ],
                        ],
                    ],
                ],
            ],
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
