<?php

use kartik\dynagrid\DynaGrid;
use kartik\grid\GridView;
use Yii;
use yii\bootstrap\Html;
use yii\data\ArrayDataProvider;
use yii\bootstrap\Tabs;
use yii\bootstrap\Button;
use yii\bootstrap\ButtonGroup;
use kartik\select2\Select2;

$data = array();
if(is_array($result) && isset($result['list'])){
    foreach($result['list'] as $v){
        if(is_array($v)){
            $author = "开发者：".substr($v['author']['name'],1);
            $v['description'] = mb_substr($v['description'], 0,"255");
            if($author){
                $v['description'] .= "<br/>".$author;
            }
            $v['version'] = join(',',$v['versions']);
            $v['status'] = 'unsetup';
            //增加操作类型
            $btn_setup_label = Html::tag('i','安装',['class'=>'fa fa-cog']);
            $btn_setup = Html::a($btn_setup_label,'#',['class' => 'setup btn btn-xs btn-primary','style'=>'','data-toggle' => 'modal','data-title'=>$v['name'],'data-version'=>$v['version'],'data-target'=>'#install-modal']);

            $btn_unsetup_label = Html::tag('i','卸载',['class'=>'fa fa-edit']);
            $btn_unsetup = Html::a($btn_unsetup_label,'#',['class' => 'unsetup btn btn-xs btn-success','style'=>'','data-toggle' => 'modal','data-title'=>$v['name'],'data-version'=>$v['version'],'data-toggle'=>'#modal']);

            $btn_delete_label = Html::tag('i','删除',['class'=>'fa fa-trash']);
            $btn_delete = Html::a($btn_delete_label,'#',['class' => 'delete btn btn-xs btn-danger','style'=>'','data-title'=>$v['name'],'data-toggle' => 'modal','data-version'=>$v['version'],'data-toggle'=>'#modal']);
            $v['_action_'] = '';
            if($v['status']=='setuped'){
                $v['_action_'] = $btn_unsetup;
            }else {
                $v['_action_'] = $btn_setup.' '.$btn_delete;
            }
            $data[]=$v;
        }

    }
}

$gridDataProvider = new ArrayDataProvider([
    'allModels' => $data,
    'sort' => [
        //'attributes' => ['name', 'prettyName', 'email'],
    ],
    'pagination' => [
        'pageSize' => $result['pageSize'],
        'page' => $result['page']
    ],
]);
//,'onclick'=>'extension_action(this,"setup")'
$gridDataProvider->setTotalCount($result['total']);
$gridDataProvider->getPagination()->pageSize = $result['pageSize'];

$classes = [
    'all' => 'btn btn-info '. ($tab == 'all' ? 'active' : ''),
    'setuped' => 'btn btn-info '. ($tab == 'setuped' ? 'active' : ''),
    'downloaded' => 'btn btn-info '. ($tab == 'downloaded' ? 'active' : '')
];
$before = "<div class='row' style='margin-left: 0'>";
$before .= "<div class='col-xs-2' style='padding-left: 0;padding-right: 10px;'>";
$before .= Select2::widget([
    'name' => 'ext-category',
    'attribute' => 'category',
    'data' => $categories,
    'theme'=>Select2::THEME_DEFAULT,
    'hideSearch'=>true,
    'options' => [
        'placeholder' => '选择一个分类',
        'multiple' => false
    ],
    'pluginOptions' => [
        'allowClear' => false
    ],
]);
$before .= "</div>";
$before .= <<<HTML
<div class='col-xs-3' style="padding-left: 0">
    <div class="input-group">
        <input type="text" class="form-control" placeholder="输入包名">
        <span class="input-group-btn">
          <button type="button" class="btn btn-info btn-flat">搜索</button>
        </span>
    </div>
</div>
HTML;
$before .= "</div>";

$panelFooterTemplate=<<< HTML
{summary}<div class="kv-panel-pager">{pager}</div>
    <div class="clearfix"></div>
HTML;

$content = DynaGrid::widget([
    'columns' => array(
        //['class'=>'kartik\grid\CheckboxColumn', 'order'=>DynaGrid::ORDER_FIX_LEFT],
        array('attribute'=>'name', 'header'=>'包名','options'=>array('style'=>'width:15%','class'=>'extensionid')),
        array('attribute'=>'version', 'header'=>'版本','options'=>array('style'=>'width:10%')),
        array('attribute'=>'prettyName', 'header'=>'名称','options'=>array('style'=>'width:15%')),
        array('attribute'=>'extType', 'header'=>'类型','options'=>array('style'=>'width:5%')),
        array('attribute'=>'description', 'header'=>'描述','format' => 'raw','options'=>array('style'=>'width:40%')),
        array('attribute'=>'_action_','header'=>'操作','format' => 'raw','options'=>array('style'=>'width:30%')),
    ),
    'storage'=>DynaGrid::TYPE_COOKIE,
    'theme'=>'panel-default',
    'allowThemeSetting' => false,
    'allowFilterSetting' => false,
    'allowPageSetting' => false,
    'allowSortSetting' => true,
    'gridOptions'=>[
        'hover' => true,
        'dataProvider' => $gridDataProvider,
        'panel'=>[
            'before'=>$before,
            'after' => false
        ],
        'panelTemplate'=>"{panelBefore}\n{items}\n{panelFooter}",
        'toolbar' => false,
        'panelFooterTemplate' => $panelFooterTemplate
    ],
    'options'=>['id'=>'dynagrid-ext-store'] // a unique identifier is important
]);
?>
<div class="nav-tabs-custom">
<?=  Tabs::widget([
    'items' => [
        [
            'label' =>  "本地扩展",
            'url'=>['local']
        ],
        [
            'label' => '扩展商店',
            'url'=>['store'],
            'content'=> $content,
            'active' => true
        ]
    ],
]);
?>
</div>
