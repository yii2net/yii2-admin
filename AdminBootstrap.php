<?php
namespace yikaikeji\openadm;

use yii\base\BootstrapInterface;
use yii\base\Application;
use yii\helpers\VarDumper;
use yikaikeji\openadm\web\SystemEvent;


class AdminBootstrap implements BootstrapInterface
{
    public function bootstrap($app)
    {
        $array = [
            'cache' => [
                'class' => 'yii\caching\FileCache',
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
        ];
        echo VarDumper::dump($array);exit;

        $app->on(Application::EVENT_BEFORE_REQUEST, function () {
            SystemEvent::beforeRequest();
        });
    }
}