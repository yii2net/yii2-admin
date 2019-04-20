<?php
/**
 * Created by PhpStorm.
 * User: xiongchuan
 * Date: 2017/1/1
 * Time: 下午12:10
 */
namespace openadm\admin\themes\adminlte2;
use yii\web\AssetBundle;
class AdminltePluginsAsset extends AssetBundle
{
    public $sourcePath = '@vendor/almasaeed2010/adminlte/bower_components';
    public $js = [
        'fastclick/lib/fastclick.js',
    ];
    public $css = [
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
        'openadm\admin\themes\adminlte2\AdminLteAsset',
        'openadm\admin\themes\adminlte2\ShowLoadingAsset',
    ];
}