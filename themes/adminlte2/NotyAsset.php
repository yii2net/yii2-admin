<?php
namespace openadm\admin\themes\adminlte2;

use yii\base\Exception;
use yii\web\AssetBundle as BaseAdminLteAsset;

/**
 * AdminLte AssetBundle
 * @since 0.1
 */
class NotyAsset extends BaseAdminLteAsset
{
    public $sourcePath = '@bower/noty/js/noty';
    public $css = [
    ];
    public $js = [
        'packaged/jquery.noty.packaged.js',
//        'themes/default.js',
//        'themes/bootstrap.js',
//        'themes/metroui.js',
//        'themes/relax.js',
//        'themes/semanticUI.js',
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
