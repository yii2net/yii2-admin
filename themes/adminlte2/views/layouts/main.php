<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Alert;
use openadm\admin\themes\adminlte2\ThemeAsset;
ThemeAsset::register($this);

?>
<?php $this->beginPage() ?>

    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <title><?= Html::encode($this->title) ?></title>
        <meta name="description" content="OpenAdm是一个基于Yii2的后台开源骨架，集成了用户和插件系统,使用主题功能,默认使用AdminLTE2的模板的主题,可以非常方便的开发新的功能。">
        <meta name="author" content="xiongchuan86@gmail.com">
        <meta name="keyword" content="openadmin,admin,yii2,adminlte,rbac">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <!-- Tell the browser to be responsive to screen width -->
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        <link rel="shortcut icon" href="<?=Url::home(true)?>static/ico/favicon.png">
        <?= Html::csrfMetaTags() ?>
        <![CDATA[YII-BLOCK-HEAD]]>
    </head>

    <?php if(!Yii::$app->user->isGuest):?>
    <body class="hold-transition sidebar-mini skin-yellow-light iframe" style="padding: 15px 15px 0;">
    <div class="content">

        <?php $this->beginBody() ?>
        <?=$content?>
        <?php endif;?>

    </div>
    <!-- end: JavaScript-->
    <?php $this->endBody() ?>
    <?php \openadm\admin\web\Util::Alert(); ?>
    </body>
    </html>
<?php $this->endPage() ?>
