<?php
/**
 * Created by PhpStorm.
 * User: rominas
 * Date: 12/9/14
 * Time: 4:48 PM
 */

namespace rowasc\yii2oauthserver\models;


class AccessTokenScope extends AbstractStorage {

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%oauth_access_token_scopes}}';
    }


    /**
     * @inheritdoc
     */
    public function fields()
    {
        return [
            'access_token',
            'session_id',
            'expire_time'
        ];
    }
} 