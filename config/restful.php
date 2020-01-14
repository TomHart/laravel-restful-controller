<?php
return [
    'query_string_keys' => [
        'limit' => 'limit',
        'order' => 'order'
    ],

    'user_agent' => 'TomHart_API_Database_Driver',

    'headers' => [
        'User-Agent' => config('restful.user_agent', 'TomHart_API_Database_Driver')
    ],

    'default_json_key' => 'data',

    'pagination_json_keys' => [
        'current_page',
        'data',
        'first_page_url',
        'from',
        'last_page',
        'last_page_url',
        'next_page_url',
        'path',
        'per_page',
        'prev_page_url',
        'to',
        'total'
    ]
];
