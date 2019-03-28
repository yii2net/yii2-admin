<?php
namespace yikaikeji\openadm;

use Yii;
use yii\base\BootstrapInterface;
use yii\base\Application;
use yikaikeji\openadm\web\SystemEvent;


class AdminBootstrap implements BootstrapInterface
{
    public function bootstrap($app)
    {
        Yii::setAlias('@openadm', '@vendor/yikaikeji/yii2-openadm');
        if(PHP_SAPI !== 'cli'){
            $app->on(Application::EVENT_BEFORE_REQUEST, function () {
                SystemEvent::beforeRequest();
            });
        }
    }
}