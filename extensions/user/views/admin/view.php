<?php

use yii\helpers\Html;
use kartik\dialog\Dialog;
use kartik\detail\DetailView;


/**
 * @var yii\web\View $this
 * @var amnah\yii2\user\models\User $user
 */

$this->title = Yii::t('user', 'User').":".$user->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('user', 'Users'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="box">
    <div class="box-header with-border" style="display: none">
        <h3 class="box-title"><i class="fa fa-user"></i><span class="break"><?php echo Html::encode($this->title); ?></span></h3>
        <div class="box-icon">
        </div>
    </div>
    <div class="box-body pad table-responsive">

    <?php
    $pk = $user->getPrimaryKey();
    echo DetailView::widget([
        'model' => $user,
        'condensed'=>false,
        'hover'=>true,
        'mode'=>Yii::$app->request->get('edit')=='t' ? DetailView::MODE_EDIT : DetailView::MODE_VIEW,
//        'panel'=>[
//            'heading'=>'查看',
//            'type'=>DetailView::TYPE_INFO,
//        ],

        //提示信息设置
        'alertMessageSettings'=>[
            'kv-detail-error' => 'alert alert-danger',
            'kv-detail-success' => 'alert alert-success',
            'kv-detail-info' => 'alert alert-info',
            'kv-detail-warning' => 'alert alert-warning'
        ],

        //弹框按钮设定
        'krajeeDialogSettings'=>[
            /*'options' =>[
                'size' => Dialog::SIZE_SMALL
            ],*/
            'dialogDefaults'=>[
                Dialog::DIALOG_ALERT => [
                    'type' => Dialog::TYPE_INFO,
                    'title' => '提示',
                    'buttonLabel' => '<i class="glyphicon glyphicon-ok"></i> 确定'
                ],
                Dialog::DIALOG_CONFIRM => [
                    'type' => Dialog::TYPE_WARNING,
                    'title' => "确认",
                    'btnOKClass' => 'btn-warning',
                    'btnOKLabel' => '<i class="glyphicon glyphicon-ok"></i> 确定',
                    'btnCancelLabel' => '<i class="glyphicon glyphicon-ban-circle"></i> 取消'
                ],
            ],
        ],

        'deleteOptions'=>[
            'url'=>['deletefromdetail','id'=>$pk],
            'lable' =>'删除',
        ],
        /*'updateOptions'=>[
            'url'=>['detailview'],
        ],*/
        'formOptions' =>[
            'id' => "edit-model-form",
            //'action' => ["user/update",id=>$pk],
            'action' => "/user/update?id=$pk",
        ],
        'container' => ['id'=>'kv-demo'],
        //'formOptions' => ['action' => \yii\helpers\Url::to("/mgr/history/detailviewdelete")],// your action to delete
        'enableEditMode'=>false,
        //'buttons1' =>'{update}',
        'buttons2' => '{reset} {save}',
        'attributes' => [
            'id',
            'role_id',
            'status',
            'email:email',
            'username',
            [                      // the owner name of the model
              'label' => $profile->getAttributeLabel('full_name'),
              'value' => $profile->full_name,
            ],
            'password',
            'auth_key',
            'access_token',
            'logged_in_ip',
            'logged_in_at',
            'created_ip',
            'created_at',
            'updated_at',
            'banned_at',
            'banned_reason',
        ],
    ]) ?>
</div>
</div>
