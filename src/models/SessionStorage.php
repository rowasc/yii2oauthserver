<?php
/**
 * Created by PhpStorm.
 * User: rsuarez
 * Date: 11/30/14
 * Time: 4:07 PM
 */

namespace rowasc\yii2oauthserver\models;

use League\OAuth2\Server\Entity\AccessTokenEntity;
use League\OAuth2\Server\Entity\AuthCodeEntity;
use League\OAuth2\Server\Entity\ScopeEntity;
use League\OAuth2\Server\Entity\SessionEntity;
use League\OAuth2\Server\Exception\OAuthException;
use League\OAuth2\Server\Storage;
use yii\db\Query;

class SessionStorage extends AbstractStorage implements Storage\SessionInterface
{


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%oauth_sessions}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['owner_type', 'owner_id','client_id'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        return [
            'owner_type',
            'owner_id',
            'client_id',
            'client_redirect_uri'
        ];
    }

    /**
     * Get a session from an access token
     *
     * @param  \League\OAuth2\Server\Entity\AccessTokenEntity $accessToken The access token
     *
     * @return SessionEntity
     * @throws OAuthException
     */
    public function getByAccessToken(AccessTokenEntity $accessToken)
    {
        $querySessions=new Query();
        $session=null;
        $sessionResult=$querySessions->select(['{{%oauth_sessions}}.id as id', '{{%oauth_sessions}}.owner_type as owner_type', '{{%oauth_sessions}}.owner_id as owner_id', '{{%oauth_sessions}}.client_id as client_id', '{{%oauth_sessions}}.client_redirect_uri as redirect_uri'])
            ->from('{{%oauth_sessions}}')
            ->innerJoin('oauth_access_tokens', 'oauth_access_tokens.session_id={{%oauth_sessions}}.id')
            ->where(['oauth_access_tokens.access_token'=> $accessToken->getId()])
            ->one();
        if ($sessionResult){
            $session =new SessionEntity($this->getServer());
            $session->setId($sessionResult['id']);
            $session->setOwner($sessionResult['owner_type'], $sessionResult['owner_id']);
            if (!$session->save()){
                throw new OAuthException(json_encode($session->errors));
            }
        }else{
            throw new OAuthException(json_encode($sessionResult));
        }

        return $session;
    }

    /**
     * Get a session from an auth code
     *
     * @param  \League\OAuth2\Server\Entity\AuthCodeEntity $authCode The auth code
     *
     * @return SessionEntity
     * @throws OAuthException
     */
    public function getByAuthCode(AuthCodeEntity $authCode)
    {
        $querySessions=new Query();
        $session=null;
        $sessionResult=$querySessions->select(['{{%oauth_sessions}}.id as id', '{{%oauth_sessions}}.owner_type as owner_type', '{{%oauth_sessions}}.owner_id as owner_id', '{{%oauth_sessions}}.client_id as client_id', '{{%oauth_sessions}}.client_redirect_uri as redirect_uri'])
            ->from('{{%oauth_sessions}}')
            ->innerJoin('oauth_auth_codes', '{{%oauth_auth_codes}}.session_id={{%oauth_sessions}}.id')
            ->where(['{{%oauth_auth_codes}}.auth_code'=> $authCode->getId()])
            ->one();

        if ($sessionResult){
            $session =new SessionEntity($this->getServer());
            $session->setId($sessionResult['id']);
            $session->setOwner($sessionResult['owner_type'], $sessionResult['owner_id']);
            if (!$session->save()){
                throw new OAuthException(json_encode($session->errors));
            }

        }else{
            throw new OAuthException(json_encode($sessionResult));
        }

        return $session;
    }


    /**
     * Get a session's scopes
     *
     * @param  \League\OAuth2\Server\Entity\SessionEntity
     *
     * @return array Array of \League\OAuth2\Server\Entity\ScopeEntity
     */
    public function getScopes(SessionEntity $session)
    {
        $querySessions=new Query();
        $sessionScopes=array();
        $sessionScopeResults=$querySessions->select(['{{%oauth_scopes}}.*'])
            ->from('{{%oauth_sessions}}')
            ->innerJoin('{{%oauth_session_scopes}}', '{{%oauth_sessions}}.id={{%oauth_session_scopes}}.session_id')
            ->innerJoin('{{%oauth_scopes}}', '{{%oauth_scopes}}.id={{%oauth_session_scopes}}.scope')
            ->where(['{{%oauth_sessions}}.id'=> $session->getId()])
            ->all();

        foreach($sessionScopeResults as $sessionScopeResult){
            $sessionScopes[]= (new ScopeEntity($this->getServer()))->hydrate([
                'id'=>$sessionScopeResult['id'],
                'description'=>$sessionScopeResult['description']
            ]);
        }

        return $sessionScopes;
    }

    /**
     * Create a new session
     *
     * @param  string $ownerType         Session owner's type (user, client)
     * @param  string $ownerId           Session owner's ID
     * @param  string $clientId          Client ID
     * @param  string $clientRedirectUri Client redirect URI (default = null)
     *
     * @return int The session's ID
     * @throws OAuthException
     */
    public function create($ownerType, $ownerId, $clientId, $clientRedirectUri = null)
    {
        $sessionStorage = new SessionStorage();
        $sessionStorage->setAttribute("owner_type",$ownerType);
        $sessionStorage->setAttribute("owner_id", $ownerId);
        $sessionStorage->setAttribute("client_id",$clientId);
        if ($sessionStorage->save()){
            return $sessionStorage->getPrimaryKey();
        }else{
            throw new OAuthException(json_encode($sessionStorage->errors));
        }
    }

    /**
     * Associate a scope with a session
     *
     * @param  \League\OAuth2\Server\Entity\SessionEntity $session The session
     * @param  \League\OAuth2\Server\Entity\ScopeEntity   $scope   The scope
     *
     * @return null|string
     * @throws OAuthException
     */
    public function associateScope(SessionEntity $session, ScopeEntity $scope)
    {
        $sessionScopesStorage = new SessionScopesStorage();
        $sessionScopesStorage->setAttribute("session_id", $session->getId());
        $sessionScopesStorage->setAttribute("scope", $scope->getId());
        if ($sessionScopesStorage->save()){
            return $sessionScopesStorage->getPrimaryKey();
        }else{
            throw new OAuthException(json_encode($sessionScopesStorage->errors));
        }
    }
}