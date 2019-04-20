<?php
namespace openadm\admin;

use Yii;
use yii\base\BootstrapInterface;
use yii\base\Application;
use openadm\admin\web\SystemEvent;


class AdminBootstrap implements BootstrapInterface
{
    public function bootstrap($app)
    {
        Yii::setAlias('@openadm', '@vendor/openadm/yii2-admin');
        if(PHP_SAPI !== 'cli'){
            $app->on(Application::EVENT_BEFORE_REQUEST, function () {
                SystemEvent::beforeRequest();
            });
        }
    }
}