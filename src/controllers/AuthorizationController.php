<?php
/**
 * Created by PhpStorm.
 * User: rsuarez
 * Date: 11/29/14
 * Time: 8:50 PM
 */
namespace rowasc\yii2oauthserver\controllers;

use League\OAuth2\Server\Exception\OAuthException;
use Yii;
/**
 * TheLeague's OAuth 2.0 components
 */
use League\OAuth2\Server\Exception\InvalidClientException;
use League\OAuth2\Server\Exception\InvalidCredentialsException;
use League\OAuth2\Server\Exception\InvalidRequestException;
use League\OAuth2\Server\Exception\UnsupportedGrantTypeException;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Storage;
/**
 * API components
 */
use rowasc\yii2oauthserver\components\AuthorizationServer;
use rowasc\yii2oauthserver\components\AuthServerComponent;
use rowasc\yii2oauthserver\models\User;

use rowasc\components\Log;

/**
 * Common components
 */

class AuthorizationController extends ApiController {
    use Log;


    /**
     * By adding actions to the public_actions array, I make the specified action accesible to all public , even if they do not have a bearer in the authorization headers
     * @var array
     */
    protected $public_actions= ['login'];

    public $modelClass = 'api\modules\v1\models\ApiUserIdentity';
    public $log_category="authorization";

    /* @var $server AuthorizationServer */
    protected $server;
    /* @var $serverComponent AuthServerComponent */
    protected $serverComponent;

    public function beforeAction($action) {

        $this->serverComponent = Yii::$app->get('AuthServerComponent', false);

        if (isset($this->serverComponent)) {
            $this->server = $this->serverComponent->server;
        } else {
            throw new OAuthException(Yii::t('app', "Authentication Server Error"));
        }

        return parent::beforeAction($action);
    }

    /**
     * @return array
     * @throws InvalidRequestException
     * @throws InvalidCredentialsException
     * @throws InvalidClientException
     * @throws ServerException
     * @throws \League\OAuth2\Server\Exception\UnsupportedGrantTypeException
     */
    public function actionLogin() {
        $user=null;
        $passwordGrant = new PasswordGrant();
        $modelClass=$this->modelClass;
        $user = $modelClass::findOne(['username' => Yii::$app->request->post("username"), 'status' => $modelClass::STATUS_ACTIVE]);

        $passwordGrant->setVerifyCredentialsCallback(function ($username, $password) {
                /* @var $modelClass User */
                $modelClass = $this->modelClass;
                /* @var $user User */
                $user = $modelClass::findOne(['username' => $username, 'status' => $modelClass::STATUS_ACTIVE]);
                if ($user !== null && $user->validatePassword($password)) {
                    return $user->getId();
                } else {
                    return false;
                }
            });
        $this->server->addGrantType($passwordGrant);

        try {
            $response = $this->server->issueAccessToken("password");

        } catch (InvalidCredentialsException $e) {

            throw new InvalidCredentialsException($e->getMessage());

        } catch (InvalidClientException $e) {

            throw  new InvalidClientException($e->getMessage());

        } catch (InvalidRequestException $e) {

            throw new  InvalidRequestException($e->getMessage());

        } catch (UnsupportedGrantTypeException $e) {

            throw new OAuthException($e->getMessage());
        }
	    if (isset($response["access_token"])){
             $response["user_id"]=$user->getId();
        }
	    return $response;
    }


    public function actionLogout() {

        $return = ['status' => true];

        $email = null;

        $accessToken = $this->serverComponent->getAccessTokenFromHeader();
        $tokenEntity = $this->server->getAccessTokenStorage()->get($accessToken);

        /* @var $user User */
        $user = Yii::$app->getUser()->getIdentity();

        try {

            if (isset($user)) {
                $email = $user->email;
            }


            if ($tokenEntity) {
                $tokenEntity->expire();
            }
        } catch (\Exception $e) {
            $this->_logError(Yii::t('app', "Error while logging out user: $email"));
            $this->_logTrace($e);
            $return = ['status' => false];
        }

        return $return;
    }
}
