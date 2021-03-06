<?php
/**
 * 扩展管理者
 * 扩展id,扩展目录，必须为小写
 * @author xiongchuan <xiongchuan@luxtonenet.com>
 */
namespace openadm\admin\extensions\admin\models;

use yii;
use openadm\admin\web\SystemConfig;
use yii\helpers\FileHelper;
use yii\base\ErrorException;
use yii\helpers\Json;
use yii\base\InvalidArgumentException;
use yii\helpers\Html;
use Openadm\Extension\Loader as ExtensionLoader;
use Openadm\Extension\Implement\Dependency;

class ExtensionManager
{
    const STATUS_SUCCESS = 1;
    const STATUS_ERROR   = 0;
    const ERROR_NEEDED   = 110;
    const ERROR_COMPOSER   = 130;
    const ERROR_NOTATLOCAL = 120;
    const ERROR_MIGRATE = 130;

    const EXTENSION_TYPE_ADMIN = "ADMIN";
    const EXTENSION_TYPE_API   = "API";
    const EXTENSION_TYPE_HOME  = "HOME";

    const EXTENSION_CONFIG_ID_RECORD_KEY = "EXTENSION_CONFIG_IDS";

    static private $_extensions = array();
    static private $_setupedextensions = array();

    const MIGRATE_UP    = 'up';
    const MIGRATE_DOWN  = 'down-extension';//重写数据库清楚操作
    const MIGRATION_DEFAULT_DIRNAME = 'migrations';

    static public $isShowMsg = 0;

    static $ExtensionLoader = null;

    static public function loader()
    {
        if(static::$ExtensionLoader == null){
            $admin = Yii::$app->getModule('admin');
            static::$ExtensionLoader = new ExtensionLoader([
                'packagePathSymLink' => true,
                'logLevel'=>'debug',
                'shellEnv'=>['http_proxy'=>'10.0.8.107:3128'],
                'composerPath'=>$admin->composerPath,
                'vendorPath'=>Yii::getAlias('@vendor'),
                'rootProjectPath'=>Yii::getAlias($admin->rootProjectPath),
                'packageInstalledPath'=>Yii::getAlias($admin->packageInstalledPath),
                'packageScanPath'=>Yii::getAlias($admin->packageScanPath),
                'onSetupCallback'=>function($output){
                    ExtensionManager::showMsg($output,1,'','cmd_box');
                },
                'onUnSetupCallback'=>function($output){
                    ExtensionManager::showMsg($output,1,'','cmd_box');
                },
            ]);
        }
        return static::$ExtensionLoader;
    }

	static public function isWin()
	{
		return strtoupper(substr(PHP_OS,0,3))==='WIN';
	}
	
	static public function getYiiCommand()
	{
		if( self::isWin() )
		{
			return '@root/yii.bat';
		}
		return '@root/yii';
	}

    static public function setShowMsg($value)
    {
        static::$isShowMsg = $value;
    }

    /**
     * 此处的输出方式 要配合 iframe输出
     *
     * 具体使用参看:@app/themes/adminlte2/views/extension-manager/local.php
     *
     * <code>
    window.onmessage = function (msg,boxId) {
        var box = [];
        if(boxId != ''){
            box = $('#'+boxId);
        }
        if(box.length>0){
            box.append(msg);
        }else{
            $('.modal-body').append(msg);
        }
    }
     * </code>
     *
     * @param $msg
     * @param int $rn
     * @param string $type
     * @param string $boxId
     */
    static public function showMsg($msg,$rn=1,$type='info',$boxId='')
    {
        if(!static::$isShowMsg){
            return;
        }
        $color='';
        switch ($type){
            case 'info':
                $color='';
                break;
            case 'success':
                $color = 'green';
                break;
            case 'error':
                $color = 'red';
                break;
            default:
                break;

        }
        if($color){
            $str = "<span style=\"color:{$color}\">$msg</span>".($rn == 1 ? '<br />' : '');
        }else{
            $str = "$msg".($rn == 1 ? '<br />' : '');
        }

        $str = str_replace(["'","\n"],["\"",""],$str);
        echo "<script>parent.onmessage('$str','$boxId');</script>";
        ob_flush();
        flush();
    }

