<?php
/**
 * 修改action的默认样式
 */
namespace yikaikeji\openadm\grid;

use Yii;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn as GridActionColumn;

class ActionColumn extends GridActionColumn
{
    /**
     * @var string the template used for composing each cell in the action column.
     * Tokens enclosed within curly brackets are treated as controller action IDs (also called *button names*
     * in the context of action column). They will be replaced by the corresponding button rendering callbacks
     * specified in [[buttons]]. For example, the token `{view}` will be replaced by the result of
     * the callback `buttons['view']`. If a callback cannot be found, the token will be replaced with an empty string.
     *
     * As an example, to only have the view, and update button you can add the ActionColumn to your GridView columns as follows:
     *
     * ```php
     * ['class' => 'yii\grid\ActionColumn', 'template' => '{view} {update}'],
     * ```
     *
     * @see buttons
     */
    public $template = '{view} {update} {delete}';

    public function getName($name)
    {
        $names = [
            'view' => '',
            'update' => '',
            'delete' => ''
        ];
        return isset($names[$name]) ? $names[$name] : '';
    }

    /**
     * Initializes the default button rendering callbacks.
     */
    protected function initDefaultButtons()
    {
        $this->initDefaultButton('view', 'eye',['class'=>'btn btn-sm btn-primary']);
        $this->initDefaultButton('update', 'edit',['class'=>'btn btn-sm btn-success']);
        $this->initDefaultButton('delete', 'trash', [
            'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
            'data-method' => 'post',
            'class'=>'btn btn-sm btn-danger'
        ]);
    }

    /**
     * Initializes the default button rendering callback for single button
     * @param string $name Button name as it's written in template
     * @param string $iconName The part of Bootstrap glyphicon class that makes it unique
     * @param array $additionalOptions Array of additional options
     * @since 2.0.11
     */
    protected function initDefaultButton($name, $iconName, $additionalOptions = [])
    {
        if (!isset($this->buttons[$name]) && strpos($this->template, '{' . $name . '}') !== false) {
            $this->buttons[$name] = function ($url, $model, $key) use ($name, $iconName, $additionalOptions) {
                $title = Yii::t('yii', ucfirst($name));
                $options = array_merge([
                    'title' => $title,
                    'aria-label' => $title,
                    'data-pjax' => '0'
                ], $additionalOptions, $this->buttonOptions);
                $icon = Html::tag('i', $this->getName($name), ['class' => "fa fa-$iconName"]);
                return Html::a($icon, $url, $options);
            };
        }
    }

}
