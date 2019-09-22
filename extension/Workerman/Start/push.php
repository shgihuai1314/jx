<?phpuse Workerman\Worker;use \system\modules\user\components\UserIdentity;use \system\modules\notify\models\DirectMessage;require_once __DIR__ . '/../Autoloader.php';define('APP_NAME', 'console'); // 应用名称，必填require_once(__DIR__ . '/../../../vendor/autoload.php');require_once(__DIR__ . '/../../../vendor/yiisoft/yii2/Yii.php');require_once(__DIR__ . '/../../../common/config/bootstrap.php');require_once(__DIR__ . '/../../../console/config/bootstrap.php');$config = yii\helpers\ArrayHelper::merge(    require_once(__DIR__ . '/../../../common/config/main.php'),    require_once(__DIR__ . '/../../../common/config/main-local.php'),    require_once(__DIR__ . '/../../../console/config/main.php'),    require_once(__DIR__ . '/../../../console/config/main-local.php'));$application = new yii\console\Application($config);$app = new \system\modules\main\components\LoadModule();$app->bootstrap($application);// 初始化一个worker容器，监听1234端口global $worker;$worker = new Worker('websocket://0.0.0.0:8903');// ====这里进程数必须必须必须设置为1====$worker->count = 1;$worker->name = 'push';// 新增加一个属性，用来保存uid到connection的映射(uid是用户id或者客户端唯一标识)$worker->uidConnections = array();// worker进程启动后建立一个内部通讯端口$worker->onWorkerStart = function($worker){    // 开启一个内部端口，方便内部系统推送数据，Text协议格式 文本+换行符    $inner_text_worker = new Worker('Text://0.0.0.0:8902');    $inner_text_worker->onMessage = function($connection, $buffer)    {        global $worker;        // $data数组格式，里面有uid，表示向那个uid的页面推送数据        $data = \yii\helpers\Json::decode($buffer, true);        // 通过workerman，向uid的页面推送数据        $message = sendMessageByUid($data);        // 返回推送结果        $connection->send($message);    };    $inner_text_worker->listen();};// 当有客户端发来消息时执行的回调函数$worker->onMessage = function($connection, $data) use ($worker){    if (is_string($data)) {        $data = \yii\helpers\Json::decode($data, true);    }    // 判断当前客户端是否已经验证,即是否设置了uid    if(!isset($connection->uid)) {        if (!isset($data['token'], $data['to_user'])) {            $connection->send('连接失败');            $connection->close();        } else {            $user = UserIdentity::findIdentityByAccessToken($data['token']);            if (!$user) {                $connection->send('连接失败');                $connection->close();                return;            }            // 没验证的话把第一个包当做uid（这里为了方便演示，没做真正的验证）            $connection->uid = $user['user_id'];            //$connection->to_user = $data['to_user'];            $worker->uidConnections[$connection->uid]['to_user'] = $data['to_user'];            $worker->uidConnections[$connection->uid]['conn'] = $connection;            return;        }    }    // 给特定uid发送    sendMessageByUid($data);};// 当有客户端连接断开时$worker->onClose = function($connection){    global $worker;    if(isset($connection->uid))    {        // 连接断开时删除映射        unset($worker->uidConnections[$connection->uid]);    }};// 针对uid推送数据function sendMessageByUid($data){    global $worker;    if(isset($worker->uidConnections[$data['to_user']]))    {        $connection = $worker->uidConnections[$data['to_user']]['conn'];        if ($worker->uidConnections[$data['to_user']]['to_user'] == $data['from_user'])        {            DirectMessage::updateAll(['is_read' => 1], ['id' => $data['id']]);            $connection->send(\yii\helpers\Json::encode($data));        }    } else {        echo PHP_EOL.$data['to_user'].'user connect fail';    }    return false;}// 运行所有的worker（其实当前只定义了一个）Worker::runAll();