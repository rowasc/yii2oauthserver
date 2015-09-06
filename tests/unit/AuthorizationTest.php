<?php
namespace rowasc\yii2oauthserver\tests;
include __DIR__ . '/PasswordGrant.php';

use League\OAuth2\Server\TokenType\Bearer;
use rowasc\yii2oauthserver\components\AuthorizationServer;
use rowasc\yii2oauthserver\models\ClientStorage;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Codeception\Util\Stub;
use Yii;
class AuthorizationTest extends \Codeception\TestCase\Test
{
    /**
     * @var \OauthTester
     */
    protected $tester;


    protected function getVerifyCredentialsCallbackTrue ($username,$password){
        /* @var $modelClass User */
        $request = $this->getRequest();
        return $request->get("username")===$username && $request->get("password")===$password?1:null;
    }

    protected function _before()
    {
    }

    protected function _after()
    {
    }
    public function testSetRequest(){

        /**
         * Initializating the authorization server
         */
        $authorizationServer = new AuthorizationServer();
        $authorizationServer->setRequest(new Request());
        $this->assertNotEmpty($authorizationServer->getRequest(),"Authorization server's request is set");
        $this->assertNotEquals(Request::METHOD_POST,$authorizationServer->getRequest()->getMethod(), "Request is not a POST request");
        $this->assertObjectHasAttribute("request",$authorizationServer->getRequest(), "Request object has a request attribute");
        $this->assertNull($authorizationServer->getRequest()->request->get("client_id"), "Request's client_id is not set");
        $this->assertNull($authorizationServer->getRequest()->request->get("client_secret"), "Request's client_secret is not set");

        /**
         * valid request
         */
        $authorizationServer =null;
        $authorizationServer = new AuthorizationServer();
        $authorizationServer->setRequest($this->getRequest());
        $this->assertNotEmpty($authorizationServer->getRequest(),"Authorization server's request is set");
        $this->assertEquals(Request::METHOD_POST,$authorizationServer->getRequest()->getMethod(), "Request is a POST request");
        $this->assertObjectHasAttribute("request",$authorizationServer->getRequest(), "Request object has a request attribute");
        $this->assertNotNull($authorizationServer->getRequest()->request->get("client_id"), "Request's client_id is set");
        $this->assertEquals("client_name",$authorizationServer->getRequest()->request->get("client_id"), "Request's client_id equals 'client_name'");
        $this->assertNotNull($authorizationServer->getRequest()->request->get("client_secret"), "Request's client_secret is set");
        $this->assertEquals("client_secret",$authorizationServer->getRequest()->request->get("client_secret"), "Request's client_secret equals 'client_secret'");
    }

    public function testSetGrant(){

        /**
         * Initializating the authorization server
         */
        $authorizationServer = new AuthorizationServer();

        $passwordGrant= Stub::make('rowasc\yii2oauthserver\tests\PasswordGrant', ['completeFlow' => true]);
        $authorizationServer->addGrantType($passwordGrant);
        $this->assertTrue($authorizationServer->hasGrantType("password"), "Authorization server does not have a password grant");
    }


    private  function getRequest(){

        /**
         * Creating a request from scratch
         */
        $headers = array();
        $headers["Authorization"]="Bearer bplSD3tlNIvPVQFpo3owKoCyiyIDsnpBYd7NHOMk";

        $request = new Request();
        $request->setMethod(Request::METHOD_POST);

        $request->request->set('client_id', "client_name");
        $request->request->set('client_secret', "client_secret");
        $request->request->set('username', "webmaster");
        $request->request->set('password', "webmaster");

        $request->headers= new HeaderBag($headers);
        return $request;
    }

    /**
     * Testing that complete flow is called for the grant we set
     *
     */
    public  function testAuthorizationServerValidCredentials(){


        /**
         * Initializating the authorization server
         */
        $authorizationServer = new AuthorizationServer();
        $authorizationServer->setClientStorage(new ClientStorage());
        $request = $this->getRequest();
        $request->request->set("client_id","romina");
        $authorizationServer->setRequest($request);


        $functionTrueCompleteFlow = function () {
            $userId =null;
            $bearer = new Bearer();
            $userId=$this->getVerifyCredentialsCallbackTrue("webmaster","webmaster");
            if ($userId!==null){
                $bearer->setParam("access_token", "123456");
                $bearer->setParam("expires_in",time());
            }
            return $bearer;
        };


        /**
         * Initializating the Grant type. In this case, we are using PasswordGrant
         */
        $passwordGrant= Stub::make('rowasc\yii2oauthserver\tests\PasswordGrant', ['completeFlow' => $functionTrueCompleteFlow,'getTokenType'=>new Bearer()]);
        /**
         * Does nothing, basically
         */
        $passwordGrant->setVerifyCredentialsCallback(function(){});

        $authorizationServer->addGrantType($passwordGrant);
        $response = $authorizationServer->issueAccessToken("password");
        $this->assertNull($response->getParam("bearer"), " 'response' has an invalid attribute");//sanity check, in case a non existing attribute stops returning null because the library changed the getParam function
        $this->assertNotNull($response->getParam("access_token"), " 'response' does not have the access_token attribute");
        $this->assertNotNull($response->getParam("expires_in"), " 'response' does not have the expires_in attribute");
        $this->assertEquals("123456",$response->getParam("access_token"), " access_token does not match what we expected");

    }


    /**
     * Testing that complete flow is called for the grant we set
     *
     */
    public  function testAuthorizationServerInvalidCredentials(){


        /**
         * Initializating the authorization server
         */
        $authorizationServer = new AuthorizationServer();
        $authorizationServer->setClientStorage(new ClientStorage());
        $request = $this->getRequest();
        $authorizationServer->setRequest($request);

        $functionTrueCompleteFlow = function () {
            $userId =null;
            $bearer = new Bearer();
            $userId=$this->getVerifyCredentialsCallbackTrue("tester","webmaster");
            if ($userId!==null){
                $bearer->setParam("access_token", "123456");
                $bearer->setParam("expires_in",time());
            }
            return $bearer;
        };


        /**
         * Initializating the Grant type. In this case, we are using PasswordGrant
         */
        $passwordGrant= Stub::make('rowasc\yii2oauthserver\tests\PasswordGrant', ['completeFlow' => $functionTrueCompleteFlow,'getTokenType'=>new Bearer()]);
        /**
         * Does nothing, basically
         */
        $passwordGrant->setVerifyCredentialsCallback(function(){});

        $authorizationServer->addGrantType($passwordGrant);
        $response = $authorizationServer->issueAccessToken("password");
        $this->assertNull($response->getParam("bearer"), " 'response' has an invalid attribute");//sanity check, in case a non existing attribute stops returning null because the library changed the getParam function
        $this->assertNull($response->getParam("access_token"), " 'response' does not have the access_token attribute");
        $this->assertNull($response->getParam("expires_in"), " 'response' does not have the expires_in attribute");
    }

}