    /**
     * 获取已经安装的扩展
     */
    static public function GetSetupedExtensions()
    {
        if(empty(static::$_setupedextensions)){
            //不允许缓存
            $extensions = SystemConfig::Get('',null,SystemConfig::CONFIG_TYPE_EXTENSION,false);
            foreach ($extensions as $extension){
                try{
                    static::$_setupedextensions[$extension['cfg_name']] = Json::decode($extension['cfg_value'],true);
                }catch (InvalidArgumentException $e){
                    static::$_setupedextensions[$extension['cfg_name']] = $extension['cfg_value'];
                }
            }
        }
        return static::$_setupedextensions;
    }

    static public function ExtensionSetupedCompleted($package)
    {
        $record_key = isset(static::$_extensions[$package->getName()][static::EXTENSION_CONFIG_ID_RECORD_KEY]) ? static::$_extensions[$package->getName()][static::EXTENSION_CONFIG_ID_RECORD_KEY] : [];
        $cfg_value = Json::encode(array_merge($package->toArray(),[static::EXTENSION_CONFIG_ID_RECORD_KEY=>$record_key]));
        $params = array(
            'cfg_value'   => $cfg_value,
            'cfg_comment' => $package->getPrettyName(),
            'cfg_type'    =>SystemConfig::CONFIG_TYPE_EXTENSION
        );
        SystemConfig::Set($package->getName(),$params);
        return true;
    }


    /**
     * 获取单个extension的config
     * @param $packageName string
     * @param $cache 是否缓存
     * @param $checkDependency 是否检查依赖扩展
     */
    static public function GetExtensionConfig($packageName,$cache=true,$checkDependency = true)
    {
        $config = array(
            'setup'  => static::IsSetuped($packageName),
            'package' => false,
            'needed'   => []
        );
        $package = static::loader()->getPackage($packageName);
        if($package){
            $config['package'] = $package;
            if($checkDependency){
                $config['need'] = static::CheckDependency($package->toArray());
            }
        }else{
           return false;
        }
        if($cache){
            static::$_extensions[$packageName] = $config;
        }
        return $config;
    }

    /**
     * extension: <vendor name>/<extension id>
     * 需要获取@extensions/vendorname/extensionid的所有extensions
     * @return array|boolean
     */
    static public function GetExtensionsWithNamespace($extensionDir)
    {
        $extensions = [];
        $vendorDirs = array_slice(scandir($extensionDir,0),2);//过滤掉.|..目录
        foreach ($vendorDirs as $vendorDir){
            if(is_dir($extensionDir.'/'.$vendorDir)){
                $extensionIDs = array_slice(scandir($extensionDir.'/'.$vendorDir,0),2);
                foreach($extensionIDs as $extensionID){
                    if(is_dir($extensionDir.'/'.$vendorDir.'/'.$extensionID)){
                        array_push($extensions,$vendorDir.'/'.$extensionID);
                    }
                }
            }
        }
        return $extensions;
    }

    /**
     *
     * 获取本地的全部扩展
     * 支持分页显示
     * @param $type string  all:全部,setuped:安装的,new:新的
     * @param $page int
     * @param $pageSize int
     * @return array|boolean
     */
    static public function GetLocalExtensions($type="all",$page=1,$pageSize=20)
    {
        $result = static::loader()->localList('',$type=='all' ? '' : $type,'',$page,$pageSize);
        if($result){
            foreach ($result['data'] as $k=>$v){
                if(static::IsSetuped($v['name']) == 1){
                    $result['data'][$k]['status'] = 'setuped';
                }elseif(static::IsSetuped($v['name']) == 0){
                    $result['data'][$k]['status'] = 'downloaded';
                }elseif(static::IsSetuped($v['name']) == 2){
                    $result['data'][$k]['status'] = '';
                }
            }
        }
        return $result;
    }

    static public function GetRemoteExtensions($category="",$query='',$page=1,$pageSize=20)
    {
        //url = https://www.easy-mock.com/mock/5cafe8c28e2ab4156b285246/list
        $url = "https://www.easy-mock.com/mock/5cafe8c28e2ab4156b285246/list";
        $result = json_decode(file_get_contents($url),true);
        return $result['data'];
    }


