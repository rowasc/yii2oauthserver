<?php
namespace rowasc\yii2oauthserver\models;

use Yii;
use yii\db\ActiveRecord;
/**
 * User model
*/
class User  extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null) {
        $identity = static::find()
            ->innerJoin('{{%oauth_sessions}}', '{{%oauth_sessions}}.owner_id={{%user}}.id')
            ->innerJoin('{{%oauth_access_tokens}}', '{{%oauth_access_tokens}}.session_id = {{%oauth_sessions}}.id')
            ->where('{{%oauth_access_tokens}}.access_token = :access_token', [":access_token" => $token])
            ->one();
        return $identity;
    }
}
