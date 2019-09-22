<?php
/**
* Created by PhpStorm.
* User: Cold_heart
* Date: 2017/8/19
* Time: 15:30
*/

/** @var \yii\web\View $this */
/** @var array $list */

use yii\helpers\Html;

\system\modules\main\assets\FileInfoAssets::register($this);

$this->title = '附件列表';
?>
<style>
    .custom-table td .layui-table-cell {height: 42px;line-height: 42px;}
    .custom-table td .layui-table-cell a {display: block; color: #1988fa;}
    .custom-table td .layui-table-cell a:hover {color: #1988fa;}
</style>
<div class="layui-tab layui-tab-brief">
	<ul class="layui-tab-title">
		<li class="layui-this"><?= $this->title ?></li>
	</ul>
</div>
<div class="layui-row">
	<?= \system\widgets\GridViewWidget::widget([
		'data' => $list,//数据
		'model' => '\system\modules\main\models\Fileinfo',//模型类
		'search' => [//查询字段
			'items' => [
				[
					'type' => 'input',
					'name' => 'search',
                    'label' => '',
                    'placeholder' => '请输入文件名、来源'
				],
				[
					'type' => 'input',
                    'class' => 'width-180',
					'name' => 'upload_user',
                    'inputOption' => [
                        'class' => 'user-group-select',
                        'data-select_type' => 'user',
                        'data-select_max' => '1',
                    ]
				],
				[
					'class' => 'width-240',
					'type' => 'date-range',
					'name' => 'upload_time',
				]
			],
		],//查询
		'columns' => [//列表信息
			'file_type' => [100, 'custom' => function ($val) {
                return Html::img('/static/images/filetype/' . $val . '_lt.png', ['style' => 'height: 40px;']);
			}],
			'name' => ['custom' => function ($val) {
                return Html::a($val['name'], $val['src'], ['download' => $val['name']]);
			}, 'paramsType' => 'array'],
			'source' => [120],
			'size' => [100],
			'upload_time' => [180, 'date', ['format' => 'Y-m-d H:i:s']],
			'upload_user' => [120, 'custom', [\system\modules\user\models\User::className(), 'getInfo']],
		],//列参数
	]); ?>
</div>

