<?php

return [

    'relationship' => [
        'config' => [
            'resource' => 'Specify the Runway Resource to be used for this field.',
            'relationship_name' => 'The name of the Eloquent Relationship this field should use. When left empty, Runway will attempt to guess it based on the field handle.',
            'create' => 'Allow creation of new models.',
            'with' => 'Specify any relationship which should be eager loaded when this field is augmented.',
            'title_format' => 'Configure the title format used for displaying results in the fieldtype. You can use Antlers to pull in model data.',
            'search_index' => 'An appropriate search index will be used automatically where possible, but you may define an explicit one.',
            'query_scopes' => 'Select which query fields should be applied when retrieving selectable models.',
        ],
    ],

    'has_many' => [
        'config' => [
            'reorderable' => 'Can the models be reordered?',
            'order_column' => 'Which column should be used to keep track of the order?',
        ],
    ],

];
