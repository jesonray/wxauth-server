<?php

/**
 * Created by PhpStorm.
 * User: Ray
 * Date: 2017/3/7
 * Time: 上午10:25
 */

namespace raysoft\WxAuthServer\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "gb_wxlogin_task".
 *
 * @property integer $task_id
 * @property integer $app_id
 * @property integer $task_type
 * @property integer $auth_type
 * @property integer $user_id
 * @property string  $token
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property User $user
 * @property App $app
 */
class Task extends ActiveRecord
{
    const STATUS_WAIT = 0;
    const STATUS_SCANED = 1;
    const STATUS_AUTHED = 2;
    const STATUS_DENIED = 3;

    const STATUS_MAP = [
        self::STATUS_WAIT => '等待扫码',
        self::STATUS_SCANED => '已扫描',
        self::STATUS_AUTHED => '已授权',
        self::STATUS_DENIED => '已拒绝'
    ];

    const TYPE_BASE = 1;
    const TYPE_INFO = 2;

    const TASK_LOGIN = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%wxlogin_task}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['token'], 'string', 'max' => 32],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getApp()
    {
        return $this->hasOne(App::className(), ['app_id'=>'app_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['user_id'=>'user_id']);
    }

    /**
     * 创建认证任务
     * @param App $app
     * @param $taskType
     * @param $authType
     * @return bool | string
     */
    public static function create($app, $taskType, $authType)
    {
        $token = self::genToken();

        $task = new self;
        $task->token = $token;
        $task->app_id = $app->app_id;
        $task->task_type = $taskType;
        $task->auth_type = $authType;
        $task->created_at = time();
        if( $task->save() ) {
            Yii::error('创建认证任务失败', 'WXLOGIN');
            return $token;
        }
        return false;
    }

    /**
     * 清理过期的任务
     * @param int $timeout
     */
    public static function clearExpired($timeout=120)
    {
        return self::deleteAll(['<', 'created_at', time()-$timeout]);
    }

    private static function genToken()
    {
        return md5('AUTH_TOKEN_'.date('ymd').rand(10000, 99999));
    }

    public function getStatusText()
    {
        return ArrayHelper::getValue(self::STATUS_MAP, $this->status, '');
    }
}