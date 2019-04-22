<?php
namespace openadm\admin\themes\adminlte2;

use yii\base\Exception;
use yii\web\AssetBundle as BaseAdminLteAsset;

/**
 * AdminLte AssetBundle
 * @since 0.1
 */
class StaticAsset extends BaseAdminLteAsset
{
    public $sourcePath = '@bower/';
    public $css = [
    ];
    public $js = [
        'noty/js/noty/packaged/jquery.noty.packaged.js',
        "jquery-fullscreen/jquery.fullscreen-min.js"
    ];
    public $depends = [
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
