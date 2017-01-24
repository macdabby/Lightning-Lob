<?php

return [
    'routes' => [
        'static' => [
            'admin/orders/fulfillment/lob' => 'Modules\\Lob\\Pages\\Fulfillment',
        ]
    ],
    'modules' => [
        'checkout' => [
            'fulfillment_handlers' => [
                'lob' => 'Modules\\Lob\\Connector\\Checkout',
            ]
        ]
    ]
];
