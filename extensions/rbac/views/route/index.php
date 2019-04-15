<?php

use yii\helpers\Html;
use yii\helpers\Json;

use yii2mod\rbac\RbacRouteAsset;
RbacRouteAsset::register($this);
/* @var $this yii\web\View */
/* @var $routes [] */

$this->title = Yii::t('yii2mod.rbac', 'Routes');
$this->params['breadcrumbs'][] = $this->title;
$this->render('/layouts/_sidebar');
?>
<div class="box pad15">
<div class="box-header with-border">
    <h3 class="box-title"><i class="fa fa-user"></i><span class="break"><?php echo Html::encode($this->title); ?></span></h3>
    <div class="box-icon">
    </div>
</div>
<div class="box-body table-responsive">
    <p>
<?php echo Html::a(Yii::t('yii2mod.rbac', 'Refresh'), ['refresh'], [
    'class' => 'btn btn-info btn-sm',
    'id' => 'btn-refresh',
]); ?>
    </p>
<?php echo $this->render('../_dualListBox', [
    'opts' => Json::htmlEncode([
        'items' => $routes,
    ]),
    'assignUrl' => ['assign'],
    'removeUrl' => ['remove'],
]); ?>
</div>
</div>
