<?php

/**
 * Created by PhpStorm.
 * User: Ray
 * Date: 2017/3/7
 * Time: 上午10:25
 */

namespace raysoft\WxAuthServer\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "gb_wxlogin_user".
 *
 * @property integer $user_id
 * @property string $openid
 * @property string $nickname
 * @property integer $sex
 * @property string $avatar
 * @property string $city
 * @property string $province
 * @property string $country
 * @property integer $created_at
 * @property integer $updated_at
 */
class User extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%wxlogin_user}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sex', 'created_at', 'updated_at'], 'integer'],
            [['openid'], 'string', 'max' => 100],
            [['nickname', 'city', 'province'], 'string', 'max' => 20],
            [['avatar'], 'string', 'max' => 200],
            [['country'], 'string', 'max' => 50],
        ];
    }
}