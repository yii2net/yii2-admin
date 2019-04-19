<?php
namespace yikaikeji\openadm\web;

use Yii;

class Util
{

    const APP_SINGLE = 'single';
    const APP_IFRAME = 'iframe';

    const CACHE_5MINS           = 300;
    const CACHE_30MINS          = 1800;
    const CACHE_1HOURS          = 3600;

    const CACHE_NAMESPACE = "openadm::";

    static public function cache_flush()
    {
        Yii::$app->cache->flush();
    }

    static public function getCache()
    {
        return Yii::$app->cache;
    }

    static public function cache_set($key,$data,$expired='')
    {
        if(empty($expired)){
            $expired = static::CACHE_1HOURS;
        }
        return static::getCache()->set(self::CACHE_NAMESPACE.$key,$data,$expired);
    }

    static public function cache_get($key)
    {
        return static::getCache()->get(self::CACHE_NAMESPACE.$key);
    }

    static public function checkIframeOrSingle($url)
    {
        //判断是否以http开头
        if(preg_match('/^http/i',$url)){
            return self::APP_IFRAME;
        }
        try{
            $controller = Yii::$app->createController($url);
            if($controller){
                $controller = $controller[0];
                if($controller->layout === false){
                    return self::APP_SINGLE;
                }else{
                    return self::APP_IFRAME;
                }
            }else{
                return self::APP_IFRAME;
            }
        }catch (\Exception $e){
            return self::APP_IFRAME;
        }
    }

    static public function checkIframeOrSingleCached($url)
    {
        $appType = self::cache_get($url);
        if(!$appType){
            $appType = self::checkIframeOrSingle($url);
            self::cache_set($url,$appType);
        }
        return $appType;
    }

    static public function Alert()
    {
        $alertTypes = [
            'error' => 'alert-danger',
            'danger' => 'alert-danger',
            'success' => 'alert-success',
            'info' => 'alert-info',
            'warning' => 'alert-warning'
        ];
        $session = Yii::$app->session;
        $flashes = $session->getAllFlashes();
        foreach ($flashes as $type => $data) {
            if (isset($alertTypes[$type])) {
                $data = (array)$data;
                foreach ($data as $i => $message) {
                    echo "<script>oa.Noty({type:'{$type}',text:'$message'});</script>";
                }
                $session->removeFlash($type);
            }
        }
    }
}