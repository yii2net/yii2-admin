<?php

use yii\bootstrap\Html;
use yii\helpers\Url;
use yii2mod\editable\EditableColumn;
use yii\widgets\Pjax;
use yii\bootstrap\Tabs;
use kartik\dynagrid\DynaGrid;
use kartik\grid\GridView;
use yii\bootstrap\Button;
use yii\bootstrap\ButtonGroup;
use yii\bootstrap\Modal;
use openadm\kartikcrud\BulkButtonWidget;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var amnah\yii2\user\Module $module
 * @var amnah\yii2\user\models\search\UserSearch $searchModel
 * @var amnah\yii2\user\models\User $user
 * @var amnah\yii2\user\models\Role $role
 */

$module = $this->context->module;
$controller = $this->context;
$user = $module->model("User");
$role = $module->model("Role");
$this->title = Yii::t('user', 'Users');
$this->params['breadcrumbs'][] = $this->title;
?>



<?php Pjax::begin(['options'=>['class'=>'box pad15']]); ?>
<div class="box-header with-border">
    <h3 class="box-title"><i class="fa fa-user"></i><span class="break">用户管理</span></h3>
</div>
<div class="box-body pad table-responsive">
        <?php  $btns  = ' '.Html::a('创建', ['create'], [ 'title'=>'创建用户','role'=>'modal-remote','class' => 'btn btn-success user-add']) ?>
        <?php  $btns .= ' '.Html::a('激活', "javascript:void(0);", ['class' => 'btn btn-primary batch-active']) ?>
        <?php  $btns .= ' '.Html::a('取消激活', "javascript:void(0);", ['class' => 'btn btn-primary batch-inactive']) ?>
        <?php  $btns .= ' '.Html::a('封号', "javascript:void(0);", ['class' => 'btn btn-info batch-banned']) ?>
        <?php  $btns .= ' '.Html::a('取消封号', "javascript:void(0);", ['class' => 'btn btn-info batch-unbanned']) ?>
        <?php  $btns .= ' '.BulkButtonWidget::widget([
                'buttons'=>Html::a('删除', ["deletes"] ,
                    [
                        "class"=>"btn btn-danger",
                        'role'=>'modal-remote-bulk',
                        'data-confirm'=>false, 'data-method'=>false,// for overide yii data api
                        'data-request-method'=>'post',
                        'data-confirm-title'=>'确认操作',
                        'data-confirm-message'=>'你确定要执行删除操作吗？'
                    ]),
            ]); ?>
        <?php

$before = $btns;

$panelFooterTemplate=<<< HTML
{summary}<div class="kv-panel-pager">{pager}</div>
    <div class="clearfix"></div>
