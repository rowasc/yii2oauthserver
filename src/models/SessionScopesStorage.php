<?php
/**
 * Created by PhpStorm.
 * User: rominas
 * Date: 12/18/14
 * Time: 11:37 AM
 */

namespace rowasc\yii2oauthserver\models;


use yii\db\ActiveRecord;

class SessionScopesStorage extends  ActiveRecord{


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%oauth_session_scopes}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['session_id', 'scope'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        return [
            'session_id',
            'scope'
        ];
    }
} 