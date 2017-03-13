<?php
/**
 * Created by PhpStorm.
 * User: Ray
 * Date: 2017/3/7
 * Time: 下午4:08
 */

namespace raysoft\WxAuthServer\models;


use yii\db\ActiveRecord;

/**
 * This is the model class for table "gb_wxlogin_app".
 *
 * @property integer $app_id
 * @property string $name
 * @property string $access
 * @property string $url
 * @property string $key
 * @property string $secret
 * @property integer $created_at
 * @property integer $updated_at
 */
class App extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%wxlogin_app}}';
    }

    public function checkAccess($access)
    {
        $accesses = json_decode($this->access, 1);
        return in_array($access, $accesses);
    }
}