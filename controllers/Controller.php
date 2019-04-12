<?php
namespace yikaikeji\openadm\controllers;

use yii\helpers\Url;
use yii\web\Controller as BaseController;


class Controller extends BaseController
{
    public $layout = false;

    public function init(){
        parent::init();
        if(preg_match('/iframe/i',Url::to())){
            $this->layout = '/main';
        }
    }

}