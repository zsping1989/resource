<?php
/**
 * 通过 PhpStorm 创建.
 * 创建人: zhangshiping
 * 日期: 16-5-3
 * 时间: 上午11:19
 *
 * 验证扩展类
 *
 */

namespace Resource\Validators;


use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Validator;

class CustomValidator extends Validator{

    /**
     * 验证手机号码
     * 参数: $attribute
     * 参数: $value
     * 参数: $parameters
     * 返回: bool
     */
    public function validateMobilePhone($attribute, $value, $parameters)
    {
        if(!$value) return true;
        return preg_match("/^1[34578]\\d{9}$/", $value);
    }

    /**
     * 验证是否为空
     * 参数: $attribute
     * 参数: $value
     * 参数: $parameters
     * 返回: bool
     */
    public function validateIsNull($attribute, $value, $parameters)
    {
        return empty($value);
    }

    /**
     * 验证用户输入密码是否正确
     * @param $attribute
     * @param $value
     * @param $parameters
     * 返回: mixed
     */
    public function validateCkeckPassword($attribute, $value, $parameters){
        $user = Auth::user(); //获取当前用户
        return Auth::validate(['uname' => $user['uname'], 'password' => $value]); //验证
    }


    /**
     * 验证验证码
     * 参数: $attribute
     * 参数: $value
     * 参数: $parameters
     * 返回: bool
     */
    public function validateUserName($attribute, $value, $parameters)
    {
        if(!$value) return true;
        return preg_match("/^[a-zA-Z][A-Za-z0-9_]{3,15}$/", $value);
    }

    /**
     * 验证身份证号码
     * @param $attribute
     * @param $value
     * @param $parameters
     * @return bool|int
     */
    public function validateIsIdcard($attribute, $value, $parameters){
        if(!$value) return true;
        return preg_match("/^[1-9]\d{7}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}$|^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}([0-9]|X)$/", $value);
    }



} 