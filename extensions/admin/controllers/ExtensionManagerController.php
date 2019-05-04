<?php
namespace openadm\admin\extensions\admin\controllers;

use Yii;
use openadm\admin\controllers\Controller;
use openadm\admin\web\SystemConfig;
use openadm\admin\extensions\admin\models\ExtensionManager;
use openadm\admin\web\Util;

class ExtensionManagerController extends Controller
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
		$tab = in_array($tab,array('all','setuped','downloaded')) ? $tab : 'all';
		//获取插件
		$pageSize = 20;
		$result = ExtensionManager::GetLocalExtensions($tab,$page,$pageSize);
		return $this->render("local",['tab'=>$tab,'result'=>$result]);
	}
	
	public function actionStore($tab = "all",$page=1)
	{
        $tab = in_array($tab,array('all','setuped','downloaded')) ? $tab : 'all';
        //获取插件
        $pageSize = 20;
        $result = ExtensionManager::GetRemoteExtensions('','',$page,$pageSize);
        $categories = [
            'all' => '全部',
            'dev' => '开发'
        ];
        return $this->render("store",['tab'=>$tab,'result'=>$result,'categories'=>$categories]);
	}

	//iframe for long request
	public function actionAjax()
	{
        if(Yii::$app->request->isPost){
            $action   = Yii::$app->request->post('action','');
            $packageName = Yii::$app->request->post('packageName','');
            $packageVersion = Yii::$app->request->post('packageVersion','');
            $locate = Yii::$app->request->post('locate','');
            ob_start();
            ob_end_clean();
            ob_implicit_flush();
            header('X-Accel-Buffering: no');
            if($packageName && $action && in_array($action,['setup','unsetup','delete','clear'])){
                ExtensionManager::setShowMsg(1);
                ExtensionManager::$action($packageName,$packageVersion,$locate);
                ExtensionManager::setShowMsg(0);
                //update  systemconfig
                Util::cache_flush();
            }
        }
        exit;
	}


}