<?php

use yii\bootstrap\Html;
use yii\helpers\Url;
use yii2mod\editable\EditableColumn;
use yii\widgets\Pjax;
use yii\bootstrap\Tabs;
use kartik\dynagrid\DynaGrid;
use yii\bootstrap\Button;
use yii\bootstrap\ButtonGroup;
use yii\bootstrap\Modal;
use xiongchuan86\kartikcrud\CrudAsset;

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
        <?php  $btns .= ' '.Html::a('删除', "javascript:void(0);", ['class' => 'btn btn-danger batchdelete']) ?>
        <?php

$before = $btns;

$panelFooterTemplate=<<< HTML
{summary}<div class="kv-panel-pager">{pager}</div>
    <div class="clearfix"></div>
HTML;

        $content = DynaGrid::widget([
            'storage'=>DynaGrid::TYPE_COOKIE,
            'theme'=>'panel-default',
            'allowThemeSetting' => false,
            'allowFilterSetting' => false,
            'allowPageSetting' => false,
            'allowSortSetting' => true,
            'gridOptions'=>[
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
                'panelFooterTemplate' => $panelFooterTemplate
            ],
            'options'=>['id'=>'dynagrid-user'], // a unique identifier is important
            'columns' => [
                [
                    'class' => '\kartik\grid\CheckboxColumn',
                    'multiple' => true,
                    'name' => 'uid',
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
                    'options'=>['style'=>'width:180px']
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
                    'options'=>['style'=>'width:80px']
                ],
                [
                    'label'=>'禁用',
                    'attribute'=>'banned_at',
                    'filter'=>'',
                    'value'=>function($model){
                        return $model->banned_at ? "禁用" : "正常";
                    },
                    'options'=>['style'=>'width:80px']
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
                        return Url::to([$action,'id'=>$key]);
                    },

                    //动作栏按钮设定（默认为：查看，禁用，删除）
                    'template' => \xiongchuan86\kartikcrud\Helper::filterActionColumn(['view','activate','inactivate', 'delete']),
                    'buttons' => [
                        'activate' => function($url, $model) {
                            //if ($model->status == 1) {
                            //    return '';
                            //}
                            $options = [
                                'role'=>'modal-remote',
                                'title'=>'启用',
                                'data-confirm'=>false,
                                'data-method'=>false,// for overide yii data api
                                'data-request-method'=>'post',
                                'data-toggle'=>'tooltip',
                                'data-confirm-title'=>'确认操作',
                                'data-confirm-message'=>'你确定要启用吗？'
                            ];
                            return Html::a('<span class="glyphicon glyphicon-ok"></span>', $url, $options);
                        },
                        'inactivate' => function($url, $model) {
                            //if ($model->status == 0) {
                            //return '';
                            //}
                            $options = [
                                'role'=>'modal-remote',
                                'title'=>'禁用',
                                'data-confirm'=>false,
                                'data-method'=>false,// for overide yii data api
                                'data-request-method'=>'post',
                                'data-toggle'=>'tooltip',
                                'data-confirm-title'=>'确认操作',
                                'data-confirm-message'=>'你确定要禁用吗？'
                            ];
                            return Html::a('<span class="glyphicon glyphicon-cog"></span>', $url, $options);
                        },
                    ],
                    'viewOptions'=>['role'=>'modal-remote','title'=>'查看及修改','data-toggle'=>'tooltip'],
                    //'updateOptions'=>['role'=>'modal-remote','title'=>'更新', 'data-toggle'=>'tooltip'],
                    'deleteOptions'=>['role'=>'modal-remote','title'=>'删除',
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
    $("#dynagrid-user").yiiGridView("setSelectionColumn",{name:"uid[]"});
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
                        setTimeout(function(){location.href=oa_timestamp(location.href);},1000);
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
                        setTimeout(function(){location.href=oa_timestamp(location.href);},1000);
                    }else{
                        oa.Noty({text: data.msg,type:\'error\',timeout:1000});
                    }
                }
            });
        });
    }
}
$(".batchdelete").on("click", function () {
    oa_action("/user/admin/deletes",1,"确定要删除?");
});
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

?>

<?php Modal::begin([
    "id"=>"ajaxCrudModal",
    "footer"=>"",// always need it for jquery plugin
    "options" =>['data-backdrop '=>'static','data-keyboard'=>'false','z-index'=>'-999'],
])?>
<?php Modal::end(); ?>

<?php
//引入js放在最后，应为_column.php引入了kv-checkbox.js,为了让别最后引入改写后的：kv-grid-checkbox-fix.js放在最后
CrudAsset::register($this);

