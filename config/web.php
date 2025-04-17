<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';
date_default_timezone_set('Asia/Jakarta');

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'modules' => [
        'gridview' => [
            'class' => '\kartik\grid\Module'
        ],
        'user' => [
            'class' => 'dektrium\user\Module',
            'enableRegistration' => false, 
            'enableConfirmation' => false,
            'enableUnconfirmedLogin' => true,
        ],
        'admin' => [
            'class' => 'mdm\admin\Module',      
            'layout' => 'left-menu', // 'left-menu', 'top-menu', atau 'main'
        ],
    ],
    'components' => [
        'request' => [
            'cookieValidationKey' => 'exU8Agz3brmfW2g0m_EQ4UcSe4063k4x',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
            'cachePath' => '@app/runtime/cache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
            'loginUrl' => ['site/login'],
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            'useFileTransport' => true,
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
        'view' => [
            'theme' => [
                'pathMap' => [
                    '@vendor/dmstr/yii2-adminlte-asset/example-views/yiisoft/yii2-app/views/layouts' => '@app/views/layouts',
                ],
            ],
        ],
        'assetManager' => [
            'bundles' => [
                'kartik\form\ActiveFormAsset' => [
                    'bsDependencyEnabled' => false
                ],
            ],
        ],
        'formatter' => [
            'locale' => 'id-ID', 
            'defaultTimeZone' => 'Asia/Jakarta',
            'timeZone' => 'Asia/Jakarta',
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                // '<controller:\w+>/<id:\d+>' => '<controller>/view',
                // '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
                // '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['api'], // Sesuai nama ApiController
                    'pluralize' => false, // biar URL tetap pakai "api/user" bukan "apis/user"
                    'extraPatterns' => [
                        'GET user' => 'user', // endpoint: GET /api/user?id=1
                        'POST login' => 'login',
                    ],
                ]
            ],
        ],      
    ],
    'as access' => [
        'class' => 'mdm\admin\components\AccessControl',
        'allowActions' => [
            'site/login',
            'site/logout',
            'site/error',
            'admin/*',
            'gii/*',
            'api/user',
            'api/login',
        ],
    ],
    'params' => $params,
    'name' => 'Backup Log',
];

if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
