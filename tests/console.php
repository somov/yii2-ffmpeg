<?php
/**
 * Created by PhpStorm.
 * User: web
 * Date: 15.04.19
 * Time: 18:57
 */

return [
    'class' => \yii\web\Application::class,
    'id' => 'test-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    /*'extensions' => [
        'somov/yii2-settings' => [
            'name' => 'Yii2 settings ',
            'version' => '1.0.0',
            'alias' => ['somov/settings' => __DIR__ . '/../src']
        ]
    ],*/
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            'enableCsrfValidation' => false,
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@runtime/logs/test.log',
                    'categories' => ['application'],
                    'levels' => ['error', 'trace', 'warning', 'info'],
                    'logVars' => [],
                ],

                [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@runtime/logs/trace.log',
                    'categories' => ['somov*'],
                    'levels' => ['trace'],
                    'logVars' => [],
                ],

            ],
        ],
    ],
];