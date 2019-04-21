<?php
namespace openadm\admin\themes\adminlte2;

use yii\base\Exception;
use dmstr\web\AdminLteAsset as BaseAdminLteAsset;

/**
 * AdminLte AssetBundle
 * @since 0.1
 */
class AdminLteAsset extends BaseAdminLteAsset
{
    public $sourcePath = '@vendor/almasaeed2010/adminlte/dist';
    public $css = [
        'css/AdminLTE.min.css',
        'css/skins/_all-skins.min.css'
    ];
    public $js = [
        'js/adminlte.min.js'
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'rmrevin\yii\fontawesome\AssetBundle',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
        'kartik\select2\ThemeDefaultAsset'
    ];

    /**
     * @var string|bool Choose skin color, eg. `'skin-blue'` or set `false` to disable skin loading
     * @see https://almsaeedstudio.com/themes/AdminLTE/documentation/index.html#layout
     */
    public $skin = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        // Append skin color file if specified
        parent::init();
    }
}
