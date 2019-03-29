<?php
namespace yikaikeji\openadm\extensions\admin\controllers;

use yii;
use yii\helpers\Json;
use yikaikeji\openadm\controllers\Controller;
use yikaikeji\openadm\web\SystemEvent;

/**
 * @name 控制面板
 * @package yikaikeji\openadm\extensions\admin\controllers
 */
class DashboardController extends Controller
{
    public $defaultAction = 'index';

    /**
     * @name 主页
     */
    public function actionMain()
    {
        return $this->render('main');
    }


    public function actionIndex()
    {
        $menus = SystemEvent::GetAdminMenu();
        if(Yii::$app->request->isAjax){
            return '<script>OA_Menus='.Json::encode($menus).'</script>';
        }
        return $this->renderPartial('index',['menus'=>$menus]);
    }
}
