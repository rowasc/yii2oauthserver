<?php
/**
 * Created by PhpStorm.
 * User: rsuarez
 * Date: 11/30/14
 * Time: 7:14 PM
 */

namespace rowasc\yii2oauthserver\models;

use League\OAuth2\Server\Storage;
use League\OAuth2\Server\AbstractServer;

use yii\db\ActiveRecord;

class AbstractStorage extends ActiveRecord implements Storage\StorageInterface{


    /**
     * Server
     * @var \League\OAuth2\Server\AbstractServer $server
     */
    protected $server;

    /**
     * Set the server
     * @param \League\OAuth2\Server\AbstractServer $server
     * @return self
     */
    public function setServer(AbstractServer $server)
    {
        $this->server = $server;

        return $this;
    }

    /**
     * Return the server
     * @return \League\OAuth2\Server\AbstractServer
     */
    protected function getServer()
    {
        return $this->server;
    }
    public function delete(){
        parent::delete();
    }

}