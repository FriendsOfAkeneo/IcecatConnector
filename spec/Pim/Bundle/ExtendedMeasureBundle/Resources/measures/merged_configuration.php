<?php
return [
    'measures_config' => [
        'Weight' => [
            'standard' => 'GRAM',
            'units'    => [
                'GRAM'     => [
                    'convert'           => [['mul' => 1]],
                    'symbol'            => 'g',
                    'name'              => 'gram',
                    'unece_code'        => 'GRM',
                    'alternative_symbols' => [],
                ],
                'KILOGRAM'     => [
                    'convert'           => [['mul' => 1000]],
                    'symbol'            => 'kg',
                    'name'              => 'kilo gram',
                    'unece_code'        => 'KGM',
                    'alternative_symbols' => ['kilo'],
                ],
                'BADMETER' => [
                    'convert'           => [['mul' => 666]],
                    'symbol'            => 'm',
                    'alternative_symbols' => [],
                ],
                'DUPLICATE_UNIT' => [
                    'convert'           => [['mul' => 666]],
                    'symbol'            => 'bar',
                    'alternative_symbols' => [],
                ],
            ],
        ],
        'Length' => [
            'standard' => 'METER',
            'units'    => [
                'METER' => [
                    'convert'           => [['mul' => 1]],
                    'symbol'            => 'm',
                    'alternative_symbols' => ['mt'],
                ],
                'KILOMETER' => [
                    'convert'           => [['mul' => 1000]],
                    'symbol'            => 'km',
                    'alternative_symbols' => [],
                ],
                'DUPLICATE_UNIT' => [
                    'convert'           => [['mul' => 666]],
                    'symbol'            => 'foo',
                    'alternative_symbols' => [],
                ],
            ],
        ],
    ]
];