    /**
     * 获取扩展路径
     */
    static public function GetExtensionPath($packageName, $source = false)
    {
        $prefix = Yii::getAlias('@extensions');
        if($source){
            $prefix = Yii::getAlias(Yii::$app->getModule('admin')->packageScanPath);
        }
        return $prefix.DIRECTORY_SEPARATOR.strtolower($packageName).DIRECTORY_SEPARATOR;
    }

    /**
     * 获取扩展路径
     */
    static public function GetVendorExtensionPath($packageName)
    {
        $prefix = Yii::getAlias('@vendor');
        return $prefix.DIRECTORY_SEPARATOR.strtolower($packageName).DIRECTORY_SEPARATOR;
    }

    /**
     * 删除静态变量数组里面的值
     */
    static public function ExtensionDeleteStaticVar($package)
    {
        if(!empty(static::$_setupedextensions)){
            unset(static::$_setupedextensions[$package->getName()]);
        }
    }

    /**
     * 判断是否已经安装
     */
    static public function IsSetuped($packageName)
    {
        if(empty(static::$_setupedextensions)){
            static::GetSetupedExtensions();
        }
        //除了判断数据库里面有记录，还要判断文件夹是否存在

        $flag = isset(static::$_setupedextensions[$packageName]) && is_dir(static::GetExtensionPath($packageName)) ? 1 :0;
        if(isset(static::$_setupedextensions[$packageName]) &&
            //在src/extension||vendor/都可以，取决于composer.json的type=library||yii2-extension
            (is_dir(static::GetExtensionPath($packageName)) || is_dir(static::GetVendorExtensionPath($packageName)) )
        ){
            $flag = 1;
        }elseif(isset(static::$_setupedextensions[$packageName]) && !is_dir(static::GetExtensionPath($packageName))){
            $flag = 2;
        }elseif(!isset(static::$_setupedextensions[$packageName]) && is_dir(static::GetExtensionPath($packageName))){
            $flag = 0;
        }else{
            $flag = 0;
        }
        return $flag;
    }

    /**
     * 检测依赖关系
     */
    static public function CheckDependency(array $config)
    {
        $unsetuped = [];
        if(is_array($config)){
            $dependency = new Dependency($config);
            $dependencies = $dependency->getDependencies();
            if(!empty($dependencies)){
                static::showMsg('');
                foreach($dependencies as $packageName=>$pacakgeVersion){
                    if($packageName){
                        static::showMsg('|___检测依赖扩展:'.$packageName.":".$pacakgeVersion.' 是否安装...',0);
                        if($dependency->isInstalled($packageName,$pacakgeVersion)){
                            list($v,$installedVersion) = $dependency->getInstalledDependencies($packageName);
                            static::showMsg('已安装 '.$packageName.":".$installedVersion,1,'success');
                        }elseif($dependency->isSkiped($packageName,$pacakgeVersion)){
                            static::showMsg('忽略',1,'info');
                        }else{
                            $unsetuped[] = $packageName.":".$pacakgeVersion;

                            $uninstallPackages = $dependency->getUnInstalledDependencies($packageName);
                            if($uninstallPackages && is_array($uninstallPackages)){
                                static::showMsg('当前安装版本:'.$uninstallPackages[1]." 不符合要求版本公式:".$pacakgeVersion,1,'error');
                            }else{
                                static::showMsg('未安装',1,'error');
                            }
                        }
                    }
                }
            }
        }
        return $unsetuped;
    }

    /**
     * 扩展注入route
     */
    static public function ExtensionInjectRoute($package)
    {
        $route = $package->get("route");
        if($route && is_array($route)){
            $params = [
                'cfg_value'   => Json::encode($route),
                'cfg_comment' => $package->getName(),
                'cfg_pid'     => 0,
                'cfg_order'   => 0,
                'cfg_type'    => 'ROUTE'
            ];
            $cfg_name = strtoupper("extension_".$package->getName()."_route");
            $lastid = SystemConfig::Set($cfg_name,$params);
            static::RecordExtensionConfigId($package->getName(),$lastid);
        }
    }

