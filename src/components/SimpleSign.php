<?php
/**
 * Created by PhpStorm.
 * User: Ray
 * Date: 2017/3/9
 * Time: 下午3:26
 */

namespace raysoft\WxAuthServer\components;


class SimpleSign
{
    /**
     * @param string $params
     * @param string $secret
     * @return mixed
     */
    public static function sign($params, $secret = '')
    {
        return md5(strtoupper(md5(static::assemble($params))) . $secret);
    }

    /**
     * @param $params
     * @return null|string
     */
    private static function assemble($params)
    {
        if( !is_array($params) ) {
            return null;
        }

        ksort($params, SORT_STRING);
        $sign = '';
        foreach ($params AS $key => $val) {
            $sign .= $key . (is_array($val) ? self::assemble($val) : $val);
        }

        return $sign;
    }
}