<?php
/**
 * Created by PhpStorm.
 * User: Ray
 * Date: 2017/3/6
 * Time: 下午4:43
 */

namespace raysoft\WxAuthServer\controllers;

use raysoft\WxAuthServer\models\Task;
use yii;
use Exception;
use raysoft\WxAuthServer\components\BaseController;
use yii\helpers\Url;

class ApiController extends BaseController
{
    /**
     * 登陆请求
     * @return array
     * @throws \Exception
     */
    public function actionLogin()
    {
        $taskTypeMap = [
            'login' => 1
        ];

        // 获取action
        $action = Yii::$app->request->get('action');
        if( !$action ) {
            throw new Exception('Invalid Argument: action');
        }

        // 检查login是否允许
        if( !isset($taskTypeMap[$action]) ) {
            throw new Exception('Invalid Argument: action');
        }

        // 检查权限
        if( !$this->app->checkAccess($action) ) {
            throw new Exception('Invalid Action: '.$action);
        }

        // 清理任务
        Task::clearExpired();

        // 创建任务
        $taskToken = Task::create($this->app, $taskTypeMap[$action], Task::TYPE_INFO);

        return [
            'task' => $taskToken,
            'url' => Url::to(['auth/index', 'token'=>$taskToken], true),
            'status' => 0
        ];
    }

    /**
     * 检查状态
     * @return array
     * @throws \Exception
     */
    public function actionStatus()
    {
        // 获取taskToken
        $taskToken = Yii::$app->request->get('task');
        if( !$taskToken ) {
            throw new Exception('Invalid Argument: task');
        }

        // 查询任务
        $task = Task::findOne(['token'=>$taskToken]);
        if( !$task ) {
            throw new Exception('Invalid Task');
        }

        // 如果已经授权, 返回用户信息
        $user = [];
        if( $task->status==Task::STATUS_AUTHED ) {
            $user['openid'] = $task->user->openid;
            if( $task->auth_type==Task::TYPE_INFO ) {
                $user['nickname'] = $task->user->nickname;
                $user['avatar'] = $task->user->avatar;
                $user['sex'] = $task->user->sex;
                $user['province'] = $task->user->province;
                $user['city'] = $task->user->city;
                $user['country'] = $task->user->country;
            }
        }

        return [
            'task' => $task->token,
            'status' => $task->status,
            'text' => $task->getStatusText(),
            'user' => $user
        ];
    }
}