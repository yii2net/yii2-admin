<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;

/**
 * @var yii\web\View $this
 * @var amnah\yii2\user\Module $module
 * @var amnah\yii2\user\models\User $user
 * @var amnah\yii2\user\models\Profile $profile
 * @var amnah\yii2\user\models\Role $role
 * @var yii\widgets\ActiveForm $form
 */

$module = $this->context->module;
$role = $module->model("Role");
?>

<div class="user-form">

    <?php $form = ActiveForm::begin([],['create'],[]); ?>
    <table class="table table-hover table-bordered table-striped detail-view">
        <tbody>
        <tr>
            <th style='width: 20%; text-align: right; vertical-align: middle;'>
                <?= Html::activeLabel($user, 'email') ?></th>
            <td>
                <div class='kv-form-attribute'>    <?= $form->field($user, 'email',['showLabels'=>false])->textInput() ?>

                </div>
            </td>
        </tr>

        <tr>
            <th style='width: 20%; text-align: right; vertical-align: middle;'>
                <?= Html::activeLabel($user, 'username') ?></th>
            <td>
                <div class='kv-form-attribute'>    <?= $form->field($user, 'username',[
                        'showLabels'=>false
                    ])->textInput() ?>

                </div>
            </td>
        </tr>

        <tr>
            <th style='width: 20%; text-align: right; vertical-align: middle;'>
                <?= Html::activeLabel($user, 'newPassword') ?></th>
            <td>
                <div class='kv-form-attribute'>    <?= $form->field($user, 'newPassword',[
                        'showLabels'=>false
                    ])->passwordInput() ?>

                </div>
            </td>
        </tr>

        <tr>
            <th style='width: 20%; text-align: right; vertical-align: middle;'>
                <?= Html::activeLabel($profile, 'full_name') ?></th>
            <td>
                <div class='kv-form-attribute'>    <?= $form->field($profile, 'full_name',[
                        'showLabels'=>false
                    ])->textInput() ?>

                </div>
            </td>
        </tr>

        <tr>
            <th style='width: 20%; text-align: right; vertical-align: middle;'>
                <?= Html::activeLabel($user, 'role_id') ?></th>
            <td>
                <div class='kv-form-attribute'>
    <?php
        $role_id = Yii::$app->request->get('role_id','');
        if(!empty($role_id)){
            echo  $form->field($user, 'role_id',['labelOptions'=>['style'=>'display:none']])->hiddenInput(['value'=>$role_id]);
        }else{
            echo $form->field($user, 'role_id',['showLabels'=>false])->widget(kartik\select2\Select2::classname(), [
                'data' => $role::dropdown(),
                'options' => ['placeholder' => 'Select a state ...'],
                'theme' => kartik\select2\Select2::THEME_DEFAULT,
                'value' => 1,
                'hideSearch' => true,
                'pluginOptions' => [
                    //'allowClear' => true ,
                    'multiple' => false
                ],
            ]);
        }
    ?>
                </div>
            </td>
        </tr>

        <tr>
            <th style='width: 20%; text-align: right; vertical-align: middle;'>
                <?= Html::activeLabel($user, 'status') ?></th>
            <td>
                <div class='kv-form-attribute'>
    <?= $form->field($user, 'status',['showLabels'=>false])->widget(kartik\select2\Select2::classname(), [
        'data' => $user::statusDropdown(),
        'options' => ['placeholder' => 'Select a state ...'],
        'theme' => kartik\select2\Select2::THEME_DEFAULT,
        'hideSearch' => true,
        'pluginOptions' => [
            //'allowClear' => true ,
            'multiple' => false
        ],
    ]); ?>

                </div>
            </td>
        </tr>


    <?php // use checkbox for banned_at ?>
    <?php // convert `banned_at` to int so that the checkbox gets set properly ?>
    <?php $user->banned_at = $user->banned_at ? 1 : 0 ?>
        <tr>
            <th style='width: 20%; text-align: right; vertical-align: middle;'>
                <?= Html::activeLabel($user, 'banned_at', ['label' => Yii::t('user', 'Banned')]); ?>
            </th>
            <td>
                <div class='kv-form-attribute'>
                <?= $form->field($user, 'banned_at',[
                            'showLabels'=>false
                        ])->widget(kartik\checkbox\CheckboxX::classname(), [
                    'pluginOptions'=>['threeState'=>false]
                ]) ?>
                </div>
            </td>
        </tr>

        <tr>
            <th style='width: 20%; text-align: right; vertical-align: middle;'>
                <?= Html::activeLabel($user, 'banned_reason', ['label' => Yii::t('user', 'Banned')]); ?>
            </th>
            <td>
                <div class='kv-form-attribute' style="margin-bottom: 14px;">
                <?= $form->field($user, 'banned_reason',['showLabels'=>false])->textarea(['size'=>3]); ?>
                </div>
            </td>
        </tr>

    <?php if (!Yii::$app->request->isAjax): ?>
    <tr>
        <th style='width: 20%; text-align: right; vertical-align: middle;'>

        </th>
        <td>
            <div class='kv-form-attribute'>

        <?= Html::submitButton($user->isNewRecord ? Yii::t('user', 'Create') : Yii::t('user', 'Update'), ['class' => $user->isNewRecord ? 'btn btn-primary' : 'btn btn-primary']) ?>
        <?php echo Html::a(Yii::t('user', 'Cancel'),['index'], ['class' => 'btn btn-default']); ?>

            </div>
        </td>
    </tr>
    <?php endif;?>
        </tbody>
    </table>
    <?php ActiveForm::end(); ?>

</div>
