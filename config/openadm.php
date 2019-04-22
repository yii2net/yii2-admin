<?php

return [
    'params' => [
        'bsDependencyEnabled' => false, // this will not load Bootstrap CSS and JS for all Krajee extensions
        // you need to ensure you load the Bootstrap CSS/JS manually in your view layout before Krajee CSS/JS assets
        //
        // other params settings below
        // 'bsVersion' => '4.x',
        // 'adminEmail' => 'admin@example.com'
    ],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
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
            'bundles' => require(\yii::getAlias('@config') . '/' . (YII_ENV_PROD ? 'assets-prod.php' : 'assets-dev.php')),
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
                ]
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
        'user' => [
            'class' => 'amnah\yii2\user\components\User',
            'loginUrl' => '/user/admin/login',
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
            'class' => 'openadm\admin\extensions\admin\Module',
            'as access' => [
                'class' => yii2mod\rbac\filters\AccessControl::class,
            ]
        ],
        'dynagrid'=> [
            'class'=>'\kartik\dynagrid\Module',
            // other module settings
        ],
        'gridview'=> [
            'class'=>'\kartik\grid\Module',
            // other module settings
        ],
        'user' => [
            'class' => 'amnah\yii2\user\Module',
            'loginRedirect' => '/',
            'logoutRedirect'=>'/',
            'requireEmail' => true,
            'requireUsername' => true,
            'modelClasses'=>[
                'UserSearch' => 'openadm\admin\extensions\user\models\search\UserSearch',
            ],
            'controllerMap' => [
                'admin' => [
                    'class' => 'openadm\admin\extensions\user\controllers\AdminController',
                    'protected_uids' => [1],
                    'superadmin_uid' => 1,//超级管理员
                ],
                'default' => [
                    'class' => 'openadm\admin\extensions\user\controllers\DefaultController',
                ]
            ],
            'viewPath' => '@openadm/extensions/user/views',
        ],
        'rbac' => [
            'class' => 'yii2mod\rbac\Module',
            'as access' => [
                'class' => yii2mod\rbac\filters\AccessControl::class
            ],
            'controllerMap' => [
                'assignment' => [
                    'class' => 'openadm\admin\extensions\rbac\controllers\AssignmentController',
                ],
                'role' => [
                    'class' => 'openadm\admin\extensions\rbac\controllers\RoleController',
                ],
                'route' => [
                    'class' => 'openadm\admin\extensions\rbac\controllers\RouteController',
                ],
            ],
            'viewPath' => '@openadm/extensions/rbac/views',
        ],
    ],
];