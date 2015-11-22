<?php
/**
 * Created by PhpStorm.
 * User: rominas
 * Date: 12/19/14
 * Time: 3:50 PM
 */

namespace rowasc\yii2oauthserver\components;
use League\OAuth2\Server\Exception\AccessDeniedException;
use League\OAuth2\Server\Exception\OAuthException;
use Yii;
use yii\filters\auth\AuthMethod;
use yii\web\ServerErrorHttpException;

class OAuthBearerFilter extends AuthMethod {
    /**
     * @var string the HTTP authentication realm
     */
    public $realm = 'api';

    /**
     * @inheritdoc
     */
    public function authenticate($user, $request, $response) {

        $accessToken = Yii::$app->AuthServerComponent->getAccessTokenFromHeader();
        $identity = null;
        try {
            if ($accessToken && Yii::$app->ResourceServerComponent->server->isValidRequest(false, $accessToken)) {
                $identity = $user->loginByAccessToken($accessToken);
            }
        } catch (AccessDeniedException $e) {
            $this->handleFailure($response);
            Yii::error(Yii::t('rowasc.oauth', 'Access Denied: authorization error'), 'api');
        } catch (\Exception $s) {
            throw new ServerErrorHttpException($s->getMessage(), 500);
        }

        return $identity;
    }

    /**
     * @inheritdoc
     */
    public function handleFailure($response) {
        throw new OAuthException(Yii::t('rowasc.oauth', 'Access Denied: authorization error.'));
    }

    /**
     * @inheritdoc
     */
    public function challenge($response) {
        $response->getHeaders()->set('WWW-Authenticate', "Bearer realm=\"{$this->realm}\"");
    }
}