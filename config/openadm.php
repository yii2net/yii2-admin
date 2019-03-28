<?php

return [
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'view' => [
            'theme' => [
                'basePath' => '@openadm/themes/adminlte2',
            ],
        ],
        'assetManager' => [
            'class' => 'yii\web\AssetManager',
            'basePath' => '@webroot/static/assets',
            'baseUrl'  => '@web/static/assets',
            'linkAssets'=>true,
            'bundles' => require(__DIR__ . '/' . (YII_ENV_PROD ? 'assets-prod.php' : 'assets-dev.php')),
        ],
        'i18n' => [
            'translations' => [
                '*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'sourceLanguage' => 'en-US',
                    'basePath' => '@app/messages',
                ],
                'user' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'sourceLanguage' => 'en-US',
                    'basePath' => '@app/messages',
                ],
                'noty' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'sourceLanguage' => 'en-US',
                    'basePath' => '@app/messages',
                ],
            ],
        ],
        'errorHandler' => [
            'class' =>'yikaikeji\openadm\web\ErrorHandler',
            'errorAction' => 'site/error',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => false,
            'rules'=>[
            ],
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
            'cache' => 'cache',
            'ruleTable' => '{{%auth_rule}}', // Optional
            'itemTable' => '{{%auth_item}}',  // Optional
            'itemChildTable' => '{{%auth_item_child}}',  // Optional
            'assignmentTable' => '{{%auth_assignment}}',  // Optional
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
        'user' => [
            'class' => 'amnah\yii2\user\components\User',
            'loginUrl' => '/user/admin/login'
        ],
        //文件系统
        'fs' => [
            'class' => 'creocoder\flysystem\LocalFilesystem',
            'path' => '@webroot/uploads',
        ],
        'fileStorage'=>[
            'class' => 'trntv\filekit\Storage',
            'baseUrl' => '@web/uploads',
            'filesystemComponent' => 'fs',
        ],

    ],
    'modules' => [
        'admin' => [
            'class' => 'yikaikeji\openadm\modules\admin\Module',
            'as access' => [
                'class' => yii2mod\rbac\filters\AccessControl::class,
            ],
        ],
        'hello' => [
            'class' => 'yikaikeji\hello\Module'
        ],
        'noty' => [
            'class' => 'lo\modules\noty\Module',
        ],
        'user' => [
            'class' => 'amnah\yii2\user\Module',
            'loginRedirect' => '/',
            'logoutRedirect'=>'/',
            'requireEmail' => true,
            'requireUsername' => true,
            'controllerMap' => [
                'admin' => [
                    'class' => 'yikaikeji\openadm\modules\user\controllers\AdminController',
                    'protected_uids' => [1],
                    'superadmin_uid' => 1,//超级管理员
                ],
                'default' => [
                    'class' => 'yikaikeji\openadm\modules\user\controllers\DefaultController',
                ]
            ],
            'viewPath' => '@openadm/modules/user/views',
        ],
        'rbac' => [
            'class' => 'yii2mod\rbac\Module',
            'as access' => [
                'class' => yii2mod\rbac\filters\AccessControl::class
            ],
            'controllerMap' => [
                'assignment' => [
                    'class' => 'yii2mod\rbac\controllers\AssignmentController',
                ],
                'role' => [
                    'class' => 'yikaikeji\openadm\modules\rbac\controllers\RoleController',
                ],
                'route' => [
                    'class' => 'yikaikeji\openadm\modules\rbac\controllers\RouteController',
                ],
            ],
            'viewPath' => '@openadm/modules/rbac/views',
        ],
    ],
];