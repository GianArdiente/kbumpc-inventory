<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Product Information
    |--------------------------------------------------------------------------
    */

    'name' => 'KBUMPC Inventory Management System',

    'product_id' => 'KBUMPC-IMS',

    'version' => '0.9.0',

    /*
    |--------------------------------------------------------------------------
    | Software Developer
    |--------------------------------------------------------------------------
    */

    'developer' => [
        'name' => 'Jairus Gian S. Ardiente',
    ],

    /*
    |--------------------------------------------------------------------------
    | Licensing
    |--------------------------------------------------------------------------
    |
    | This information identifies the organization for which this
    | installation is licensed.
    |
    */

     'license' => [
        'organization' => env(
            'PRODUCT_LICENSE_ORGANIZATION',
            'Unlicensed'
        ),

        'license_id' => env(
            'PRODUCT_LICENSE_ID',
            null
        ),

        'license_file' => base_path(
            'tools/licensing/issued/KBUMPC-2026-001.license'
        ),

        'public_key' => base_path(
            'tools/licensing/signing-public.pem'
        ),
    ],

];