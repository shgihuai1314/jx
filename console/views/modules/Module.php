<?php

echo "<?php\n";
?>


namespace system\modules\<?= $module_id ?>;


class Module extends \yii\base\Module
{
	public $controllerNamespace = 'system\modules\<?= $module_id ?>\controllers';

	public function init()
	{
		parent::init();

		// custom initialization code goes here
	}
}