HTML;

        $content = GridView::widget([
            'pjax'=>true,
            'hover' => true,
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'panel'=>[
                'before'=>$before,
                'after' => false
            ],
            'panelTemplate'=>"{panelBefore}\n{items}\n{panelFooter}",
            'toolbar' =>  false,
            'panelFooterTemplate' => $panelFooterTemplate,
            'options'=>['id'=>'dynagrid-user'], // a unique identifier is important
            'columns' => [
                [
                    'class' => '\kartik\grid\CheckboxColumn',
                    'multiple' => true,
                    'name' => 'selection',
                    'filterOptions'=>['style'=>'width:30px']
                ],

                [
                    'attribute' => 'id',
                    'filterOptions'=>['style'=>'width:50px']
                ],
                [
                    'attribute' => 'username',
                    'format'=>'raw',
                    'value' => function($model)use($controller){
                        return $model->username.($model->id == $controller->superadmin_uid ? "<span data-toggle='tooltip' data-original-title='超级管理员' class='label label-success'>超</span>" :"");
                    },
                    'filterOptions'=>['style'=>'width:100px']
                ],
                [
                    'attribute'=>'email',
                    'options'=>['style'=>'width:120px']
                ],
                //'profile.full_name',
                //'profile.timezone',
                [
                    'attribute'=>'created_at',
                    'options'=>['style'=>'width:130px']
                ],
                [
                    'class' => EditableColumn::class,
                    'url' => ['change-role'],
                    'type' => 'select',
                    'editableOptions' => function ($model) use($role,$controller){
                        return [
                            'source' => $role::dropdown(),
                            'value' => $model->role_id,
                        ];
                    },
                    'attribute' => 'role_id',
                    'label' => Yii::t('user', 'Role'),
                    'filter' => $role::dropdown(),
                    'value' => function($model, $index, $dataColumn) use ($role) {
                        $roleDropdown = $role::dropdown();
                        return $roleDropdown[$model->role_id];
                    },
                    'options'=>['style'=>'width:80px']
                ],
                [
                    'label'=>'激活',
                    'class' => EditableColumn::class,
                    'url' => ['change-status'],
                    'type' => 'select',
                    'editableOptions' => function ($model) use($user,$controller){
                        $source = $user::statusDropdown();
                        krsort($source);//如果不倒序排列,source序列化会变成数组而不是对象
                        return [
                            'source' => $source,
                            'value' => $model->status,
                        ];
                    },
                    'attribute' => 'status',
                    'label' => Yii::t('user', 'Status'),
                    'filter' => $user::statusDropdown(),
                    'value' => function($model, $index, $dataColumn) use ($user) {
                        $statusDropdown = $user::statusDropdown();
                        return $statusDropdown[$model->status];
                    },
                    'options'=>['style'=>'width:100px']
                ],
//                [
//                    'label'=>'封号',
//                    'class' => 'kartik\grid\EnumColumn',
//                    'attribute' => 'banned_at',
//                    'value'=>function($model){
//                        return $model->banned_at ? "禁用" : "正常";
//                    },
//                    'filter'=>[
//                            '0' => '禁用',
//                            '1' => '正常'
//                    ],
//                    'enum' => [0=>"禁用",1=>"正常"], // returns a value => content pair
//                    'loadEnumAsFilter' => true, // optional - defaults to `true`
//                    'options'=>['style'=>'width:40px']
//                ],
                [
                    'label'=>'禁用',
                    'attribute'=>'banned_at',
                    'filter'=>[
                        '0' => '禁用',
                        '1' => '正常'
                    ],
                    'value'=>function($model){
                        return $model->banned_at ? "禁用" : "正常";
                    },
                    'options'=>['style'=>'width:40px']
                ],

                // 'password',
                // 'auth_key',
                // 'access_token',
                // 'logged_in_ip',
                // 'logged_in_at',
                // 'created_ip',
                // 'updated_at',
                // 'banned_at',
                // 'banned_reason',

                [
                    'class' => 'kartik\grid\ActionColumn',
                    'dropdown' => false,
                    'vAlign'=>'middle',
                    'urlCreator' => function($action, $model, $key, $index) {
                        $pks = [];
                        $primaryKeys = $model->primaryKey();
                        if(count($primaryKeys) === 1){
                            $pks[$primaryKeys[0]] = $key;
                        }else{
                            $pks = $key;
                        }
                        return Url::to(array_merge([$action],$pks));
                    },

                    //动作栏按钮设定（默认为：查看，禁用，删除）
                    'template' => \openadm\kartikcrud\Helper::filterActionColumn(['view','update', 'delete']),
                    'buttons' => [
                        'activate' => function($url, $model) {
                            if ($model->status == 1) {
                                return '';
                            }
                            $options = [
                                'role'=>'modal-remote',
                                "class" => "btn btn-primary btn-xs",
                                'title'=>'启用',
                                'data-confirm'=>false,
                                'data-method'=>false,// for overide yii data api
                                'data-request-method'=>'post',
                                'data-toggle'=>'tooltip',
                                'data-confirm-title'=>'确认操作',
                                'data-confirm-message'=>'你确定要启用吗？'
                            ];
                            return Html::a('<i class="glyphicon glyphicon-ok" ></i>', $url, $options);
                        },
                        'inactivate' => function($url, $model) {
                            if ($model->status == 0 || $model->status == null) {
                            return '';
                            }
                            $options = [
                                "class" => "btn btn-primary btn-xs",
                                'role'=>'modal-remote',
                                'title'=>'禁用',
                                'data-confirm'=>false,
                                'data-method'=>false,// for overide yii data api
                                'data-request-method'=>'post',
                                'data-toggle'=>'tooltip',
                                'data-confirm-title'=>'确认操作',
                                'data-confirm-message'=>'你确定要禁用吗？'
                            ];
                            return Html::a('<i class="glyphicon glyphicon-lock" ></i>', $url, $options);
                        },
                    ],
                    'options'=>['style'=>'width:80px'],
                    'viewOptions'=>['role'=>'modal-remote','title'=>'查看',"class"=>"btn btn-default btn-xs",'data-toggle'=>'tooltip'],
                    'updateOptions'=>['role'=>'modal-remote','title'=>'更新',"class"=>"btn btn-info btn-xs", 'data-toggle'=>'tooltip'],
                    'deleteOptions'=>['role'=>'modal-remote','title'=>'删除',
                        "class"=>"btn btn-danger btn-xs",
                        'data-confirm'=>false, 'data-method'=>false,// for overide yii data api
                        'data-request-method'=>'post',
                        'data-toggle'=>'tooltip',
                        'data-confirm-title'=>'确认操作',
                        'data-confirm-message'=>'你确定要删除该记录吗？'],
                ],
            ],
        ]);
        echo $content;
        ?>
</div>
<?php Pjax::end(); ?>

<?php
$this->registerJs('
function oa_action(action,status,tips){
    $("#dynagrid-user").yiiGridView("setSelectionColumn",{name:"selection[]"});
    var keys = $("#dynagrid-user").yiiGridView("getSelectedRows");
    if(keys.length==0){
        oa.Noty({text: "请至少选择一条数据!",type:\'warning\'});
        return ;
    }
    if(tips == ""){
        $.ajax({
                url: action,
                type: \'post\',
                data: {ids:keys,status:status,_csrf:"'.Yii::$app->request->csrfToken.'"},
                success: function (data) {
                    // do something
                    if(data["code"] == 200){
                        oa.Noty({text: data.msg,type:\'success\'});
                        setTimeout(function(){location.href=oa_timestamp(location.href);},1500);
                    }else{
                        oa.Noty({text: data.msg,type:\'error\',timeout:1000});
                    }
                }
            });
    }else{
        yii.confirm(tips,function(){
            $.ajax({
                url: action,
                type: \'post\',
                data: {ids:keys,status:status,_csrf:"'.Yii::$app->request->csrfToken.'"},
                success: function (data) {
                    // do something
                    if(data["code"] == 200){
                        oa.Noty({text: data.msg,type:\'success\'});
                        setTimeout(function(){location.href=oa_timestamp(location.href);},1500);
                    }else{
                        oa.Noty({text: data.msg,type:\'error\',timeout:1500});
                    }
                }
            });
        });
    }
}
$(".batch-active").on("click", function () {
    oa_action("/user/admin/active",1,"");
});
$(".batch-inactive").on("click", function () {
    oa_action("/user/admin/active",0,"");
});
$(".batch-banned").on("click", function () {
    oa_action("/user/admin/banned",1,"");
});
$(".batch-unbanned").on("click", function () {
    oa_action("/user/admin/banned",0,"");
});
');
