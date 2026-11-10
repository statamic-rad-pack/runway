<?php

require_once __DIR__.'/../../models.php';

return [
    'resources' => [
        \RunwayRelationshipsSandbox\Author::class => [
            'name' => 'Authors',
            'title_field' => 'name',
            'graphql' => true,
        ],
        \RunwayRelationshipsSandbox\Notebook::class => [
            'name' => 'Notebooks',
            'title_field' => 'title',
            'graphql' => true,
            'nested_field_prefixes' => ['selections'],
        ],
    ],
    'disable_migrations' => true,
];
