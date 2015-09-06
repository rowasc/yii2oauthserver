<?php
/**
 * Created by PhpStorm.
 * User: Romina Suarez
 * Date: 3/17/15
 * Time: 3:30 PM
 */

namespace rowasc\yii2oauthserver\components;

use rowasc\yii2oauthserver\models\AccessTokenStorage;
use rowasc\yii2oauthserver\models\ClientStorage;
use rowasc\yii2oauthserver\models\ScopeStorage;
use rowasc\yii2oauthserver\models\SessionStorage;

use Yii;

class AuthServerComponent extends OAuthApiObject {
    const OAUTH_TOKEN_EXPIRATION=43200;
    /* @var $server AuthorizationServer */
    public $server;

    public function init() {

        $this->server = new AuthorizationServer();
        $this->server->setSessionStorage(new SessionStorage());
        $this->server->setAccessTokenStorage(new AccessTokenStorage());
        $this->server->setClientStorage(new ClientStorage());
        $this->server->setScopeStorage(new ScopeStorage());

        $token_expiration = (isset(Yii::$app->params["oauth_token_expiration"])) ? Yii::$app->params["oauth_token_expiration"] : self::OAUTH_TOKEN_EXPIRATION;

        $this->server->setAccessTokenTTL($token_expiration);

        parent::init();
    }

    public static function getAccessTokenFromHeader() {
        $authHeader = Yii::$app->getRequest()->getHeaders()->get('Authorization');
        $accessToken = null;
        if ($authHeader !== null && preg_match("/^Bearer\\s+(.*?)$/", $authHeader, $matches)) {
            $accessToken = $matches[1];
        } elseif ($accessToken === null && $authHeader != "" && $authHeader != null) {
            $accessToken = $authHeader;
        }
        return $accessToken;
    }


}