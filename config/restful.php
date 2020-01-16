<?php

return [

    /*
     |--------------------------------------------------------------------------
     | Query Strings
     |--------------------------------------------------------------------------
     |
     | You can change the following to change the query
     | strings added/used when paginating, ordering etc.
     |
     */
    'query_string_keys' => [
        'limit' => 'limit',
        'order' => 'order',
        'page' => 'page'
    ],

    /*
     |--------------------------------------------------------------------------
     | User Agent
     |--------------------------------------------------------------------------
     |
     | What User Agent should we send when querying the API.
     |
     */
    'user_agent' => 'TomHart_API_Database_Driver',

    /*
     |--------------------------------------------------------------------------
     | Headers
     |--------------------------------------------------------------------------
     |
     | When making the curl requests for data, what
     | headers should be included by default
     |
     */
    'headers' => [
        'User-Agent' => config('restful.user_agent', 'TomHart_API_Database_Driver')
    ],

    /*
     |--------------------------------------------------------------------------
     | Default JSON key
     |--------------------------------------------------------------------------
     |
     | If a list of records are contained in a top level key, setting it
     | here will automatically extract it. Because we use Laravel
     | pagination, by default everything is in the 'data' key.
     |
     */
    'default_json_key' => 'data',

    /*
     |--------------------------------------------------------------------------
     | Pagination detection
     |--------------------------------------------------------------------------
     |
     | To detect if a response is paginated we check for certain JSON
     | keys being available. Below are the default keys used by
     | Laravel's builtin pagination system. If you're using
     | something different, change the keys accordingly.
     |
     */
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
    ],


    /*
     |--------------------------------------------------------------------------
     | Logging
     |--------------------------------------------------------------------------
     |
     | If required we can log the calls the Builder makes.
     |
     */
    'logging' => true
];
