<?php

return [

    // The default gateway to use
    'default' => 'alipay',

    // Add in each gateway here
    'gateways' => [
        'paypal' => [
            'driver' => 'PayPal_Express',
            'options' => [
                'solutionType' => '',
                'landingPage' => '',
                'headerImageUrl' => ''
            ]
        ],
        'alipay' => [
            'driver' => 'Alipay_Express',
            'options' => [
                'partner' => '',
                'key' => '',
                'sellerEmail' => '',
                'returnUrl' => '',
                'notifyUrl' => ''
            ]
        ],
        'alipayMobile' => [
            'driver' => 'Alipay_MobileExpress',
            'options' => [
                'partner' => '',
                'key' => '',
                'sellerEmail' => '',
                'notifyUrl' => '',
                'privateKey' => storage_path('app/alipay/rsa_private_key.pem'),
                'publicKey' => storage_path('app/alipay/rsa_public_key.pem'),

            ]
        ],

        'unionpay' => [
            'driver' => 'UnionPay_Express',
            'options' => [
                'merId' => '777290058130430',
                'certPath' => storage_path('app') . '/unionpay/certs/acp_test_sign.pfx',
                'certPassword' => '000000',
                'certDir' => storage_path('app') . '/unionpay/certs',
                'returnUrl' => '',
                'notifyUrl' => ''
            ]
        ],
        'wechat' => [
            'driver' => 'WeChat_Express',
            'options' => [
                'appId' => '',
                'appKey' => '',
                'mchId' => ''
            ]
        ],
        'WechatPay' => [
            'driver' => 'WechatPay_App',
            'options' => [
                'appId' => '',
                'apiKey' => '',
                'mchId' => ''
            ]
        ]
    ]

];