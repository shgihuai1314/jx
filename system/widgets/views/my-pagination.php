<?php
/* @var $contId string 分页的容器id  */
/* @var $pagination \yii\data\Pagination 数据分页类  */
/* @var $this \yii\base\Widget */
/* @var $showText bool */
/* @var $showPage bool */
/* @var $jump string */

// 生成url
$params = Yii::$app->request->getQueryParams();
if (isset($params['page'])) unset($params['page']);
$params[0] = Yii::$app->controller->getRoute();
$url = Yii::$app->urlManager->createUrl($params);
if (strpos($url, '?') === false) {
    $url .= '?';
} else {
    $url .= '&';
}

if ($jump == null) {
    $jump = "function (obj, first) {
                // 如果不是首页，点击后跳转
                if (!first) {
                    location.href = '{$url}page='+obj.curr;
                }
            }";
}

?>

<style>
    .systemPage .layui-laypage span {
        background-color: transparent;
    }
</style>

<div class="systemPage">
    <?php if ($showText):?>
        <div>总计：<?= $pagination->totalCount?>条记录 共<?= $pagination->getPageCount()?>页，每页显示<?= $pagination->defaultPageSize?>条</div>
    <?php endif;?>
    <?php if ($showPage && $pagination->getPageCount()>1):?>
        <div id="<?= $contId?>" class="page"></div>
    <?php endif;?>
</div>

<script type="text/javascript">
    layui.laypage.render({
        elem: '<?= $contId?>' // 分页容器
        , count: <?= $pagination->totalCount?> // 总记录数
        , limit: <?= $pagination->limit ?> // 每页显示记录
        , curr: <?= $pagination->page+1 ?> // 当前页码
        , groups: 5 //连续显示分页数
        , layout: ['prev', 'page', 'next', 'skip']
        <?php if ($theme):?>
        , theme: '<?= $theme?>'
        <?php endif;?>
        , jump: <?= $jump?>
    });
</script>