    /**
     * 把config注入到system_config
     * @param $package
     */
    static public function ExtensionInjectConfig($package)
    {
        $configs = $package->get('config');
        if($configs && is_array($configs)){
            foreach ($configs as $config){
                if(isset($config['cfg_name']) && !empty($config['cfg_name'])){
                    $params = [
                        'cfg_name'  => $config['cfg_name'],
                        'cfg_value' => isset($config['cfg_value']) ? $config['cfg_value'] : '',
                        'cfg_comment' => isset($config['cfg_comment']) ? $config['cfg_comment'] : '',
                    ];
                    $lastid = SystemConfig::Set($config['cfg_name'],$params);
                    static::RecordExtensionConfigId($package->getName(),$lastid);
                }
            }
        }
    }

    /**
     * 安装过程中,记录_pluings[packageName] = ['config_ids'=>[]]
     * @param $packageName packageName
     * @param $configId system_config id
     */
    static public function RecordExtensionConfigId($packageName,$configId)
    {
        if( $configId>0){
            if(!isset(static::$_extensions[$packageName])){
                static::$_extensions[$packageName] = [];
            }
            if(!isset(static::$_extensions[$packageName][static::EXTENSION_CONFIG_ID_RECORD_KEY])){
                static::$_extensions[$packageName][static::EXTENSION_CONFIG_ID_RECORD_KEY] = [];
            }
            array_push(static::$_extensions[$packageName][static::EXTENSION_CONFIG_ID_RECORD_KEY],$configId);
        }
    }

    static public function _ExtensionInjectMenuWithKeys($packageName,$cfg_pid,array $menus)
    {
        foreach ($menus as $key=>$keymenus){
            $menukey = $key;
            if(is_numeric($key)){
                $menukey = 'MENU';//如果是数字，代表菜单是后台的
            }
            static::_ExtensionInjectMenu($packageName,$cfg_pid,$menukey,$keymenus);
        }
    }

    /**
     * 实际注入方法
     * @param $extensionId
     * @param $cfg_name
     * @param array $menus
     */
    static public function _ExtensionInjectMenu($packageName,$cfg_pid,$key,array $menus)
    {
        foreach ($menus as $menu){
            $params = [
                'cfg_value'   => isset($menu['cfg_value']) ? $menu['cfg_value'] : '',
                'cfg_comment' => isset($menu['cfg_comment']) ? $menu['cfg_comment'] : '',
                'cfg_pid'     => $cfg_pid ==0 ? (isset($menu['cfg_pid']) ? $menu['cfg_pid'] : 0) : $cfg_pid,
                'cfg_order'   => isset($menu['cfg_order']) ? $menu['cfg_order'] : 0
            ];
            if(empty($params['cfg_value']) || empty($params['cfg_comment']))continue;
            //检查cfg_value是否为数组,并且有url,icon(可选)
            if(is_array($params['cfg_value']) && isset($params['cfg_value']['url'])){
                $params['cfg_value'] = Json::encode($params['cfg_value']);
            }else{
                continue;//不满条件,就继续foreach
            }
            //写入system_config表
            $lastPuginConfigId = SystemConfig::Set($key,$params);
            static::RecordExtensionConfigId($packageName,$lastPuginConfigId);

            //检查是否有子菜单
            if(isset($menu['items']) && is_array($menu['items'])){
                static::_ExtensionInjectMenu($packageName,$lastPuginConfigId,$key,$menu['items']);
            }
        }

    }

    /**
     * 注入Extension的数据库操作
     * @param $package
     * @param $type up/down up=创建,down=回退
     */
    static public function ExtensionInjectMigration($package,$type)
    {
        if(!$package){
            //extension 目录异常
            static::showMsg("");
            static::showMsg("获取扩展配置失败,请检查扩展是否正常!",1,'error');
            return false;
        }
        if($package->get('migrationDirName')){
            $migrationDirName = $package->get('migrationDirName');
        }else{
            $migrationDirName = static::MIGRATION_DEFAULT_DIRNAME;
        }
        //先检测是否为@开头的
        if(substr($migrationDirName,0,1) == '@'){
            $migrationPath = Yii::getAlias($migrationDirName);
        }else{
            //检查是否需要migrate操作,原则是看是否有migrations目录
            $migrationPath = $package->getInstalledPath().DIRECTORY_SEPARATOR.$migrationDirName;
        }
        if(is_dir($migrationPath)){
            static::showMsg("需要",1,'success');
            static::showMsg("开始执行Migrate操作...");
            $yii = Yii::getAlias(self::getYiiCommand());
            //--interactive=0 非交互式命令行
            $params = "--migrationPath=$migrationPath --interactive=0";
            $action = "migrate/";
            switch ($type){
                case static::MIGRATE_UP:
                    $action .= static::MIGRATE_UP;
                    break;
                case static::MIGRATE_DOWN:
                    $action .= static::MIGRATE_DOWN;
                    break;
                default:
                    break;
            }
            $cmds = [
                $yii,
                $action,
                $params
            ];
            $cmd = join(" ",$cmds);
			if(self::isWin()){
				$cmd = str_replace("\\","\\\\",$cmd); 
			}
            static::showMsg("<p id='cmd_box' style='background-color: #2c763e;color:#f5db88'>",0);
            //执行
            $handler = popen($cmd, 'r');
            static::showMsg("cmd:  ".$cmd."\n",1,'','cmd_box');
            while (!feof($handler)) {
                $output = fgets($handler,1024);
                static::showMsg($output,1,'','cmd_box');
            }
            pclose($handler);
            static::showMsg("</p>",1);
        }else{
            static::showMsg("不需要",1,'success');
        }
        return true;
    }

