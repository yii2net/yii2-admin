<?php
namespace yikaikeji\openadm\modules\admin\controllers;

use yii\web\Controller;

/**
 * Default controller for the `admin` module
 * @name 后台首页
 */
class DefaultController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }
}
