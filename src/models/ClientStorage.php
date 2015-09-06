<?php
/**
 * Created by PhpStorm.
 * User: rsuarez
 * Date: 11/30/14
 * Time: 4:12 PM
 */

namespace rowasc\yii2oauthserver\models;

use League\OAuth2\Server\Entity\ClientEntity;
use League\OAuth2\Server\Entity\SessionEntity;
use League\OAuth2\Server\Storage;
use yii\db\Query;

class ClientStorage extends AbstractStorage implements Storage\ClientInterface{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%oauth_clients}}';
    }
    /**
     * Validate a client
     *
     * @param  string $clientId     The client's ID
     * @param  string $clientSecret The client's secret (default = "null")
     * @param  string $redirectUri  The client's redirect URI (default = "null")
     * @param  string $grantType    The grant type used (default = "null")
     *
     * @return \League\OAuth2\Server\Entity\ClientEntity
     */
    public function get($clientId, $clientSecret = null, $redirectUri = null, $grantType = null)
    {
        $client=null;
        $selectTargets="{{%oauth_clients}}.id, {{%oauth_clients}}.name";

        if ($redirectUri!==null && is_string($redirectUri) && strlen($redirectUri)>0){
            $selectTargets="{{%oauth_clients}}.*, {{%oauth_client_redirect_uris}}.*";
        }

        $clientStorageQueryBuilder=new Query();
        $clientStorageQueryBuilder->select($selectTargets)->from("{{%oauth_clients}}")->where("{{%oauth_clients}}.id=:oauth_client_id AND {{%oauth_clients}}.secret=:oauth_client_secret_where", [":oauth_client_id"=>$clientId,":oauth_client_secret_where"=>$clientSecret]);

        /**
         * Regarding oauth_client_redirect_uris:
         * You may or may not have this table. If you don't, and don't need it , as described in the league docs for each grant,
         * you won't really pass this condition, and that's why it's implemented this way
         */
        if ($redirectUri!==null && is_string($redirectUri) && strlen($redirectUri)>0){
            $clientStorageQueryBuilder->innerJoin("{{%oauth_client_redirect_uris}}","{{%oauth_clients}}.id = {{%oauth_client_redirect_uris}}.client_id")->where(
               ["{{%oauth_client_redirect_uris}}.redirect_uri"=>$redirectUri]
            );
        }

        $clientStorageResult=$clientStorageQueryBuilder->one();
        if (isset($clientStorageResult['id']) && isset($clientStorageResult['name'])){
            $client=(new ClientEntity($this->getServer()))->hydrate([
                "id"=>$clientStorageResult["id"],
                "name"=>$clientStorageResult["name"],
            ]);
        }
        return $client;
    }

    /**
     * Get the client associated with a session
     *
     * @param  \League\OAuth2\Server\Entity\SessionEntity $session The session
     *
     * @return \League\OAuth2\Server\Entity\ClientEntity
     */
    public function getBySession(SessionEntity $session)
    {
        $client=null;
        $clientStorageQueryBuilder= new Query();
        $clientBySessionResult = $clientStorageQueryBuilder->select("{{%oauth_clients}}.id, {{%oauth_clients}}.name")
            ->from("{{%oauth_clients}}")
            ->innerJoin("{{%oauth_sessions}}","{{%oauth_clients}}.id={{%oauth_sessions}}.client_id")
            ->where(["{{%oauth_sessions}}.id"=>$session->getId()])
            ->one();

        if (count($clientBySessionResult) === 1) {
            $client = (new ClientEntity($this->server))->hydrate([
                'id'    =>  $clientBySessionResult[0]['id'],
                'name'  =>  $clientBySessionResult[0]['name'],
            ]);
        }

        return $client;

    }
}