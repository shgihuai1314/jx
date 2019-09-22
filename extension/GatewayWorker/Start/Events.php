<?php
/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */

use \GatewayWorker\Lib\Gateway;
use system\modules\user\models\User;
use \system\modules\user\components\UserIdentity;

/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class Events
{
    /**
     * 有消息时
     * @param int $client_id
     * @param mixed $message
     */
    public static function onMessage($client_id, $message)
    {
        echo $client_id . ' send new message ';
        // 客户端传递的是json数据
        $message_data = json_decode($message, true);
        if (!$message_data) {
            return;
        }
        // 根据类型执行不同的业务
        switch ($message_data['type']) {
            case 'pong':// 客户端回应服务端的心跳
                return;
            case 'login':
                // 判断是否有房间号
                if (!isset($message_data['room_id'])) {
                    throw new \Exception("\$message_data['room_id'] not set. client_ip:{$_SERVER['REMOTE_ADDR']} \$message:$message");
                }

                // 房间号
                $room_id = $message_data['room_id'];

                // 用户ID
                $user = UserIdentity::findIdentityByAccessToken($message_data['user_token']);
                $user_id = $user['user_id'];

                // 用户信息
                $userInfo = [
                    'user_id' => $user_id,
                    'username' => User::getInfo($user_id),
                    'avatar' => User::getInfo($user_id, 'avatar'),
                ];
                // 把房间号和用户信息放到session中
                $_SESSION['room_id'] = $room_id;
                $_SESSION['user_info'] = $userInfo;

                // 讲当前客户端加入到该房间分组
                Gateway::joinGroup($client_id, $room_id);

                // 获取房间内所有用户列表
                $clients_list = Gateway::getClientSessionsByGroup($room_id);
                foreach ($clients_list as $cid => $item) {
                    $clients_list[$cid] = $item['user_info'];
                }

                // 转播给当前房间的所有客户端，xx进入聊天室 message {type:login, client_id:xx, name:xx}
                /*$new_message = [
                    'type' => $message_data['type'],
                    'client_id' => $client_id,
                    'user_info' => $userInfo,
                    'client_list' => $clients_list,
                    'time' => date('Y-m-d H:i:s')
                ];
                Gateway::sendToGroup($room_id, json_encode($new_message));*/
                return;
            case 'sendmsg':
                // 非法请求
                if (!isset($_SESSION['room_id'])) {
                    throw new \Exception("\$_SESSION['room_id'] not set. client_ip:{$_SERVER['REMOTE_ADDR']}");
                }
                // 用户ID
                $room_id = $message_data['room_id'];

                $user = UserIdentity::findIdentityByAccessToken($message_data['user_token']);
                $user_id = $user['user_id'];

                // 用户信息
                $userInfo = [
                    'user_id' => $user_id,
                    'username' => $user->realname,
                    'avatar' => $user->avatar,
                ];

                $model = new \system\modules\course\models\CourseChat();
                $model->loadDefaultValues();

                $model->room_id = $room_id;
                $model->user_id = $user_id;
                $model->content = $message_data['content'];
                $model->save();

                // 把房间号和用户信息放到session中
                $_SESSION['room_id'] = $room_id;
                $_SESSION['user_info'] = $userInfo;

                // 私聊
                /*if ($message_data['to_client_id'] != 'all') {
                    $new_message = [
                        'type' => 'sendmsg',
                        'client_id' => $client_id,
                        'user_info' => $userInfo,
                        'to_client_id' => $message_data['to_client_id'],
                        'content' => "<p class='text-red' style='margin-bottom: 5px; font-weight: bold'>对你说: </p>" . $message_data['content'],
                        'time' => date('Y-m-d H:i:s'),
                    ];
                    // 发送给私聊对象
                    Gateway::sendToClient($message_data['to_client_id'], json_encode($new_message));

                    // 发送给自己
                    $new_message['content'] = "<p style='margin-bottom: 5px; font-weight: bold'>你对" . $message_data['to_user_name'] . "说: </p>" . $message_data['content'];
                    Gateway::sendToCurrentClient(json_encode($new_message));
                } else {*/
                    $new_message = [
                        'type' => 'sendmsg',
                        'client_id' => $client_id,
                        'user_info' => $userInfo,
                        'to_client_id' => 'all',
                        'content' => $message_data['content'],
                        'time' => date('Y-m-d H:i:s'),
                    ];
                    Gateway::sendToGroup($room_id, json_encode($new_message));
                /*}*/
                return;
        }
    }

    /**
     * 当客户端断开连接时
     * @param integer $client_id 客户端id
     */
    public static function onClose($client_id)
    {
        // 从房间的客户端列表中删除
        if (isset($_SESSION['room_id'])) {
            $room_id = $_SESSION['room_id'];
            $new_message = ['type' => 'logout', 'client_id' => $client_id, 'user_info' => $_SESSION['user_info'], 'time' => date('Y-m-d H:i:s')];
            Gateway::sendToGroup($room_id, json_encode($new_message));
        }
        echo $client_id . ' close connection';
    }
}
