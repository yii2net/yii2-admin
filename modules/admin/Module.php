<?php
namespace yikaikeji\openadm\modules\admin;

/**
 * admin module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'yikaikeji\openadm\modules\admin\controllers';

    public $defaultRoute = 'dashboard/index';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }
}
