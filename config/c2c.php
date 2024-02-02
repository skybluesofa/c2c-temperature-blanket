<?php

return [
    'latitude' => env('C2C_BLANKET_LATITUDE'),
    'longitude' => env('C2C_BLANKET_LONGITUDE'),
    'timezone' => urlencode(env('APP_TIMEZONE')),
    'design' => env('C2C_BLANKET_DESIGN') ?? 'default',
    'color' => env('C2C_BLANKET_COLORS') ?? 'default',
    'columns' => intval(env('C2C_BLANKET_COLUMNS') ?? '16'),

    /**
     * Add or remove rows and columns
     * Available options are:
     *  precip, rain, snow, high, avg, low, daylight
     */
    'designs' => [
        // 3 x 3 showing average temp
        'default' => [
            ['avg', 'avg', 'avg'],
            ['avg', 'avg', 'avg'],
            ['avg', 'avg', 'avg'],
        ],
        // 3 x 3 showing high, average, and low temps
        'temps' => [
            ['high', 'high', 'avg'],
            ['high', 'avg', 'low'],
            ['avg', 'low', 'low'],
        ],
        // 4 x 4 showing high; average; and low temps, precipitation, and daylight hours
        'all_day' => [
            ['precip', 'high', 'high', 'avg'],
            ['high', 'high', 'avg', 'low'],
            ['high', 'avg', 'low', 'low'],
            ['avg', 'low', 'low', 'daylight'],
        ],
        // 4 x 4 showing high; average; and low temps, precipitation, and daylight hours
        'smiley' => [
            ['high', 'high', 'black', 'black', 'black', 'black', 'black', 'high', 'high'],
            ['high', 'black', 'avg', 'avg', 'avg', 'avg', 'avg', 'black', 'high'],
            ['black', 'avg', 'black', 'avg', 'avg', 'avg', 'black', 'avg', 'black'],
            ['black', 'avg', 'avg', 'avg', 'avg', 'avg', 'avg', 'avg', 'black'],
            ['black', 'avg', 'avg', 'avg', 'precip', 'avg', 'avg', 'avg', 'black'],
            ['black', 'avg', 'black', 'avg', 'avg', 'avg', 'black', 'avg', 'black'],
            ['black', 'avg', 'avg', 'black', 'black', 'black', 'avg', 'avg', 'black'],
            ['low', 'black', 'avg', 'avg', 'avg', 'avg', 'avg', 'black', 'low'],
            ['low', 'low', 'black', 'black', 'black', 'black', 'black', 'low', 'low'],
        ],
    ],

    'colors' => [
        'default' => [
            'temperature' => [
                '-100' => ['blue', 'Blue'],
                '32' => ['green', 'Green'],
                '50' => ['yellow', 'Yellow'],
                '80' => ['red', 'Red'],
            ],
            'daylight' => [
                '0' => ['black', 'Black'],
                '10' => ['navy', 'Navy'],
                '12' => ['blue', 'Blue'],
                '14' => ['turquoise', 'Turquoise'],
            ],
            'precipitation' => [
                'rain' => [
                    '-1' => ['white', 'White'],
                    '0' => ['#ccc', 'Light Gray'],
                    '2' => ['#999', 'Medium Gray'],
                    '4' => ['#444', 'Dark Gray'],
                    '6' => ['#000', 'Black'],
                ],
                'snow' => [
                    '-1' => ['white', 'White'],
                    '0' => ['#ccf', 'Light Blue'],
                    '2' => ['#99c', 'Medium Blue'],
                    '4' => ['#449', 'Dark Blue'],
                    '6' => ['#000', 'Black'],
                ],
            ],
        ],
    ],
];
