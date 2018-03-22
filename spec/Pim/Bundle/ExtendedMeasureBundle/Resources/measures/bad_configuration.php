<?php
return [
    'measures_config' => [
        'Weight' => [
            'standard' => 'g',
            'units'    => [
                'KILOGRAM'   => [
                    'convert'           => [['mul' => 1000]],
                    'symbol'            => 'kg',
                    'name'              => 'kilogram',
                    'unece_code'        => 'KGM',
                    'alternative_symbols' => ['kilo', 'kilogramme']
                ],
                'WRONGMETER' => [
                    'symbol'            => 'm',
                    'convert'           => [['mul' => 666]],
                    'alternative_symbols' => ['kg']
                ]
            ]
        ]
    ]
];
