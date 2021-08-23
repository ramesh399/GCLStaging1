<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => '5o8ihQ8ZTNneV60NfI0C1Rt0_5m7TAKS',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]
        ],		
		'userdata' => [
            'class' => 'app\components\UserDataComponent',            
        ],	
		'userrole' => [
            'class' => 'app\components\UserRoleComponent',            
        ],
		'globalfuns' => [
            'class' => 'app\components\GlobalComponent',            
        ],
        'formatter' => [
            'dateFormat' => 'd-M-Y',
            'datetimeFormat' => 'd-M-Y H:i:s',
            'timeFormat' => 'H:i:s',
       ],
		'jwt' => [
		  'class' => \sizeg\jwt\Jwt::class,
          'key'   => 'secret',
          'jwtValidationData' => \app\components\JwtValidationData::class,
		],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
            'enableSession' =>false
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'mail.gcl-intl.com',
                'username' => 'noreply@gcl-intl.com',
                'password' => '+eZ5$9=D#IF0',
                'port' => '465',
                'encryption' => 'ssl',
            ],			
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
       
        'urlManager' => [
            'enablePrettyUrl' => true,
            //'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
                ['class' => 'yii\rest\UrlRule', 'controller' => 'country'],
            ],
        ],
        'qr' => [
			'class' => '\Da\QrCode\Component\QrCodeComponent',
			// ... you can configure more properties of the component here
		],
    ],
    'modules' => [
        'master' => [
            'class' => 'app\modules\master\Module',
        ],
        'application' => [
            'class' => 'app\modules\application\Module',
        ],
        'offer' => [
            'class' => 'app\modules\offer\Module',
        ],
		'invoice' => [
            'class' => 'app\modules\invoice\Module',
        ],
        'audit' => [
            'class' => 'app\modules\audit\Module',
        ],
        'certificate' => [
            'class' => 'app\modules\certificate\Module',
        ],
        'library' => [
            'class' => 'app\modules\library\Module',
        ],
		'transfercertificate' => [
            'class' => 'app\modules\transfercertificate\Module',
        ],
        'changescope' => [
            'class' => 'app\modules\changescope\Module',
        ],
		'unannouncedaudit' => [
            'class' => 'app\modules\unannouncedaudit\Module',
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['127.0.0.1', '::1','90.0.1.66'],
    ];
}

return $config;
