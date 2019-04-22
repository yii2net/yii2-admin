<?php

namespace openadm\admin\console;

use yii\base\InvalidConfigException;
use yii\console\controllers\MigrateController as BaseMigrateController;
use yii;
use yii\helpers\Console;
use openadm\admin\extensions\admin\models\ExtensionManager;
/**
 * Class MigrateController
 *
 * @package yii2mod\rbac\commands
 *
 * Below are some common usages of this command:
 *
 * ```
 * # creates a new migration named 'create_rule'
 * yii rbac/migrate/create create_rule
 *
 * # applies ALL new migrations
 * yii rbac/migrate
 *
 * # reverts the last applied migration
 * yii rbac/migrate/down
 * ```
 */
class MigrateController extends BaseMigrateController
{
    /**
     * @inheritdoc
     */
    public $migrationTable = '{{%migration}}';

    /**
     * @var string 默认路径
     */
    public $defaultMigrationPath = '@app/migrations';

    /**
     * @inheritdoc
     */
    public $migrationPath = '';

    /**
     * @inheritdoc
     */
    public $templateFile = '@yii/views/migration.php';

    protected $_extension_migration_paths = [];

    /**
     * 如果 migrationPath 有值,则说明yii migrate --migrationPath=$path,通过传参运行
     * migrationPath 没有传参,则使用默认的 defaultMigrationPath 和 extension的migrations
     *
     * 此method需要放在beforeAction里面,此时migrationPath已经被赋值。
     */
    public function generateMigrationPath()
    {
        //
        if($this->migrationPath != ''){
            $path = Yii::getAlias($this->migrationPath);
            if(is_dir($path)){
                $this->_extension_migration_paths[] = $path;
            }else{
                //报错,退出
                throw new InvalidConfigException("Migration failed. Directory specified in migrationPath doesn't exist: {$this->migrationPath}");
            }

        }else{
            //如果system config被migrate/down 删除了,会报错,通过try catch 过滤掉
            try {
                //自动把插件的migrations path加入到搜索路径
                $setupedExtensions = ExtensionManager::GetSetupedExtensions();
                if ($setupedExtensions && is_array($setupedExtensions)){
                    foreach ($setupedExtensions as $extension) {
                        $packageName = isset($extension['name']) ? $extension['name'] : '';
                        $path = Yii::getAlias('@extensions') . DIRECTORY_SEPARATOR . "{$packageName}" . DIRECTORY_SEPARATOR . "migrations";
                        if (is_dir($path)) {
                            $this->_extension_migration_paths[] = $path;
                        }
                    }
                }
            }catch (yii\base\Exception $e){
                //不影响
            }
            $this->migrationPath = $this->defaultMigrationPath;//如果不赋值,basemigratecontroller会报错
            //默认的migrationPath
            $path = Yii::getAlias($this->migrationPath);
            if(is_dir($path)){
                $this->_extension_migration_paths[] = $path;
            }
        }
        //添加path到include path
        if(count($this->_extension_migration_paths)>0){
            $need_include_paths = join(PATH_SEPARATOR,$this->_extension_migration_paths);
            set_include_path(get_include_path() . PATH_SEPARATOR . $need_include_paths);
        }else{
            throw new InvalidConfigException('At least one of `defaultMigrationPath` or `migrationPath` or `migrationNamespaces` should be specified.');
        }
    }

    public function beforeAction($action){

        $this->generateMigrationPath();

        return parent::beforeAction($action);
    }

    //重构
    protected function createMigration($class)
    {
        $class = trim($class, '\\');
        if (strpos($class, '\\') === false) {
            $file = $class . '.php';
            require_once($file);
        }

        return new $class();
    }

    //重构
    protected function getNewMigrations()
    {
        $applied = [];
        foreach ($this->getMigrationHistory(null) as $class => $time) {
            $applied[trim($class, '\\')] = true;
        }

        $migrationPaths = [];
        //把extensions的加进来
        foreach ($this->_extension_migration_paths as $_path){
            $migrationPaths[] = $_path;
        }
        foreach ($this->migrationNamespaces as $namespace) {
            $migrationPaths[$namespace] = $this->getNamespacePath($namespace);
        }


        $migrations = [];
        foreach ($migrationPaths as $namespace => $migrationPath) {
            if (!file_exists($migrationPath)) {
                continue;
            }
            $handle = opendir($migrationPath);
            while (($file = readdir($handle)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $path = $migrationPath . DIRECTORY_SEPARATOR . $file;
                if (preg_match('/^(m(\d{6}_?\d{6})\D.*?)\.php$/is', $file, $matches) && is_file($path)) {
                    $class = $matches[1];
                    //增加判断,如果namespace是数字,代表添加的全局的extension的migrations
                    if (!empty($namespace) && !is_numeric($namespace)) {
                        $class = $namespace . '\\' . $class;
                    }
                    $time = str_replace('_', '', $matches[2]);
                    if (!isset($applied[$class])) {
                        $migrations[$time . '\\' . $class] = $class;
                    }
                }
            }
            closedir($handle);
        }
        ksort($migrations);

        return array_values($migrations);
    }

    public function getMigrationClassOfExtension()
    {
        $dir = dir(Yii::getAlias($this->migrationPath));
        $migrations = [];
        while( ($file = $dir->read()) !== false){
            if($file != '.' && $file != '..'){
                $migrations[] = str_replace(".php","",$file);
            }
        }
        return $migrations;
    }

    public function actionDownExtension()
    {
        $migrations = $this->getMigrationClassOfExtension();

        if (empty($migrations)) {
            $this->stdout("No migration has been done before.\n", Console::FG_YELLOW);

            return static::EXIT_CODE_NORMAL;
        }

        $n = count($migrations);
        $this->stdout("Total $n " . ($n === 1 ? 'migration' : 'migrations') . " to be reverted:\n", Console::FG_YELLOW);
        foreach ($migrations as $migration) {
            $this->stdout("\t$migration\n");
        }
        $this->stdout("\n");

        $reverted = 0;
        if ($this->confirm('Revert the above ' . ($n === 1 ? 'migration' : 'migrations') . '?')) {
            foreach ($migrations as $migration) {
                if (!$this->migrateDown($migration)) {
                    $this->stdout("\n$reverted from $n " . ($reverted === 1 ? 'migration was' : 'migrations were') ." reverted.\n", Console::FG_RED);
                    $this->stdout("\nMigration failed. The rest of the migrations are canceled.\n", Console::FG_RED);

                    return static::EXIT_CODE_ERROR;
                }
                $reverted++;
            }
            $this->stdout("\n$n " . ($n === 1 ? 'migration was' : 'migrations were') ." reverted.\n", Console::FG_GREEN);
            $this->stdout("\nMigrated down successfully.\n", Console::FG_GREEN);
        }
    }
}
