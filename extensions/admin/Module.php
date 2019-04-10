<?php
namespace yikaikeji\openadm\extensions\admin;

use Yii;
/**
 * admin module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'yikaikeji\openadm\extensions\admin\controllers';

    public $defaultRoute = 'dashboard/index';

    public $packageInstalledPath = '@app/extensions';

    public $packageScanPath = '@storage/extensions';

    public $rootProjectPath = '@root';

    public $composerPath = '/usr/local/bin/composer';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }
}
