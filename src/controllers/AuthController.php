<?php
/**
 * Created by PhpStorm.
 * User: Ray
 * Date: 2017/3/6
 * Time: 下午3:56
 */

namespace raysoft\WxAuthServer\controllers;


use raysoft\WxAuthServer\models\Task;
use raysoft\WxAuthServer\models\User;
use Yii;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\Controller;

class AuthController extends Controller
{
    public $layout = 'wechat';

    /**
     * 登陆入口
     * @param string $token
     * @return \yii\web\Response
     */
    public function actionIndex($token='')
    {
        // 检查token
        if( !$token ) {
            return $this->redirect(Url::to(['result', 'status'=>'error', 'msg'=>'invalid_token']) );
        }

        // 检查任务
        /** @type Task $task */
        $task = Task::findOne(['token'=>$token]);
        if( !$task ) {
            return $this->redirect(Url::to(['result', 'status'=>'error', 'msg'=>'unkown_task']) );
        }

        // 更新任务状态
        $task->status = Task::STATUS_SCANED;
        $task->updated_at = time();
        $task->save();

        // 保存token
        Yii::$app->session->set('WXLOGIN_TOKEN', $token);

        return $this->actionRedirect('base');
    }

    /**
     * 微信登陆
     * @param string $type
     * @return \yii\web\Response
     */
    public function actionRedirect($type='')
    {
        // 查询token
        $token = Yii::$app->session->get('WXLOGIN_TOKEN');
        if( !$token ) {
            return $this->redirect(Url::to(['result', 'status'=>'error', 'msg'=>'invalid_token']));
        }

        // 设置state
        Yii::$app->session->set('WXLOGIN_STATE', $type);

        /** @type \EasyWeChat\Foundation\Application $app */
        $app = Yii::$app->wechat->app;
        $response = $app->oauth->scopes([
            $type=='info' ? 'snsapi_userinfo' : 'snsapi_base'
        ])->setRedirectUrl(
            Url::to(['callback'], true)
        )->redirect();

        return $this->redirect($response->getTargetUrl());
    }

    /**
     * 验证登陆
     * @return \yii\web\Response
     */
    public function actionCallback()
    {
        // 查询token
        $token = Yii::$app->session->get('WXLOGIN_TOKEN');
        if( !$token ) {
            Yii::error('获取Token错误', 'WXLOGIN');
            return $this->redirect(Url::to(['result', 'status'=>'error', 'msg'=>'invalid_token']));
        }

        // 查询任务
        /** @type Task $task */
        $task = Task::findOne(['token'=>$token]);
        if( !$task ){
            Yii::error('查询任务失败', 'WXLOGIN');
            return $this->redirect(Url::to(['result', 'status'=>'error', 'msg'=>'invalid_task']));
        }

        /** @type \EasyWeChat\Foundation\Application $app */
        $app = Yii::$app->wechat->app;
        $oauth = $app->oauth;

        // 获取用户信息
        /** @type \Overtrue\Socialite\User $user */
        $user = $oauth->user();
        if( !$user ) {
            Yii::error('从微信接口获取用户信息失败', 'WXLOGIN');
            return $this->redirect(Url::to(['result', 'status'=>'error', 'msg'=>'get_user_failed']));
        }

        // 仅需要openid
        if( $task->auth_type==Task::TYPE_BASE ) {
            return $this->processBase($task, $user);
        }
        // 需要用户资料
        elseif( $task->auth_type==Task::TYPE_INFO ) {
            return $this->processInfo($task, $user);
        }
        else {
            Yii::error('未知的任务类型', 'WXLOGIN');
            return $this->redirect(Url::to(['result', 'status'=>'error', 'msg'=>'unkown_task_type']));
        }
    }

    /**
     * 用户授权界面
     * @return string|\yii\web\Response
     */
    public function actionAuth()
    {
        // 查询token
        $token = Yii::$app->session->get('WXLOGIN_TOKEN');
        if( !$token ) {
            Yii::error('获取Token错误', 'WXLOGIN');
            return $this->redirect(Url::to(['result', 'status'=>'error', 'msg'=>'invalid_token']));
        }

        // 查询任务
        /** @type Task $task */
        $task = Task::findOne(['token'=>$token]);
        if( !$task ){
            Yii::error('查询任务失败', 'WXLOGIN');
            return $this->redirect(Url::to(['result', 'status'=>'error', 'msg'=>'invalid_task']));
        }

        return $this->render('auth', [
            'task' => $task
        ]);
    }

