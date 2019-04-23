<?php
/**
 * 主题 Adminlte2
 */

namespace openadm\admin\themes\adminlte2;
use yii\web\AssetBundle;

/**
 * @author chuan xiong <xiongchuan86@gmail.com>
 */
class ThemeAsset extends AssetBundle
{

    const  name = 'adminlte2';
    const  themeId = 'adminlte2';

    public $sourcePath = '@openadm/admin/themes/'.self::themeId.'/assets';
    public $css = [
        'css/openadm.css',
        'css/ajaxcrud.css',
    ];
    public $js = [
        'js/jquery.contextmenu.r2.js',
        'js/ajaxcrud.js',
        'js/tasktab.js',
        'js/openadm.js',
        'js/openadm-modal.js',
        'js/theme.js'
    ];
    public $jsOptions = ['position' => \yii\web\View::POS_BEGIN];
    public $depends = [
        'openadm\admin\themes\adminlte2\AdminltePluginsAsset',
        'openadm\admin\themes\adminlte2\StaticAsset',
        'openadm\admin\themes\adminlte2\LayerAsset',
    ];
}
