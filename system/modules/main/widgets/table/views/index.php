<div class="layui-form">
    <table class="layui-table">
        <thead>
        <tr>
            <!-- 基于数据模型-->
            <?php foreach ($labels as $k => $v): ?>
                <th><?= $v ?></th>
            <?php endforeach; ?>

            <!-- 基于系统配置-->
            <?php
                if(isset($groups) && !is_null($groups)):
                foreach ($groups as $k => $v):
             ?>
                <th><?= $v ?></th>
            <?php
                endforeach;
                endif;
             ?>

                <th>操作</th>
        </tr>
        </thead>

        <tbody>
        <?php foreach ($data as $item): ?>
            <tr>
                <!-- 基于数据模型-->
                <?php foreach ($labels as $k => $v): ?>
                    <td><?= \yii\helpers\Html::encode($item[$k]) ?></td>
                <?php endforeach; ?>

                <!-- 基于系统配置-->
                <?php
                    if(isset($groups) && !is_null($groups)):
                    foreach ($groups as $k => $v):
                ?>
                    <td><?= $item[$k] ?></td>
                <?php
                    endforeach;
                    endif;
                ?>

                <td>
                    <div class="layui-btn-group">
                    <a class="layui-btn  layui-btn-sm"
                       href="<?= \yii\helpers\Url::toRoute(['edit', 'id' => $item[$id]]) ?>">编辑</a>
                    <button class="layui-btn layui-btn-primary  layui-btn-sm delete-item"
                            data-id="<?= $item[$id] ?>">删除
                    </button>
                    </div>
                </td>


            </tr>
        <?php endforeach; ?>

        </tbody>
    </table>
</div>

<?php if (isset($pagination) && !is_null($pagination)): ?>
    <?= \system\widgets\MyPaginationWidget::widget([
        'pagination' => $pagination,
    ]) ?>
<?php endif; ?>
