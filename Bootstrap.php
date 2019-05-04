<?php
namespace openadm\admin;

use Yii;
use yii\base\BootstrapInterface;
use yii\base\Application;
use openadm\admin\web\SystemEvent;


class Bootstrap implements BootstrapInterface
{
    public function bootstrap($app)
    {
        Yii::setAlias("@".__NAMESPACE__, __DIR__);
        if(PHP_SAPI !== 'cli'){
            $app->on(Application::EVENT_BEFORE_REQUEST, function () {
                SystemEvent::beforeRequest();
            });
        }
    }
}