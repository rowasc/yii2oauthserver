<?php

namespace rowasc\modules\yii2oauthserver\tests\models;
use rowasc\modules\yii2oauthserver\models\User as yii2OAuthUser;

use rowasc\components\Log;
use Yii;
class ApiUserIdentity extends yii2OAuthUser
{

    public static function tableName() {
        return '{{%user}}';
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->getSecurity()->validatePassword($password, $this->password_hash);
    }

}