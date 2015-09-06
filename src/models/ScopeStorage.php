<?php
/**
 * Created by PhpStorm.
 * User: rsuarez
 * Date: 11/30/14
 * Time: 4:13 PM
 */

namespace rowasc\yii2oauthserver\models;


use League\OAuth2\Server\Entity\ScopeEntity;
use League\OAuth2\Server\Storage\ScopeInterface;

class ScopeStorage extends AbstractStorage implements ScopeInterface{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%oauth_scopes}}';
    }

    /**
     * Return information about a scope
     *
     * @param  string $scope     The scope
     * @param  string $grantType The grant type used in the request (default = "null")
     * @param  string $clientId  The client sending the request (default = "null")
     *
     * @return \League\OAuth2\Server\Entity\ScopeEntity
     */
    public function get($scope, $grantType = null, $clientId = null)
    {
        $oauthScope=self::findOne(["id"=>$scope]);
        $scopeEntity=null;
        if ($oauthScope!==null){
            $scopeEntity=(new ScopeEntity($this->getServer()))->hydrate([
                'id'=>$oauthScope->getAttribute("id"), "description"=>$oauthScope->getAttribute("description")
            ]);
        }
        return $scopeEntity;
    }

} 