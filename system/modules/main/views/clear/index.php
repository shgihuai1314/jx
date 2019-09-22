<?php
$this->title = '清理缓存';
?>
<div class="layui-tab layui-tab-brief">
    <ul class="layui-tab-title">
        <li class="layui-this">清理缓存</li>
    </ul>
</div>
<div>
    <button class="layui-btn layui-btn-lg mb10 layui-btn-primary" onclick="clearCache(1)">清理运行时缓存</button>
    <button class="layui-btn layui-btn-lg mb10 layui-btn-primary" onclick="clearCache(3)">清理静态文件缓存</button>
    <button class="layui-btn layui-btn-lg mb10" onclick="clearCache(2)">清理日志缓存</button>
</div>
<script>
    function clearCache(num) {
        $.ajax({
            url: '<?=\yii\helpers\Url::toRoute(['index'])?>',
            type: 'get',
            data: {"type": num},
            dataType: 'json',
            success: function (res) {
                switch (num) {
                    case 1:
                        var title = "运行时缓存";
                        break;
                    case 2:
                        var title = "日志缓存";
                        break;
                    case 3:
                        var title = "静态文件缓存";
                        break;
                }
                parent.layer.msg(title + res.message, {offset: '150px'});
            }
        });
    }
</script>