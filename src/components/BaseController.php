<?php
/**
 * Created by PhpStorm.
 * User: Ray
 * Date: 2017/3/7
 * Time: 下午6:02
 */

namespace raysoft\WxAuthServer\components;

use Yii;
use Exception;
use yii\web\Response;
use yii\web\Controller;
use raysoft\WxAuthServer\models\App;
use yii\web\NotFoundHttpException;

class BaseController extends Controller
{
    /** @type App */
    protected $app;

    public function init()
    {
        parent::init();

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        Yii::$app->response->on(Response::EVENT_BEFORE_SEND, function($event){
            $response = $event->sender;
            if( $response->statusCode==200 ) {
                $response->data = [
                    'code' => 200,
                    'data' => $response->data,
                ];
            } else {
                $data = [
                    'name' => $response->data['name'],
                    'code' => $response->data['code'],
                    'message' => $response->data['message']
                ];
                $response->data = [
                    'success' => $response->statusCode,
                    'data' => $data,
                ];
                $response->statusCode=200;
            }
        });

        $this->checkAccess();
    }

    /**
     * 检查权限
     * @throws \Exception
     * @throws \yii\web\NotFoundHttpException
     */
    protected function checkAccess()
    {
        $request = Yii::$app->request;

        // 获取key
        $key = $request->get('key');
        if( !$key ) {
            throw new Exception('Invalid Argument: key');
        }

        // 获取token
        $token = $request->get('token');
        if( !$token ) {
            throw new Exception('Invalid Argument: token');
        }

        // 获取时间戳
        $timestamp = $request->get('timestamp');
        if( !$timestamp || !is_numeric($timestamp) ) {
            throw new Exception('Invalid Argument: timestamp');
        }

        // 检查时间戳
        if( time()-$timestamp>60 ) {
            throw new Exception('Request is expired');
        }

        // 获取app信息
        /** @type App $app */
        $this->app = App::findOne(['key'=>$key]);
        if( !$this->app ) {
            throw new NotFoundHttpException('Invalid Key');
        }

        // 验证token
        $params = $_GET;
        unset($params['token']);
        if( SimpleSign::sign($params, $this->app->secret) != $token ) {
            throw new Exception('Invalid Token');
        }
    }
}