    /**
     * 用户确认
     * @param $token
     * @param $action
     * @return mixed
     */
    public function actionConfirm($token, $action)
    {
        // 查询任务
        /** @type Task $task */
        $task = Task::findOne(['token'=>$token]);
        if( !$task ){
            Yii::error('查询任务失败', 'WXLOGIN');
            throw new Exception('查询任务失败');
        }

        // 更新任务状态
        if( $action=='allow' ) {
            $task->status = Task::STATUS_AUTHED;
        } else {
            $task->status = Task::STATUS_DENIED;
        }
        $task->updated_at = time();
        if( !$task->save() ) {
            Yii::error('查询任务失败', 'WXLOGIN');
            throw new Exception('更新任务失败');
        }

        return 1;
    }

    /**
     * 显示结果界面
     * @param string $status
     * @param string $msg
     * @return string
     */
    public function actionResult($status, $msg='')
    {
        return $this->render('result', [
            'status' => $status
        ]);
    }

    /**
     * 获取openid的任务
     * @param Task $task
     * @param \Overtrue\Socialite\User $user
     * @return \yii\web\Response
     */
    private function processBase(Task $task, \Overtrue\Socialite\User $user)
    {
        // 查询用户
        /** @type User $model */
        $model = User::findOne(['openid'=>$user->getId()]);

        // 如果用户表不存在此用户,添加
        if( !$model ) {
            $model = new User();
            $model->openid = $user->getId();
            $model->created_at = time();
            if( !$model->save() ) {
                Yii::error('创建用户资料失败', 'WXLOGIN');
                return $this->redirect(Url::to(['result', 'status'=>'error', 'msg'=>'create_user_failed']));
            }
        }

        // 更新任务状态
        $task->user_id = $model->user_id;
        $task->updated_at = time();
        if( $task->save() ) {
            return $this->redirect(Url::to(['auth']));
        } else {
            Yii::error('更新任务失败', 'WXLOGIN');
            return $this->redirect(Url::to(['result', 'status'=>'error', 'msg'=>'update_task_failed']));
        }
    }

    /**
     * @param Task $task
     * @param \Overtrue\Socialite\User $user
     * @return \yii\web\Response
     */
    private function processInfo(Task $task, \Overtrue\Socialite\User $user)
    {
        // 查询用户
        /** @type User $model */
        $model = User::findOne(['openid'=>$user->getId()]);

        // 获取state, 以此判断是第几次跳转
        $state = Yii::$app->session->get('WXLOGIN_STATE');

        // 若此次跳转只是获取openid, 检查用户表是否有记录
        // 如果没有,再次跳转获取用户信息
        if( $state=='base' ) {
            if( !$model || !$model->nickname ) {
                return $this->redirect(Url::to(['redirect', 'type'=>'info']));
            }
        }
        // 若此次是获取userInfo, 检查用户表是否有记录
        // 如果有,更新记录
        // 如果没有,添加记录
        elseif( $state=='info' ) {
            $userInfo = $user->getOriginal();

            if( !$model ) {
                $model = new User();
                $model->created_at = time();
            } else {
                $model->updated_at = time();
            }
            $model->openid = ArrayHelper::getValue($userInfo, 'openid');
            $model->nickname = ArrayHelper::getValue($userInfo, 'nickname');
            $model->avatar = ArrayHelper::getValue($userInfo, 'headimgurl');
            $model->sex = ArrayHelper::getValue($userInfo, 'sex');
            $model->city = ArrayHelper::getValue($userInfo, 'city');
            $model->province = ArrayHelper::getValue($userInfo, 'province');
            $model->country = ArrayHelper::getValue($userInfo, 'country');
            if( !$model->save() ) {
                Yii::error('创建用户资料失败', 'WXLOGIN');
                return $this->redirect(Url::to(['result', 'status'=>'error', 'msg'=>'create_user_failed']));
            }
        }

        // 更新任务状态
        $task->user_id = $model->user_id;
        $task->updated_at = time();
        if( $task->save() ) {
            return $this->redirect(Url::to(['auth']));
        } else {
            Yii::error('更新任务失败', 'WXLOGIN');
            return $this->redirect(Url::to(['result', 'status'=>'error', 'msg'=>'update_task_failed']));
        }
    }
}