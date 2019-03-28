<?php
namespace yikaikeji\openadm\modules\admin\controllers;

use Yii;
use yikaikeji\openadm\controllers\Controller;
use yikaikeji\openadm\web\SystemConfig;
use yikaikeji\openadm\modules\admin\models\ModuleManager;

class ModuleManagerController extends Controller
{

    public $defaultAction = 'local';

    private $module_center_url = "http://api.openadm.com";

	//module list
	public function actionIndex()
	{
		$this->redirect("local");
	}
	
	public function actionLocal($tab = "all",$page=1)
	{
		$tab = in_array($tab,array('all','setuped','new')) ? $tab : 'all';
		//获取插件
		$pageSize = 20;
		$result = ModuleManager::GetModules($tab,$page,$pageSize);
		return $this->render("local",['tab'=>$tab,'result'=>$result]);
	}
	
	public function actionShop()
	{
		$url = $this->module_center_url.'/modules/token/'.Yii::app()->params['token'];
		$this->render("shop",array('url'=>$url));
	}

	//iframe for long request
	public function actionAjax()
	{
        if(Yii::$app->request->isPost){
            $action   = Yii::$app->request->post('action','');
            $moduleid = Yii::$app->request->post('moduleid','');
            if($moduleid && $action && in_array($action,['setup','unsetup','delete'])){
                ModuleManager::setShowMsg(1);
                $result = ModuleManager::$action($moduleid);
                ModuleManager::setShowMsg(0);
                //update  systemconfig
                SystemConfig::cache_flush();
            }
        }
	}


}