    /**
     * 扩展菜单注入
     */
    static public function ExtensionInjectMenu($package)
    {
        $menus = $package->get("menus");
        if($menus && is_array($menus)){
            static::_ExtensionInjectMenuWithKeys($package->getName(),0,$menus);
        }
    }

    static public function SetupLocalExtension($extensionName)
    {
        //解析配置
        $config = static::ParseExtensionConfig($extensionName);
        //根据配置执行操作
        foreach ($config as $action => $conf) {
            if(method_exists(self, $action)){
                static::$action($conf);
            }
        }
    }

    /**
     * 解析配置
     */
    static public function ParseExtensionConfig($packageName,$conf=null)
    {
        if(is_array($conf)){
            $config = $conf;
        }else{
            $configfile = static::GetExtensionPath($packageName)."/config.php";
            if(!is_file($configfile))return false;
            $config = require $configfile;
        }
        //extensionidController的extensionid要和extensionid.php里面的id值相等
        if(!isset($config['id']) || $packageName != $config['id']){
            return false;
        }
        if(!isset($config['version']) ||
            !isset($config['name']) ||
            !isset($config['type']) ||
            empty($config['version']) ||
            empty($config['name']) ||
            empty($config['type'])
        ){
            return false;
        }
        return true;
    }

    /**
     * 移除扩展在system_config里面的配置
     * @param $package
     */
    static public function ExtensionDeleteDBConfig($package)
    {
        $packageName = $package->getName();
        $extensions = SystemConfig::Get($packageName,null,SystemConfig::CONFIG_TYPE_EXTENSION);
        if($extensions && is_array($extensions))foreach ($extensions as $extension){
            try{
                $value = Json::decode($extension['cfg_value']);
                $config_ids = isset($value[static::EXTENSION_CONFIG_ID_RECORD_KEY]) ? $value[static::EXTENSION_CONFIG_ID_RECORD_KEY] : [];
                if(is_array($config_ids) && !empty($config_ids))foreach ($config_ids as $id){
                    SystemConfig::Remove($id);
                }
            }catch (InvalidArgumentException $e){

            }
            //删除自己
            SystemConfig::Remove($extension['id']);
        }
        return false;
    }

    static public function RefreshExtensionsConfig($package,$action = 'setup')
    {
        $packageNames = [];
        $extensions = static::GetSetupedExtensions();
        if($action == 'unsetup' && isset($extensions[$package->getName()])){
            unset($extensions[$package->getName()]);
        }elseif($action == 'setup' && !isset($extensions[$package->getName()])){
            $extensions[$package->getName()] = '';
        }
        if($extensions){
            $packageNames = array_keys($extensions);
        }
        if($packageNames){
            $setupedExtensionsConfig = [];
            foreach ($packageNames as $packageName){
                //if($packageName == $package->getName())continue;
                $setupedExtensionsConfig = yii\helpers\ArrayHelper::merge(
                    $setupedExtensionsConfig,
                    static::getPackageApplicationConfigByKeys($packageName)
                );
            }
        }else{
            $setupedExtensionsConfig = [
                'web' => [],
                'console' => []
            ];
        }
        if($setupedExtensionsConfig && is_array($setupedExtensionsConfig)){
            if(is_writable(\Yii::getAlias("@config"))){
                foreach ($setupedExtensionsConfig as $key => $config){
                    $filename = strtolower($key)."-ext.php";
                    $file = \Yii::getAlias("@config").DIRECTORY_SEPARATOR.$filename;
                    if(is_writable($file)){
                        file_put_contents($file,"<?php \n return ".var_export($config,true). ';');
                    }else{
                        static::showMsg("配置路径:".$file." ... 不可写, 跳过!");
                    }
                }
            }else{
                static::showMsg("配置路径:".\Yii::getAlias("@config")." ... 不可写, 跳过!");
            }
        }
    }

