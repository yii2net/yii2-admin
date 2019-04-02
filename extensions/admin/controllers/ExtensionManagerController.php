<?php
namespace yikaikeji\openadm\extensions\admin\controllers;

use Yii;
use yikaikeji\openadm\controllers\Controller;
use yikaikeji\openadm\web\SystemConfig;
use yikaikeji\openadm\extensions\admin\models\ExtensionManager;
use Yikaikeji\Extension\Loader as ExtensionLoader;
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
		$ExtensionLoader = new ExtensionLoader(['installedPath'=>Yii::getAlias($this->module->extensionDir)]);
		$result = $ExtensionLoader->localList('',$tab=='all' ? '' : $tab,'',$page,$pageSize);
		return $this->render("local",['tab'=>$tab,'result'=>$result]);
	}
	
	public function actionShop()
	{
		$url = $this->module_center_url.'/extensions/token/'.Yii::app()->params['token'];
		$this->render("shop",array('url'=>$url));
	}

	//iframe for long request
	public function actionAjax()
	{
        if(Yii::$app->request->isPost){
            $action   = Yii::$app->request->post('action','');
            $extensionid = Yii::$app->request->post('extensionid','');
            if($extensionid && $action && in_array($action,['setup','unsetup','delete'])){
                ExtensionManager::setShowMsg(1);
                $result = ExtensionManager::$action($extensionid);
                ExtensionManager::setShowMsg(0);
                //update  systemconfig
                SystemConfig::cache_flush();
            }
        }
	}


}