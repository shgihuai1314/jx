<?php

namespace system\modules\notify\migrations;

use console\models\Migration;

class M181124033855Add_config extends Migration
{
    // 所属模块
    public $module_id = 'notify';

    // 更新说明
    public $description = '增加消息提醒的相关配置';

    // 版本号
    public $version = '1.0';

    // 配置信息，包含系统配置systemConfig，拓展字段extendsField和菜单menu
    public $config = [
        //配置
        'systemConfig' => [
            [
                'name' => 'NOTIFY_TYPE',
                'title' => '消息提醒内容标识',
                'type' => 'array',
                'value' => "course=课程\r\ntopic=话题\r\nanswer=问答\r\ncomment=评论\r\nnotes=笔记\r\naudit=审核\r\npay=支付\r\nattention=关注\r\narticle=资讯",
                'remark' => '消息提醒内容标识设置',
            ],
        ],
    ];

    public function up()
    {
        $this->addColumn('tab_notify_node', 'is_self',
            $this->boolean()->notNull()->defaultValue(0)->comment('是否可自己设置;0:否;1:是'));
        $this->addColumn('tab_notify_node', 'icon',
            $this->string()->notNull()->defaultValue('')->comment('图标'));

        $this->batchInsert('tab_notify_node', [
            'node_name',
            'node_info',
            'module',
            'content',
            'send_message',
            'is_self',
        ], [
            ['topic_comment', '话题被评论', 'topic', '${user}评论了你的话题【${title}】，点击查看！', 1, 1],
            ['topic_agree', '话题被点赞', 'topic', '${user}点赞了你的话题【${title}】，点击查看！', 1, 1],
            ['topic_report', '话题被举报', 'topic', '${user}举报了你的话题【${title}】，点击查看！', 1, 1],
            ['answer_comment', '提问被回答', 'answer', '${user}评论了你的问答【${title}】，点击查看！', 1, 1],
            ['answer_agree', '提问被点赞', 'answer', '${user}点赞了你的问答【${title}】，点击查看！', 1, 1],
            ['answer_report', '提问被举报', 'answer', '${user}举报了你的问答【${title}】，点击查看！', 1, 1],
            ['comment_comment', '评论被回复', 'comment', '你在【${title}】${type}下的回答被回复，点击查看！', 1, 1],
            ['comment_agree', '评论被点赞', 'comment', '你在【${title}】${type}下的回答被点赞，点击查看！', 1, 1],
            ['comment_report', '评论被举报', 'comment', '你在【${title}】${type}下的回答被举报，点击查看！', 1, 1],
            ['notes_agree', '笔记被点赞', 'notes', '你在【${title}】的笔记被点赞，点击查看！', 1, 1],
            ['notes_report', '笔记被举报', 'notes', '你在【${title}】的笔记被举报，点击查看！', 1, 1],
            ['course_notice', '课程公告', 'course', '【${title}】有新的公告，点击查看！', 1, 0],
            ['course_update', '课程更新', 'course', '【${title}】有新的改动，点击查看！', 1, 0],
            ['course_evaluate', '课程被评价', 'course', '${user}评价了你的课程【${title}】，点击查看！', 1, 0],
            ['evaluate_reply', '评价被回复', 'course', '【${title}】下的评价被回复，点击查看！', 1, 0],
            ['evaluate_agree', '评价被点赞', 'course', '【${title}】下的评价被点赞，点击查看！', 1, 0],
            ['evaluate_report', '评价被举报', 'course', '【${title}】下的评价被举报，点击查看！', 1, 0],
            ['task_report', '任务被举报', 'course', '【${title}】下的任务被举报，点击查看！', 1, 0],
            ['course_report', '课程被举报', 'course', '${user}举报了你的课程【${title}】，点击查看！', 1, 0],
            ['course_recommend', '课程被推荐', 'course', '你的课程【${title}】被推荐了，点击查看！', 1, 0],
            ['homework_result', '作业结果', 'course', '【${title}】作业结果出来了，点击查看！', 1, 0],
            ['exam_result', '考试结果', 'course', '【${title}】考试结果出来了，点击查看！', 1, 0],
            ['live_notify', '直播通知', 'course', '【${title}】还有10分钟（${time}）就要开始直播了，点击查看！', 1, 0],
            ['file_audi', '文件审核', 'audit', '【${title}】审核${status}，点击查看！', 1, 0],
            ['charge_audi', '收费审核', 'audit', '【${title}】审核${status}，点击查看！', 1, 0],
            ['live_audi', '直播审核', 'audit', '【${title}】${type}审核${status}，点击查看！', 1, 0],
            ['pay_success', '订单支付成功', 'pay', '【${title}】支付成功，点击查看！', 1, 0],
            ['order_notify', '新订单提醒', 'pay', '你收到一笔新的订单【${title}】，点击查看！', 1, 0],
            ['order_settlement', '订单结算', 'pay', '【${title}】订单${number}已结算到你的余额，点击查看！', 1, 0],
            ['balance_withdrawal', '余额提现', 'pay', '提现已成功，点击查看！', 1, 0],
            ['attention_notify', '被人关注', 'attention', '${user}关注了你，点击查看！', 1, 0],
            ['article_comment', '文章被评论', 'article', '${user}评论了你的文章【${title}】，点击查看！', 1, 0],
            ['article_agree', '文章被点赞', 'article', '${user}点赞了你的文章【${title}】，点击查看！', 1, 0],
            ['article_report', '文章被举报', 'article', '${user}举报了你的文章【${title}】，点击查看！', 1, 0],
        ]);

        parent::up();
    }

    public function down()
    {
        parent::down();
    }
}
