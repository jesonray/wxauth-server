<?php

use raysoft\WxAuthServer\models\App;
use raysoft\WxAuthServer\models\Task;
use raysoft\WxAuthServer\models\User;
use yii\db\Migration;
use yii\db\Schema;

class m170307_015512_wxauth extends Migration
{
    public function safeUp()
    {
        $this->initAppTable();
        $this->initUserTable();
        $this->initTaskTable();
    }

    public function safeDown()
    {
        $this->dropTable(App::tableName());
        $this->dropTable(User::tableName());
        $this->dropTable(Task::tableName());
    }

    private function initAppTable()
    {
        $tableName = App::tableName();
        $this->createTable($tableName, [
            'app_id' => Schema::TYPE_PK,
            'name' => Schema::TYPE_STRING . "(20) NOT NULL DEFAULT '' COMMENT '名称'",
            'url' => Schema::TYPE_STRING . "(100) NOT NULL DEFAULT '' COMMENT '网址'",
            'access' => Schema::TYPE_TEXT . " NOT NULL DEFAULT '' COMMENT '权限'",
            'key' => Schema::TYPE_STRING . "(16) NOT NULL DEFAULT '' COMMENT 'Key'",
            'secret' => Schema::TYPE_STRING . "(32) NOT NULL DEFAULT '' COMMENT '密钥'",
            'created_at' => Schema::TYPE_INTEGER . " UNSIGNED NULL DEFAULT '0' COMMENT '创建时间'",
            'updated_at' => Schema::TYPE_INTEGER . " UNSIGNED NULL DEFAULT '0' COMMENT '修改时间'"
        ]);
        $this->createIndex('key', $tableName, 'key');
    }

    private function initUserTable()
    {
        $tableName = User::tableName();
        $this->createTable($tableName, [
            'user_id' => Schema::TYPE_PK,
            'openid' => Schema::TYPE_STRING . "(100) NOT NULL DEFAULT '' COMMENT 'openid'",
            'nickname' => Schema::TYPE_STRING . "(20) NOT NULL DEFAULT '' COMMENT '昵称'",
            'sex' => Schema::TYPE_SMALLINT . "(1) UNSIGNED NULL DEFAULT '0' COMMENT '性别'",
            'avatar' => Schema::TYPE_STRING . "(200) NULL DEFAULT '' COMMENT '头像'",
            'city' => Schema::TYPE_STRING . "(20) NULL DEFAULT '' COMMENT '城市'",
            'province' => Schema::TYPE_STRING . "(20) NULL DEFAULT '0' COMMENT '省'",
            'country' => Schema::TYPE_STRING . "(50) NULL DEFAULT '' COMMENT '国家'",
            'created_at' => Schema::TYPE_INTEGER . " UNSIGNED NULL DEFAULT '0' COMMENT '创建时间'",
            'updated_at' => Schema::TYPE_INTEGER . " UNSIGNED NULL DEFAULT '0' COMMENT '修改时间'"
        ]);
        $this->createIndex('openid', $tableName, 'openid');
    }

    private function initTaskTable()
    {
        $tableName = Task::tableName();
        $this->createTable($tableName, [
            'task_id' => Schema::TYPE_PK,
            'token' => Schema::TYPE_STRING . "(32) NOT NULL DEFAULT '' COMMENT 'Token'",
            'app_id' => Schema::TYPE_STRING . "(20) NOT NULL DEFAULT '' COMMENT 'APP ID'",
            'task_type' => Schema::TYPE_SMALLINT . "(1) NOT NULL DEFAULT '1' COMMENT '任务类型(1login)'",
            'auth_type' => Schema::TYPE_SMALLINT . "(1) NOT NULL DEFAULT '1' COMMENT '授权类型(1base,2info)'",
            'user_id' => Schema::TYPE_INTEGER . "(11) DEFAULT '0' COMMENT 'User ID'",
            'status' => Schema::TYPE_SMALLINT . "(1) UNSIGNED NULL DEFAULT '0' COMMENT '状态'",
            'created_at' => Schema::TYPE_INTEGER . " UNSIGNED NULL DEFAULT '0' COMMENT '创建时间'",
            'updated_at' => Schema::TYPE_INTEGER . " UNSIGNED NULL DEFAULT '0' COMMENT '修改时间'"
        ]);
        $this->createIndex('token', $tableName, 'token');
        $this->createIndex('app_id', $tableName, 'app_id');
        $this->createIndex('user_id', $tableName, 'user_id');
    }
}