    static public function getPackageApplicationConfigByKeys($packageName)
    {
        $config = [];
        $needCombineKeys = ['web','console'];
        foreach ($needCombineKeys as $key){
            $package = static::loader()->getPackage($packageName);
            $config[$key] = $package->get($key) ?: [];
        }
        return $config;
    }

    /**
     * 安装扩展
     * @param $packageName
     * @param $packageVersion
     * @param $locate
     * @return array
     */
    static public function setup($packageName,$packageVersion,$locate)
    {
        static::showMsg("开始安装扩展...");
        $data = array("status"=>static::STATUS_ERROR,'msg'=>'未知错误');
        //检查是否已经安装
        if( 0 == static::IsSetuped($packageName)){
            static::showMsg("执行 composer ...",1);
            static::showMsg("<p id='cmd_box' style='background-color: #2c763e;color:#f5db88'>",0);
            $EventArgs = static::loader()->setup($packageName,$packageVersion,$locate);
            static::showMsg("</p>",0);
            //check result === true
            if(!$EventArgs->result){
                static::showMsg("Composer安装失败，请检查Composer的配置或者扩展({$packageName})的配置！",1,'error');
                $data['status'] = static::STATUS_ERROR;
                $data['error_no'] = static::ERROR_COMPOSER;
                $data['msg']      = "Composer安装失败，请检查Composer的配置或者扩展({$packageName})的配置！";
                return $data;
            }
            static::showMsg("获取扩展配置...",0);
            $configRaw = static::GetExtensionConfig($packageName,false,false);//关闭这里的扩展检测
            $package = $configRaw['package'];
            static::showMsg("完成",1,'success');
            static::showMsg("检测扩展依赖...",0);
            $configRaw['needed'] = static::CheckDependency($configRaw['package']->toArray());//在这里检测扩展依赖
            if(isset($configRaw['needed']) && !empty($configRaw['needed'])){
                static::showMsg("");
                static::showMsg("请先安装缺失的依赖:".join(', ',$configRaw['needed'])."，再安装此扩展！",1,'error');
                $data['status'] = static::STATUS_ERROR;
                $data['error_no'] = static::ERROR_NEEDED;
                $data['msg']      = "请先安装缺失的依赖，再安装此扩展！";
                return $data;
            }
            static::showMsg("检测完成",1,'success');
            if($package){
                static::showMsg("检测是否需要执行Migrate...",0);
                //导入数据表
                $rn = static::ExtensionInjectMigration($package,static::MIGRATE_UP);
                if(!$rn){
                    $data['status'] = static::STATUS_ERROR;
                    $data['error_no'] = static::ERROR_MIGRATE;
                    $data['msg']      = "扩展Migrate失败,请检查扩展Migration配置!";
                    return $data;
                }

                //注入菜单
                static::showMsg("开始注册菜单...",0);
                static::ExtensionInjectMenu($package);
                static::showMsg("完成",1,'success');

                //注入config到db
                static::showMsg("开始注册系统配置...",0);
                static::ExtensionInjectConfig($package);
                static::showMsg("完成",1,'success');

                //重新生成所有生效插件的config到openadm/config/extensions.php
                static::showMsg("开始刷新所有扩展的配置文件...",0);
                static::RefreshExtensionsConfig($package,'setup');
                static::showMsg("完成",1,'success');

                //完成最后操作
                static::showMsg("保存扩展信息到数据库...",0);
                static::ExtensionSetupedCompleted($package);
                static::showMsg("完成",1,'success');

                $data['status'] = static::STATUS_SUCCESS;
                $data['msg'] = "安装成功";
                static::showMsg("扩展安装完成",1,'success');
                return $data;
            }else{
                static::showMsg("扩展配置文件解析错误,请重新下载后解压到扩展目录！",1,'error');
                //需要去扩展商城下载
                $data['status'] = static::STATUS_ERROR;
                $data['error_no'] = static::ERROR_NOTATLOCAL;
                $data['msg']      = "扩展在本地不存在，请去扩展商城下载安装！";
                return $data;
            }
        }else{
            static::showMsg("扩展已经安装!",1,'success');
            $data = array("status"=>static::STATUS_ERROR,'msg'=>'已经安装了');
        }
        return $data;
    }

