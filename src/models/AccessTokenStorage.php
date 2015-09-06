<?php
/**
 * Created by PhpStorm.
 * User: rsuarez
 * Date: 11/30/14
 * Time: 4:09 PM
 */


namespace rowasc\yii2oauthserver\models;

use League\OAuth2\Server\Entity\AccessTokenEntity;
use League\OAuth2\Server\Entity\ScopeEntity;
use League\OAuth2\Server\Exception\OAuthException;
use League\OAuth2\Server\Storage;
use Yii;
use yii\base\Exception;
use yii\db\Query;

class AccessTokenStorage extends AbstractStorage implements Storage\AccessTokenInterface {


    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%oauth_access_tokens}}';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['access_token', 'expire_time','session_id'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function fields() {
        return [
            'access_token',
            'expire_time',
            'session_id'
        ];
    }

    /**
     * Get an instance of Entity\AccessTokenEntity
     *
     * @param  string $token The access token
     *
     * @return \League\OAuth2\Server\Entity\AccessTokenEntity
     */
    public function get($token) {
        $accessTokenEntity = null;
        $accessToken = $this->findOne(array("access_token" => $token));
        if ($accessToken !== null) {
            $accessTokenEntity = (new AccessTokenEntity($this->getServer()))->setId($accessToken->getAttribute("access_token"))->setExpireTime($accessToken->getAttribute("expire_time"));
        }
        return $accessTokenEntity;
    }

    /**
     * Get the scopes for an access token
     *
     * @param  \League\OAuth2\Server\Entity\AccessTokenEntity $token The access token
     *
     * @return array                                            Array of \League\OAuth2\Server\Entity\ScopeEntity
     */
    public function getScopes(AccessTokenEntity $token) {
        $scopeEntityArray = array();
        $queryScopes = new Query();
        $scopesArray = null;
        /**
         * Using yii2's queryBuilder
         */
        $scopesArray = $queryScopes->select(['{{%oauth_scopes}}.id AS id', '{{%oauth_scopes}}.description as description'])
            ->from('{{%oauth_access_token_scopes}}')
            ->innerJoin('{{%oauth_scopes}}', '{{%oauth_access_token_scopes}}.scope= {{%oauth_scopes}}.id')
            ->all();

        $scopeEntity = null;
        if (!empty($scopesArray)) {
            foreach ($scopesArray as $scopesArrayItem) {
                $scopeEntity = (new ScopeEntity($this->getServer()))->hydrate([
                    'id' => $scopesArrayItem['id'],
                    'description' => $scopesArrayItem['description'],
                ]);
                $scopeEntityArray[] = $scopeEntity;
            }
        }

        return $scopeEntityArray;

    }

    /**
     * Creates a new access token
     *
     * @param  string         $token      The access token
     * @param  integer        $expireTime The expire time expressed as a unix timestamp
     * @param  string|integer $sessionId  The session ID
     *
     * @return AccessTokenEntity
     * @throws OAuthException
     */
    public function create($token, $expireTime, $sessionId) {
        $this->setAttribute("access_token", $token);
        $this->setAttribute("session_id", $sessionId);
        $this->setAttribute("expire_time", $expireTime);
        if ($this->save()) {
            return $this->getPrimaryKey();
        } else {
            throw new OAuthException(json_encode($this->errors));
        }
    }

    /**
     * Associate a scope with an acess token
     *
     * @param  \League\OAuth2\Server\Entity\AccessTokenEntity $token The access token
     * @param  \League\OAuth2\Server\Entity\ScopeEntity       $scope The scope
     *
     * @return bool
     * @throws OAuthException
     */
    public function associateScope(AccessTokenEntity $token, ScopeEntity $scope) {
        $accessTokenScope = new AccessTokenScope();
        $accessTokenScope->setAttribute("access_token", $token->getId());
        $accessTokenScope->setAttribute("scope", $scope->getId());
        $saved = $accessTokenScope->save();
        if ($saved) {
            return $saved;
        } else {
            throw new OAuthException(json_encode($accessTokenScope->errors));


        }
    }

    /**
     * Delete an access token
     *
     * @param  \League\OAuth2\Server\Entity\AccessTokenEntity $token The access token to delete
     *
     * @return int
     * @throws OAuthException
     */
    public function delete(AccessTokenEntity $token = null) {
        try {
            return self::deleteAll("access_token = :access_token", [":access_token" => $token->getId()]);
        } catch (Exception $e) {
            throw new OAuthException(json_encode($e));
        }
    }

}