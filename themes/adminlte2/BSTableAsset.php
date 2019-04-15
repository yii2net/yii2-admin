<?php
namespace yikaikeji\openadm\themes\adminlte2;

use yii\base\Exception;
use dmstr\web\AdminLteAsset as BaseAdminLteAsset;

/**
 * AdminLte AssetBundle
 * @since 0.1
 */
class BSTableAsset extends BaseAdminLteAsset
{
    public $sourcePath = '@npm/bootstrap-table/dist';
    public $css = [
        'bootstrap-table.css',
    ];
    public $js = [
        'bootstrap-table.js',
        'bootstrap-table-locale-all.js'
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
