<?php

return [

    /*
     * Default configuration.
     */

    'default' => [

        // Length of time (in minutes) to cache content.
        'minutes' => 60,

        // Where to store imported files (defined in "config/filesystems.php").
        'filesystem' => 's3',

        // Path within filesystem in which to store files.
        'path' => 'contentcache',

        // Domain to use in URL (optional). If not supplied, default will be used.
        'domain' => 'files.example.com',

        // Default schema for URL (optional).
        'schema' => 'https',

        // Style definition for caching alternate versions of images.
        'image_style' => [

            'full_width' => [
                'width' => 1400,
                'height' => 800,
            ],

            'thumbnail' => [
                'width' => 200,
                'height' => 200,
            ],

        ],

    ],

     /*
      * Configuration for custom content filters.
      */

    /*
    'podcasts' => [

        // Length of time (in minutes) to cache content.
        'minutes' => 60 * 3, // 3 hours

        // REST API endpoint for service from which to retrieve content.
        'endpoint' => 'https://podcasts.example.com/api/v1/content/podcasts',
        'query' => ['_format' => 'json'],

        // Perform data filter (value) on given field name (key). So in this
        // case, "id" and "episode" will be cast as integers, and "date_created"
        // and "date_changed" will be cast as Carbon date objects. All other
        // values will be cast as strings.
        'filters' => [
            'id' => 'int',
            'date_created' => 'date',
            'date_changed' => 'date',
            'episode' => 'int',
        ],

        // New fields to be created on cached content object from given field names.
        // E.g. Given an "episode" value of 13 and a "title" of "Lucky 13", the
        // new "slug" attribute (useful for use in routes) will have a value of
        // "13-lucky-13".
        'fields' => [
            'slug' => ['episode', 'title'],
        ],

        // Image derivatives of given style (see default "image_style" above)
        // will be created and added to cached content object.
        'image_derivatives' => [
            'image_url' => [
                'full_width',
                'thumbnail',
            ],
        ],

        // Keys by which the cache should be indexed. I.e. each content
        // object will be cached under each of these index keys.
        'keys' => [
            'id',
            'slug',
            'uuid',
            'episode',
        ],

    ],
    */

];
