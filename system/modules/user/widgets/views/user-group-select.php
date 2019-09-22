<?php
/**
 * Created by PhpStorm.
 * User: Cold_heart
 * Date: 2017/10/15
 * Time: 14:05
 */

/** @var \yii\web\View $this */
/** @var string $defaultValue */
/** @var string $inputName */
/** @var string $label */
/** @var string $title */
/** @var integer $select_max */
/** @var string $show_page */
/** @var string $show_range */
/** @var integer $range_type */
/** @var string $select_type */
/** @var string $required */

use system\modules\user\components\UserWithGroup;
use yii\helpers\Html;

$assets = \system\modules\user\assets\UserSelectAsset::register($this);
?>
<script>
	var baseUrl = '<?= $assets->baseUrl ?>';
</script>
<div class="weui-panel weui-panel_access select-user">
    <div class="weui-media-box">
        <div class="weui-uploader__hd font14">
            <p class="weui-uploader__title"><?= $label ?> <span class="weui-uploader__info font12">(点击头像删除)</span></p>
        </div>
        <ul class="user-item-box clearfix">
            <input type="hidden" name="<?= $inputName ?>" class="user-group-select" value="<?= $defaultValue ?>" <?= $required ? 'data-verify="required"' : '' ?>/>
            <?php
            $list = UserWithGroup::getSelectInfo($defaultValue);
            foreach ($list as $key => $val) {
                if (substr($key, 0, 1) == 'G') {//显示已选择部门
                    echo Html::tag('li',
                        Html::img('/static/images/icon/icon-department.png') . Html::tag('p', $val['name']),
                        ['class' => 'selected-item', 'data-id' => $key, 'onclick' => "delItem(this)"]
                    );
                } elseif (substr($key, 0, 1) == 'P') {//显示已选择职位
                    echo Html::tag('li',
                        Html::img('/static/images/icon/icon-position.png') . Html::tag('p', $val['name']),
                        ['class' => 'selected-item', 'data-id' => $key, 'onclick' => "delItem(this)"]
                    );
                } elseif (substr($key, 0, 1) == 'U') {//显示已选择人员
                    echo Html::tag('li',
                        Html::img($val['avatar']) . Html::tag('p', $val['realname']),
                        ['class' => 'selected-item', 'data-id' => $key, 'onclick' => "delItem(this)"]
                    );
                }
            }
            ?>
            <span class="add-members iconfont icon-hollow-add" data-title="<?= $title ?>"
                data-select_max="<?= $select_max ?>"
                data-show_page="<?= $show_page ?>"
                data-show_range="<?= $show_range ?>"
                data-range_type="<?= $range_type ?>"
                data-select_type="<?= $select_type ?>"
            ></span>
        </ul>
    </div>
</div>
