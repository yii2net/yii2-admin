<?php
/**
 * 模块管理者
 * 模块id,模块目录，必须为小写
 * @author xiongchuan <xiongchuan@luxtonenet.com>
 */
namespace yikaikeji\openadm\extensions\admin\models;

use yii;
use yikaikeji\openadm\web\SystemConfig;
use yii\helpers\FileHelper;
use yii\base\ErrorException;
use yii\helpers\Json;
use yii\base\InvalidArgumentException;
use yii\helpers\Html;
use Yikaikeji\Extension;

class ExtensionManager
{
    const STATUS_SUCCESS = 1;
    const STATUS_ERROR   = 0;
    const ERROR_NEEDED   = 110;
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
     * 获取已经安装的模块
     */
    static public function GetSetupedExtensions()
    {
        if(empty(static::$_setupedextensions)){
            $extensions = SystemConfig::Get('',null,SystemConfig::CONFIG_TYPE_EXTENSION);
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

    static public function ExtensionSetupedCompleted($extensionid,array $config)
    {
        $record_key = isset(static::$_extensions[$extensionid][static::EXTENSION_CONFIG_ID_RECORD_KEY]) ? static::$_extensions[$extensionid][static::EXTENSION_CONFIG_ID_RECORD_KEY] : [];
        $cfg_value = Json::encode(array_merge($config,[static::EXTENSION_CONFIG_ID_RECORD_KEY=>$record_key]));
        $params = array(
            'cfg_value'   => $cfg_value,
            'cfg_comment' => $config['name'],
            'cfg_type'    =>SystemConfig::CONFIG_TYPE_EXTENSION
        );
        SystemConfig::Set($extensionid,$params);
        return true;
    }


    /**
     * 获取单个extension的config
     * @param $extensionid string
     * @param $cache 是否缓存
     * @param $dir string  实时获取配置
     * @param $checkDependency 是否检查依赖模块
     */
    static public function GetExtensionConfig($extensionid,$cache=true,$dir=null,$checkDependency = true)
    {
        $dir = $dir ? $dir : static::GetExtensionPath($extensionid);
        $config = array(
            'setup'  => static::IsSetuped($extensionid),
            'config' => false
        );
        $extensionconfigfile = $dir ."/config.php";
        if(is_file($extensionconfigfile)){
            if(!static::ParseExtensionConfig($extensionid))return false;
            $config['config'] = require $extensionconfigfile;
            //检查依赖模块
            if($checkDependency){
                static::CheckDependency($config['config']);
            }
        }
        if($cache){
            static::$_extensions[$extensionid] = $config;
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
     * 获取本地的全部模块
     * 支持分页显示
     * @param $type string  all:全部,setuped:安装的,new:新的
     * @param $page int
     * @param $pageSize int
     * @return array|boolean
     */
    static public function GetExtensions($type="all",$page=1,$pageSize=20)
    {
        //获取数据源
        $setupedextensions = static::GetSetupedExtensions();
        if("setuped"==$type){
            $extensions = array_map('strtolower',array_keys($setupedextensions));
        }else{
            $extensionDir = Yii::getAlias('@extensions');
            $extensions = static::GetExtensionsWithNamespace($extensionDir);
            //改写fileArray
            if("new" == $type){
                $setuped = array_map('strtolower',array_keys($setupedextensions));
                $extensions = array_diff($extensions, $setuped);
            }
        }//获取数据源结束

        //对分页进行边界判断
        if($pageSize <=0){
            $pageSize = 20;
        }
        $total = count($extensions);
        $pages = ceil($total/$pageSize);
        if($page<=0){
            $page = 1;
        }
        if($page>=$pages){
            $page = $pages;
        }
        //分页判断结束
        $start = ($page-1)*$pageSize;
        $extensionsArraySlice = array_slice($extensions, $start,$pageSize);

        if(!empty($extensionsArraySlice)){
            foreach($extensionsArraySlice as $extensionid){
                //过滤不合格的extension
                if(!static::ParseExtensionConfig($extensionid)){
                    continue;
                }
                static::$_extensions[$extensionid] = array(
                    'setup'  => static::IsSetuped($extensionid),
                    'config' => false
                );
                $extensionconfigfile = static::GetExtensionPath($extensionid)."/config.php";
                if(is_file($extensionconfigfile)){
                    static::$_extensions[$extensionid]['config'] = require $extensionconfigfile;
                    //检查依赖模块
                    static::CheckDependency(static::$_extensions[$extensionid]['config']);
                }
            }
            $result = array(
                'page' => $page,
                'pageSize' => $pageSize,
                'total' => $total,
                'pages' => $pages,
                'data'  => static::$_extensions
            );
            return $result;
        }
        return false;
    }


    /**
     * 获取模块路径
     */
    static public function GetExtensionPath($extensionid)
    {
        return Yii::getAlias('@extensions').DIRECTORY_SEPARATOR.strtolower($extensionid).DIRECTORY_SEPARATOR;
    }

    /**
     * 删除静态变量数组里面的值
     */
    static public function ExtensionDeleteStaticVar($extensionid)
    {
        if(!empty(static::$_setupedextensions)){
            unset(static::$_setupedextensions[$extensionid]);
        }
    }

    /**
     * 判断是否已经安装
     */
    static public function IsSetuped($extensionid)
    {
        if(empty(static::$_setupedextensions)){
            static::GetSetupedExtensions();
        }
        return isset(static::$_setupedextensions[$extensionid]) ? 1 : 0;
    }

    /**
     * 检测依赖关系
     */
    static public function CheckDependency(array &$config)
    {
        $unsetuped = array();
        if(is_array($config)){
            $dependencies = isset($config['dependencies']) ? $config['dependencies'] : '';
            $array = $dependencies ? explode(",", $dependencies) : '';
            if(!empty($array)){
                static::showMsg('');
                foreach($array as $extensionid){
                    if($extensionid){
                        static::showMsg('|___检测依赖模块:'.$extensionid.'是否安装...',0);
                        if(0 == static::IsSetuped($extensionid)){
                            $unsetuped[] = $extensionid;
                            static::showMsg('未安装',1,'error');
                        }else{
                            static::showMsg('已安装',1,'success');
                        }
                    }
                }
            }
        }
        $config['needed'] = join(",",$unsetuped);
    }

    /**
     * 模块注入route
     */
    static public function ExtensionInjectRoute(array $conf)
    {
        if(isset($conf['route']) && !empty($conf['route']) && is_array($conf['route'])){
            $params = [
                'cfg_value'   => Json::encode($conf['route']),
                'cfg_comment' => $conf['id'],
                'cfg_pid'     => 0,
                'cfg_order'   => 0,
                'cfg_type'    => 'ROUTE'
            ];
            $cfg_name = strtoupper("extension_{$conf['id']}_route");
            $lastid = SystemConfig::Set($cfg_name,$params);
            static::RecordExtensionConfigId($conf['id'],$lastid);
        }
    }

    /**
     * 把config注入到system_config
     * @param array $conf
     */
    static public function ExtensionInjectConfig(array $conf)
    {
        if(isset($conf['config']) && !empty($conf['config']) && is_array($conf['config'])){
            foreach ($conf['config'] as $config){
                if(isset($config['cfg_name']) && !empty($config['cfg_name'])){
                    $params = [
                        'cfg_name'  => $config['cfg_name'],
                        'cfg_value' => isset($config['cfg_value']) ? $config['cfg_value'] : '',
                        'cfg_comment' => isset($config['cfg_comment']) ? $config['cfg_comment'] : '',
                    ];
                    $lastid = SystemConfig::Set($config['cfg_name'],$params);
                    static::RecordExtensionConfigId($conf['id'],$lastid);
                }
            }
        }
    }

    /**
     * 安装过程中,记录_pluings[extensionId] = ['config_ids'=>[]]
     * @param $extensionId extension id
     * @param $configId system_config id
     */
    static public function RecordExtensionConfigId($extensionId,$configId)
    {
        if( $configId>0){
            if(!isset(static::$_extensions[$extensionId])){
                static::$_extensions[$extensionId] = [];
            }
            if(!isset(static::$_extensions[$extensionId][static::EXTENSION_CONFIG_ID_RECORD_KEY])){
                static::$_extensions[$extensionId][static::EXTENSION_CONFIG_ID_RECORD_KEY] = [];
            }
            array_push(static::$_extensions[$extensionId][static::EXTENSION_CONFIG_ID_RECORD_KEY],$configId);
        }
    }

    /**
     * 实际注入方法
     * @param $extensionId
     * @param $cfg_name
     * @param array $menus
     */
    static public function _ExtensionInjectMenu($extensionId,$cfg_pid,array $menus)
    {
        $extension_last_config = static::ExtensionLastSavedConfig($extensionId);
        foreach ($menus as $menu){
            $params = [
                'cfg_value'   => isset($menu['cfg_value']) ? $menu['cfg_value'] : '',
                'cfg_comment' => isset($menu['cfg_comment']) ? $menu['cfg_comment'] : '',
                'cfg_pid'     => $cfg_pid ==0 ? (isset($menu['cfg_pid']) ? $menu['cfg_pid'] : 0) : $cfg_pid,
                'cfg_order'   => isset($menu['cfg_order']) ? $menu['cfg_order'] : 0
            ];
            //使用旧的配置信息
            if(!empty($extension_last_config) && isset($extension_last_config['menus']) && isset($extension_last_config['menus'][$params['cfg_comment']])){
                $params['cfg_pid'] = $extension_last_config['menus'][$params['cfg_comment']]['cfg_pid'];
                $params['cfg_order'] = $extension_last_config['menus'][$params['cfg_comment']]['cfg_order'];
            }

            if(empty($params['cfg_value']) || empty($params['cfg_comment']))continue;
            //检查cfg_value是否为数组,并且有url,icon(可选)
            if(is_array($params['cfg_value']) && isset($params['cfg_value']['url'])){
                $params['cfg_value'] = Json::encode($params['cfg_value']);
            }else{
                continue;//不满条件,就继续foreach
            }
            //写入system_config表
            $lastPuginConfigId = SystemConfig::Set(SystemConfig::MENU_KEY,$params);
            static::RecordExtensionConfigId($extensionId,$lastPuginConfigId);

            //检查是否有子菜单
            if(isset($menu['items']) && is_array($menu['items'])){
                static::_ExtensionInjectMenu($extensionId,$lastPuginConfigId,$menu['items']);
            }
        }

    }

    /**
     * 注入Extension的数据库操作
     * @param $extensionid extensionid
     * @param $type up/down up=创建,down=回退
     */
    static public function ExtensionInjectMigration($extensionid,$type)
    {
        $configRaw = static::GetExtensionConfig($extensionid,true,null,false);
        $conf      = $configRaw['config'];
        if(!$conf){
            //extension 目录异常
            static::showMsg("");
            static::showMsg("获取模块配置失败,请检查模块是否正常!",1,'error');
            return false;
        }
        if(isset($conf['migrationDirName']) && !empty($conf['migrationDirName'])){
            $migrationDirName = $conf['migrationDirName'];
        }else{
            $migrationDirName = static::MIGRATION_DEFAULT_DIRNAME;
        }
        //检查是否需要migrate操作,原则是看是否有migrations目录
        $migrationPath = Yii::getAlias('@extensions/'.$extensionid.'/'.$migrationDirName);
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

            static::showMsg("</p>",0);
        }else{
            static::showMsg("不需要",1,'success');
        }
        return true;
    }

    /**
     * 模块菜单注入
     */
    static public function ExtensionInjectMenu(array $conf)
    {
        $extensionId = $conf['id'];
        if(isset($conf['menus']) && is_array($conf['menus']) && !empty($conf['menus'])){
            static::_ExtensionInjectMenu($extensionId,0,$conf['menus']);
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
    static public function ParseExtensionConfig($extensionid,$conf=null)
    {
        if(is_array($conf)){
            $config = $conf;
        }else{
            $configfile = static::GetExtensionPath($extensionid)."/config.php";
            if(!is_file($configfile))return false;
            $config = require $configfile;
        }
        //extensionidController的extensionid要和extensionid.php里面的id值相等
        if(!isset($config['id']) || $extensionid != $config['id']){
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
     * 移除模块在system_config里面的配置
     * @param $extensionid string
     */
    static public function ExtensionDeleteDBConfig($extensionid)
    {
        $extensions = SystemConfig::Get($extensionid,null,SystemConfig::CONFIG_TYPE_EXTENSION);
        if($extensions && is_array($extensions))foreach ($extensions as $extension){
            try{
                $value = Json::decode($extension['cfg_value']);
                $config_ids = isset($value[static::EXTENSION_CONFIG_ID_RECORD_KEY]) ? $value[static::EXTENSION_CONFIG_ID_RECORD_KEY] : [];
                if(is_array($config_ids) && !empty($config_ids))foreach ($config_ids as $id){
                    $configRaw = SystemConfig::GetById($id);
                    if($configRaw && in_array($configRaw['cfg_name'],[SystemConfig::MENU_KEY,SystemConfig::HOMEMENU_KEY])){
                        static::ExtensionSaveOldConfig($extensionid,$configRaw);
                    }
                    SystemConfig::Remove($id);
                }
            }catch (InvalidArgumentException $e){

            }
            //删除自己
            SystemConfig::Remove($extension['id']);
        }
        return false;
    }

    /**
     * 卸载前 把模块的配置保存,以便下次安装的时候可以使用之前配置好的参数
     * @param $extensionid
     * @param $config
     */
    static public function ExtensionSaveOldConfig($extensionid,$config)
    {
        static::showMsg('<br/>保存模块配置信息到模块目录...');
        $Dir = static::GetExtensionPath($extensionid).'unsetup/';
        if(!is_dir($Dir)){
            @mkdir($Dir,0777);
        }
        $old_config_path = $Dir.'unsetup_save_config.php';
        if(!is_file($old_config_path)){
            @file_put_contents($old_config_path,'');
        }
        if(is_writable($Dir) && is_writable($old_config_path)){
            $content = file_get_contents($old_config_path);
            $save_config = [];
            if($content){
                try{
                    $save_config = Json::decode($content,true);
                }catch (InvalidArgumentException $e){
                }
            }
            if( is_array($save_config) ){
                if(!isset($save_config["menus"])){
                    $save_config["menus"] = [];
                }
            }else{
                $save_config = [];
                $save_config["menus"] = [];
            }
            $save_config["menus"][$config['cfg_comment']] = $config;
            file_put_contents($old_config_path,Json::encode($save_config));
            static::showMsg("配置路径:$old_config_path ... 保存完成!");
        }else{
            static::showMsg("配置路径:$old_config_path ... 不可写, 跳过!");
        }
    }

    /**
     * 获取模块之前保存的配置信息
     * @param $extensionid
     * @return array|mixed
     */
    static public function ExtensionLastSavedConfig($extensionid)
    {
        $path = static::GetExtensionPath($extensionid).'unsetup/unsetup_save_config.php';
        $save_config = [];
        if(is_file($path)){
            $content = file_get_contents( $path );
            try{
                $save_config = Json::decode($content,true);
            }catch (InvalidArgumentException $e){
            }
        }
        return $save_config;
    }


    /**
     * 安装模块
     * @param $extensionid
     */
    static public function setup($extensionid)
    {
        static::showMsg("开始安装模块...");
        $data = array("status"=>static::STATUS_ERROR,'msg'=>'未知错误');
        //检查是否已经安装
        if( 0 == static::IsSetuped($extensionid)){
            static::showMsg("获取模块配置...",0);
            $configRaw = static::GetExtensionConfig($extensionid,false,null,false);//关闭这里的模块检测
            $config = $configRaw['config'];
            static::showMsg("完成",1,'success');
            static::showMsg("检测模块依赖...",0);
            static::CheckDependency($config);//在这里检测模块依赖
            if(isset($config['needed']) && !empty($config['needed'])){
                static::showMsg("");
                static::showMsg("请先安装缺失的依赖模块:{$config['needed']}，再安装此模块！",1,'error');
                $data['status'] = static::STATUS_ERROR;
                $data['error_no'] = static::ERROR_NEEDED;
                $data['msg']      = "请先安装缺失的依赖模块，再安装此模块！";
                return $data;
            }
            static::showMsg("检测完成",1,'success');
            if($config){
                static::showMsg("检测是否需要执行Migrate...",0);
                //导入数据表
                $rn = static::ExtensionInjectMigration($extensionid,static::MIGRATE_UP);
                if(!$rn){
                    $data['status'] = static::STATUS_ERROR;
                    $data['error_no'] = static::ERROR_MIGRATE;
                    $data['msg']      = "模块Migrate失败,请检查模块Migration配置!";
                    return $data;
                }
                static::showMsg("开始注册菜单...",0);
                //注入菜单
                static::ExtensionInjectMenu($config);
                static::showMsg("完成",1,'success');
                static::showMsg("开始注册路由...",0);
                //注入route
                static::ExtensionInjectRoute($config);
                static::showMsg("完成",1,'success');
                static::showMsg("开始注册系统配置...",0);
                //注入config
                static::ExtensionInjectConfig($config);
                static::showMsg("完成",1,'success');
                static::showMsg("保存模块信息到数据库...",0);
                //完成最后操作
                static::ExtensionSetupedCompleted($extensionid,$config);
                static::showMsg("完成",1,'success');
                $data['status'] = static::STATUS_SUCCESS;
                $data['msg'] = "安装成功";
                static::showMsg("模块安装完成",1,'success');
                return $data;
            }else{
                static::showMsg("模块配置文件解析错误,请重新下载后解压到模块目录！",1,'error');
                //需要去模块商城下载
                $data['status'] = static::STATUS_ERROR;
                $data['error_no'] = static::ERROR_NOTATLOCAL;
                $data['msg']      = "模块在本地不存在，请去模块商城下载安装！";
                return $data;
            }
        }else{
            static::showMsg("模块已经安装!",1,'success');
            $data = array("status"=>static::STATUS_ERROR,'msg'=>'已经安装了');
        }
        return $data;
    }

    /**
     * 卸载模块
     * @param $extensionid
     */
    static public function unsetup($extensionid)
    {
        static::showMsg('开始卸载模块...');
        static::showMsg('检测是否需要执行Migrate...',0);
        $rn = static::ExtensionInjectMigration($extensionid,static::MIGRATE_DOWN);
        if(!$rn){
            $data['status'] = static::STATUS_ERROR;
            $data['error_no'] = static::ERROR_MIGRATE;
            $data['msg']      = "模块Migrate失败,请检查模块Migration配置!";
            return $data;
        }
        static::showMsg('删除数据库配置...',0);
        static::ExtensionDeleteDBConfig($extensionid);
        static::showMsg('完成',1,'success');
        static::ExtensionDeleteStaticVar($extensionid);
        static::showMsg('卸载完成!',1,'success');
        $data = array("status"=>static::STATUS_SUCCESS,'msg'=>'卸载完成');
        return $data;
    }

    /**
     * 删除模块
     * @param $extensionid string
     */
    static public function delete($extensionid)
    {
        static::showMsg('开始删除模块...');
        try{
            $extensionDir = static::GetExtensionPath($extensionid);
            FileHelper::removeDirectory($extensionDir);
            static::showMsg('删除完成',1,'success');
            return ['status'=>static::STATUS_SUCCESS,'msg'=>'删除成功'];
        }catch(ErrorException $e){
            static::showMsg('删除失败(没有权限)，请手动删除模块相关文件和目录！',1,'error');
            static::showMsg($e->getMessage(),1,'error');
            return ['status' => static::STATUS_ERROR,'msg' => "删除失败(没有权限)，请手动删除模块相关文件和目录！"];
        }
    }

}
