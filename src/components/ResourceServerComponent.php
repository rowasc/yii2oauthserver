<?php
/**
 * Created by PhpStorm.
 * User: rominas
 * Date: 12/18/14
 * Time: 3:15 PM
 */

namespace rowasc\yii2oauthserver\components;
use rowasc\yii2oauthserver\models\AccessTokenStorage;
use rowasc\yii2oauthserver\models\ClientStorage;
use rowasc\yii2oauthserver\models\ScopeStorage;
use rowasc\yii2oauthserver\models\SessionStorage;

use League\OAuth2\Server\ResourceServer;

class ResourceServerComponent extends OAuthApiObject {
    /* @var $server ResourceServer */
    public $server;

    public function init() {
        $sessionStorage = new SessionStorage();
        $accessTokenStorage = new AccessTokenStorage();
        $clientStorage = new ClientStorage();
        $scopeStorage = new ScopeStorage();

        $this->server = new ResourceServer(
            $sessionStorage,
            $accessTokenStorage,
            $clientStorage,
            $scopeStorage
        );

        parent::init();
    }


}