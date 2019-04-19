<?php
namespace yikaikeji\openadm\themes\adminlte2;

use yii\base\Exception;
use dmstr\web\AdminLteAsset as BaseAdminLteAsset;

/**
 * AdminLte AssetBundle
 * @since 0.1
 */
class LayerAsset extends BaseAdminLteAsset
{
    public $sourcePath = '@openadm/themes/adminlte2/assets/libs/layer/';
    public $css = [
    ];
    public $js = [
        'layer.js',
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
