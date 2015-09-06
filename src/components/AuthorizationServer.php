<?php
/**
 * Extending from OAuth 2.0 Authorization Server
 */

namespace rowasc\yii2oauthserver\components;
use League\OAuth2\Server\Grant\GrantTypeInterface;
use League\OAuth2\Server\TokenType\Bearer;
use League\OAuth2\Server\Exception\InvalidGrantException;
use League\OAuth2\Server\Exception\UnsupportedGrantTypeException;
use League\OAuth2\Server\Exception\InvalidRequestException;
use \Symfony\Component\HttpFoundation\Request;
use Yii;
/**
 * OAuth 2.0 authorization server class
 */
class AuthorizationServer extends \League\OAuth2\Server\AuthorizationServer
{
    /**
     * The delimeter between scopes specified in the scope query string parameter
     * The OAuth 2 specification states it should be a space but most use a comma
     * @var string
     */
    protected $scopeDelimiter = ' ';

    /**
     * The TTL (time to live) of an access token in seconds (default: 3600)
     * @var integer
     */
    protected $accessTokenTTL = 3600;

    /**
     * The registered grant response types
     * @var array
     */
    protected $responseTypes = [];

    /**
     * The registered grant types
     * @var array
     */
    protected $grantTypes = [];

    /**
     * Require the "scope" parameter to be in checkAuthoriseParams()
     * @var boolean
     */
    protected $requireScopeParam = false;

    /**
     * Default scope(s) to be used if none is provided
     * @var string|array
     */
    protected $defaultScope;

    /**
     * Require the "state" parameter to be in checkAuthoriseParams()
     * @var boolean
     */
    protected $requireStateParam = false;

    /**
     * Create a new OAuth2 authorization server
     * @return self
     */
    public function __construct()
    {
        // Set Bearer as the default token type
        $this->setTokenType(new Bearer());

        parent::__construct();

        return $this;
    }

    /**
     * Enable support for a grant
     * @param  GrantTypeInterface $grantType  A grant class which conforms to Interface/GrantTypeInterface
     * @param  null|string        $identifier An identifier for the grant (autodetected if not passed)
     * @return self
     */
    public function addGrantType(GrantTypeInterface $grantType, $identifier = null)
    {
        if (is_null($identifier)) {
            $identifier = $grantType->getIdentifier();
        }

        // Inject server into grant
        $grantType->setAuthorizationServer($this);

        $this->grantTypes[$identifier] = $grantType;

        if (!is_null($grantType->getResponseType())) {
            $this->responseTypes[] = $grantType->getResponseType();
        }

        return $this;
    }

    /**
     * Check if a grant type has been enabled
     * @param  string $identifier The grant type identifier
     * @return boolean Returns "true" if enabled, "false" if not
     */
    public function hasGrantType($identifier)
    {
        return (array_key_exists($identifier, $this->grantTypes));
    }

    /**
     * Returns response types
     * @return array
     */
    public function getResponseTypes()
    {
        return $this->responseTypes;
    }

    /**
     * Require the "scope" parameter in checkAuthoriseParams()
     * @param  boolean $require
     * @return self
     */
    public function requireScopeParam($require = true)
    {
        $this->requireScopeParam = $require;

        return $this;
    }

    /**
     * Is the scope parameter required?
     * @return bool
     */
    public function scopeParamRequired()
    {
        return $this->requireScopeParam;
    }

    /**
     * Default scope to be used if none is provided and requireScopeParam() is false
     * @param string $default Name of the default scope
     * @return self
     */
    public function setDefaultScope($default = null)
    {
        $this->defaultScope = $default;

        return $this;
    }

    /**
     * Default scope to be used if none is provided and requireScopeParam is false
     * @return string|null
     */
    public function getDefaultScope()
    {
        return $this->defaultScope;
    }

    /**
     * Require the "state" paremter in checkAuthoriseParams()
     * @return bool
     */
    public function stateParamRequired()
    {
        return $this->requireStateParam;
    }

    /**
     * Require the "state" paremter in checkAuthoriseParams()
     * @param  boolean $require
     * @return self
     */
    public function requireStateParam($require = true)
    {
        $this->requireStateParam = $require;

        return $this;
    }

    /**
     * Get the scope delimiter
     * @return string The scope delimiter (default: ",")
     */
    public function getScopeDelimiter()
    {
        return $this->scopeDelimiter;
    }

    /**
     * Set the scope delimiter
     * @param string $scopeDelimiter
     * @return self
     */
    public function setScopeDelimiter($scopeDelimiter = ' ')
    {
        $this->scopeDelimiter = $scopeDelimiter;

        return $this;
    }

    /**
     * Get the TTL for an access token
     * @return int The TTL
     */
    public function getAccessTokenTTL()
    {
        return $this->accessTokenTTL;
    }

    /**
     * Set the TTL for an access token
     * @param int $accessTokenTTL The new TTL
     * @return self
     */
    public function setAccessTokenTTL($accessTokenTTL = 3600)
    {
        $this->accessTokenTTL = $accessTokenTTL;

        return $this;
    }

    /**
     * Issue an access token
     *
     * @param null $grantType
     *
     * @return array Authorise request parameters
     * @throws InvalidGrantException
     * @throws InvalidRequestException
     * @throws UnsupportedGrantTypeException
     */
    public function issueAccessToken($grantType=null)
    {
        if (is_null($grantType)) {
            throw new InvalidRequestException('grant_type');
        }
        // Ensure grant type is one that is recognised and is enabled
        if (!in_array($grantType, array_keys($this->grantTypes))) {
            throw new UnsupportedGrantTypeException($grantType);
        }

        // Complete the flow
        return $this->getGrantType($grantType)->completeFlow();
    }

    /**
     * Return a grant type class
     * @param  string $grantType The grant type identifier
     * @return \GrantTypeInterface
     * @throws
     */
    public function getGrantType($grantType)
    {
        if (isset($this->grantTypes[$grantType])) {
            return $this->grantTypes[$grantType];
        }

        throw new InvalidGrantException($grantType);
    }

    /**
     * Gets the Request object. It will create one from the globals if one is not set.
     *
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getRequest()
    {
        if ($this->request === null) {
            $this->request= Request::createFromGlobals();
            $this->request->attributes = array_merge (Yii::$app->getRequest()->getBodyParams(), Yii::$app->getRequest()->get());
            foreach($this->request->attributes as $key=>$value){
                $this->request->request->set($key, $this->request->attributes[$key]);
            }
        }
        return $this->request;
    }


}
