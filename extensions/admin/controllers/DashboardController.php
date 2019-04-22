<?php
namespace openadm\admin\extensions\admin\controllers;

use yii;
use yii\helpers\Json;
use openadm\admin\controllers\Controller;
use openadm\admin\web\SystemEvent;
use openadm\admin\web\Util;

/**
 * @name 控制面板
 * @package openadm\admin\extensions\admin\controllers
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

    public function actionClearCache()
    {
        Util::cache_flush();
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = yii\web\Response::FORMAT_JSON;
            return ['code' => 200];
        }
        return true;
    }
}
