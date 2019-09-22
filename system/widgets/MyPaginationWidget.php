<?php
namespace system\widgets;

use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\data\Pagination;

/**
 * Created by PhpStorm.
 * User: ligang
 * Date: 2017/3/9
 * Time: 上午11:14
 */
class MyPaginationWidget extends Widget
{
    public $contId = 'paged';
    public $pagination = null;
    public $jump = null;
    public $showText = true;// 显示整体描述文本
    public $showPage = true; // 显示分页
    public $theme = false;   // 主题

    public function init()
    {
        parent::init();
        if (!$this->pagination instanceof Pagination) {
            throw new InvalidConfigException('请配置pagination为有效的分页类');
        }
    }

    public function run()
    {
        parent::run();
        return $this->render('my-pagination', [
            'pagination' => $this->pagination, // 数据分页类
            'contId' => $this->contId, // 容器id
            'jump' => $this->jump, // 跳转后的回调
            'showText' => $this->showText,
            'showPage' => $this->showPage,
            'theme' => $this->theme, // 主题
        ]);
    }

}