    /**
     * 卸载扩展，不删除
     * @param $packageName
     * @param string $packageVersion
     * @param string $locate
     * @return array
     */
    static public function unsetup($packageName,$packageVersion='',$locate='')
    {
        static::showMsg('开始卸载扩展...');
        static::showMsg('检测是否需要执行Migrate...',0);
        $package = static::loader()->getPackage($packageName);
        if($package){
            $rn = static::ExtensionInjectMigration($package,static::MIGRATE_DOWN);
            if(!$rn){
                $data['status'] = static::STATUS_ERROR;
                $data['error_no'] = static::ERROR_MIGRATE;
                $data['msg']      = "扩展Migrate失败,请检查扩展Migration配置!";
                return $data;
            }
        }
        static::showMsg('删除数据库配置...',0);
        static::ExtensionDeleteDBConfig($package);
        static::showMsg('完成',1,'success');
        //composer remove
        static::showMsg('执行 composer remove '. $packageName . '...',1,'info');
        static::showMsg("<p id='cmd_box' style='background-color: #2c763e;color:#f5db88'>",0);
        static::loader()->unSetup($packageName,$locate);
        static::showMsg("</p>",0);
        static::showMsg('完成',1,'success');

        static::ExtensionDeleteStaticVar($package);

        //重新生成所有生效插件的config到openadm/config/extensions.php
        static::showMsg("开始刷新所有扩展的配置文件...",0);
        static::RefreshExtensionsConfig($package,'unsetup');
        static::showMsg('卸载完成!',1,'success');
        $data = array("status"=>static::STATUS_SUCCESS,'msg'=>'卸载完成');
        return $data;
    }

    /**
     * 删除扩展文件
     * @param $packageName
     * @param string $packageVersion
     * @param string $locate
     * @return array
     */
    static public function delete($packageName,$packageVersion='',$locate='')
    {
        static::showMsg('开始删除扩展...');
        try{
            $extensionDir = static::GetExtensionPath($packageName,true);
            FileHelper::removeDirectory($extensionDir);
            static::showMsg('删除完成',1,'success');
            return ['status'=>static::STATUS_SUCCESS,'msg'=>'删除成功'];
        }catch(ErrorException $e){
            static::showMsg('删除失败(没有权限)，请手动删除扩展相关文件和目录！',1,'error');
            static::showMsg($e->getMessage(),1,'error');
            return ['status' => static::STATUS_ERROR,'msg' => "删除失败(没有权限)，请手动删除扩展相关文件和目录！"];
        }
    }

    /**
     * 当数据库有扩展配置，缺少扩展目录的时候，要清理无用扩展
     * @param $packageName
     * @param string $packageVersion
     * @param string $locate
     * @return array
     */
    static public function clear($packageName,$packageVersion='',$locate='')
    {
        static::showMsg('开始清理扩展...');
        static::showMsg('检测是否需要执行Migrate...',0);
        $package = static::loader()->getManager()->getPackageWithoutValidate($packageName);
        if($package){
            static::ExtensionInjectMigration($package,static::MIGRATE_DOWN);
        }
        static::showMsg('删除数据库配置...',0);
        static::ExtensionDeleteDBConfig($package);
        static::showMsg('完成',1,'success');
        static::ExtensionDeleteStaticVar($package);

        //重新生成所有生效插件的config到openadm/config/extensions.php
        static::showMsg("开始刷新所有扩展的配置文件...",0);
        static::RefreshExtensionsConfig($package,'clear');
        static::showMsg('清理完成!',1,'success');
        $data = array("status"=>static::STATUS_SUCCESS,'msg'=>'清理完成');
        return $data;
    }

}
