<?php
namespace openadm\admin\web;

use yii;
use yii\base\Component;
use yii\web\Request;
use yii\helpers\Json;
use yii\base\InvalidArgumentException;

class SystemEvent extends Component
{

    static public function beforeRequest()
    {
        //去掉pathInfo最后面的斜线:blog/=> blog,有斜线,urlRule无法正常匹配
        $length = strlen(Yii::$app->request->pathInfo);
        if($length>0){
            if('/' === Yii::$app->request->pathInfo[$length-1]){
                $pathInfo = substr(Yii::$app->request->pathInfo,0,$length-1);
                Yii::$app->request->pathInfo = $pathInfo;
            }

        }
        //=====
        static::AddUrlRules();
    }

    static public function AddUrlRules()
    {
        $routes = SystemConfig::Get("",null,'ROUTE');
        if($routes){
            foreach($routes as $route){
                try{
                    $rules = Json::decode($route['cfg_value'],true);
                    Yii::$app->urlManager->addRules($rules);
                }catch (InvalidArgumentException $e){
                }
            }
        }
    }

    /**
     * 获取菜单项,并且通过权限过滤
     * @param $key system_config的cfg_key
     * @param $pid system_config的id
     */
    static public function GetCanAccessMenu($key,$pid)
    {
        $menus = SystemConfig::Get($key,$pid,SystemConfig::CONFIG_TYPE_USER);
        if(is_array($menus) && !empty($menus)){
            foreach ($menus as $k=>$menu){
                try{
                    $value = Json::decode($menu['cfg_value'],true);
                    $type  = isset($value['type']) ? $value['type'] : 1;
                    if(isset($value['url'])){//必须要有url字段
                        if($type==1 && !static::CheckAccessMenu($value['url'])){
                            unset($menus[$k]);
                        }
                        else{
                            //判断url类型是iframe or  single page
                            $value['apptype'] = Util::checkIframeOrSingleCached($value['url']);
                            $menus[$k]['value'] = $value;
                        }
                    }
                }catch (InvalidArgumentException $e){
                    continue;
                }

            }
        }
        return $menus;
    }

    static public function FortmatMenus($menus,$pid=0)
    {
        $items = [];
        if(is_array($menus) && !empty($menus)){
            foreach ($menus as $k=>$menu){
                if($menu['cfg_pid']==$pid){
                    $_menu = [];
                    $_menu['content'] = $menu;
                    $submenus = static::FortmatMenus($menus,$menu['id']);
                    if(!empty($submenus)){
                        $_menu['items'] = $submenus;
                    }
                    $items[] = $_menu;
                }
            }
        }
        return $items;
    }


    //获取Admin菜单
    static public function GetAdminMenu(){
        //默认获取全部的菜单
        $menus  = static::GetCanAccessMenu('MENU','');

        return  static::FortmatMenus($menus);
    }

    /**
     * 判断菜单的权限
     */
    static public function CheckAccessMenu($url)
    {
        if($url == "#"){
            return true;
        }
        $m = Yii::$app->controller->module->id;
        $c = Yii::$app->controller->id;
        $a = Yii::$app->controller->action->id;
        $user = Yii::$app->getUser();
        //route
        $route = "/{$m}/{$c}/{$a}";
        if($user->can(str_replace("//", "/",$route),[],true)){
            return true;
        }//check action
        $route = "/{$m}/{$c}/*";
        if($user->can(str_replace("//", "/",$route),[],true)){
            return true;
        }//check controller
        $route = "/{$m}/*";
        //check
        if($user->can(str_replace("//", "/",$route),[],true)){
            return true;
        }//check module
        return false;
    }

}
