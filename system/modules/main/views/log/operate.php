<?php
/**
 * Created by PhpStorm.
 * User: ligang
 * Date: 2017/3/7
 * Time: 上午10:13
 */

/** @var \yii\web\View $this */
/** @var array $logs */
/** @var array $params */
/** @var \yii\data\Pagination $pagination */

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use system\modules\main\models\OperateLog;

//$log_types = Yii::$app->systemConfig->getValue('LOG_TYPE_LIST', []);
//
//// 搜索关键字
//$keyword = Yii::$app->request->get('keyword');
//// 当前组
//$type = Yii::$app->request->get('type');

$this->title = '操作日志';

?>

<style>
    .layui-layer-tips {width: inherit!important;}
    .content-tip th {font-weight: bold;}
    .content-tip th, .content-tip td {padding: 5px 10px; border: 1px solid #e2e2e2; text-align: left;}
    .content-tip th.label-tip, .content-tip td.label-tip {text-align: center;}
</style>

<form class="layui-form custom-form" action="" method="get">
	<div class="layui-row layui-col-space10">
		<div class="layui-col">
			<div class="layui-inline width-210">
				<input type="text" class="layui-input" name="search" placeholder="搜索内容、操作人、操作IP" value="<?= ArrayHelper::getValue($params, 'search') ?>"></div>
		</div>
		<div class="layui-col">
			<div class="layui-inline width-240">
				<input type="text" class="layui-input date" name="opt_time" data-range="true" placeholder="操作时间" value="<?= ArrayHelper::getValue($params, 'opt_time') ?>">
			</div>
		</div>
		<div class="layui-col">
			<div class="layui-inline width-150">
				<?= Html::dropDownList('type', ArrayHelper::getValue($params, 'type'), OperateLog::getAttributesList('action_type'), [
					'prompt' => '选择操作类型',
					'lay-filter' => 'search'
				])?>
			</div>
		</div>
		<div class="layui-col">
			<div class="layui-inline width-150">
				<?= Html::dropDownList('module', ArrayHelper::getValue($params, 'module'), OperateLog::getAttributesList('module'), [
					'prompt' => '请选择模块',
					'lay-filter' => 'search'
				])?>
			</div>
		</div>
		<div class="layui-col">
			<button type="submit" class="layui-btn">搜索</button>
		</div>
	</div>
</form>
<div class="separate-10 mb20"></div>
<div class="ibox float-e-margins">
    <div class="ibox-title">
        <h5>操作日志</h5>
    </div>
    <div class="ibox-content" style="padding-top: 0">
        <ul class="feed-activity-list">
            <?php foreach ($logs as $one):?>
                <li class="feed-element clearfix">
                    <div class="avatar">
                        <div class="avatar-box-left">
                            <?php
                            if ($one->operatorInfo) {
                                echo "<img class='avatar-mini img-circle' src='{$one->operatorInfo->avatar}' /> ";
                            } else {
                                echo "<img class='avatar-mini img-circle' src='/static/images/avatar/default/system.png' /> ";
                            }
                            ?>
                        </div>
                        <div class='avatar-box-right'>
                            <div class="comment-box">
                                <p class="name">
                                    <?php
                                    if ($one->operatorInfo) {
                                        echo $one->operatorInfo->realname;
                                    } else {
                                        echo "系统";
                                    }
                                    ?>
                                    {<?= OperateLog::getAttributesList('module', $one->module, $one->module)?>}</p>
                                <span><?= date('Y-m-d H:i:s', $one->opt_time)?></span>
                                <span><?= $one->opt_ip?></span>
                                <div class="comment word-break">
                                    <?= $one->logContent?>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
            <?php endforeach;?>
        </ul>
    </div>
</div>

<?= \system\widgets\MyPaginationWidget::widget([
    'pagination' => $pagination,
]) ?>

