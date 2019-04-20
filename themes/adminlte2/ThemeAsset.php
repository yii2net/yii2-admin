<?php
/**
 * 主题 Adminlte2
 */

namespace yikaikeji\openadm\themes\adminlte2;
use yii\web\AssetBundle;

/**
 * @author chuan xiong <xiongchuan86@gmail.com>
 */
class ThemeAsset extends AssetBundle
{

    const  name = 'adminlte2';
    const  themeId = 'adminlte2';

    public $sourcePath = '@openadm/themes/'.self::themeId.'/assets';
    public $css = [
        'css/openadm.css'
    ];
    public $js = [
        'js/jquery.contextmenu.r2.js',
        'js/tasktab.js',
        'js/openadm.js',
        'js/openadm-modal.js',
        'js/theme.js'
    ];
    public $jsOptions = ['position' => \yii\web\View::POS_BEGIN];
    public $depends = [
        'yikaikeji\openadm\themes\adminlte2\AdminltePluginsAsset',
        'yikaikeji\openadm\themes\adminlte2\NotyAsset',
        'yikaikeji\openadm\themes\adminlte2\LayerAsset',
        'xiongchuan86\kartikcrud\CrudAsset'
    ];
}
