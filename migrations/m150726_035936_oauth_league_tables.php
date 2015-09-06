<?php

use yii\db\Schema;
use yii\db\Migration;

class m150726_035936_oauth_league_tables extends Migration
{  public function safeUp()
{
    $tableOptions = null;
    if ($this->db->driverName === 'mysql') {
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
    }
    /**
     * oAuth 2.0 clients
     */
    $this->createTable('{{%oauth_clients}}', [
        'id' =>Schema::TYPE_STRING . ' NOT NULL',
        'secret' => Schema::TYPE_STRING . ' NOT NULL',
        'name' => Schema::TYPE_STRING . ' NOT NULL'
    ], $tableOptions);
    $this->addPrimaryKey("oauth_clients_pk","{{%oauth_clients}}","id");

    $this->insert("{{%oauth_clients}}",["secret"=>"client_secret" , "id"=>"client_name","name"=>"client name"]);

    /**
     * oAuth2.0 scopes
     */
    $this->createTable('{{%oauth_scopes}}', [
        'id' => Schema::TYPE_STRING. '(25) NOT NULL',
        'description' => Schema::TYPE_STRING . ' NOT NULL'
    ], $tableOptions);
    $this->addPrimaryKey("oauth_scopes_pk","{{%oauth_scopes}}","id");

    $this->insert("{{%oauth_scopes}}",["id"=>"username", "description"=>"Access your username"]);
    $this->insert("{{%oauth_scopes}}",["id"=>"email","description"=>"Access your email"]);
    $this->insert("{{%oauth_scopes}}",["id"=>"status","description"=>"Access your status"]);

    /**
     * oAuth 2.0 sessions table
     */

    $this->createTable('{{%oauth_sessions}}', [
        'id' => Schema::TYPE_PK,
        'owner_type' => Schema::TYPE_STRING . ' NOT NULL',
        'owner_id' => Schema::TYPE_STRING . ' NOT NULL',
        'client_id' => Schema::TYPE_STRING . ' NOT NULL',
        'client_redirect_uri' => Schema::TYPE_STRING . '  NULL',
    ], $tableOptions);

    $this->insert("{{%oauth_sessions}}",["owner_type"=>"client", "owner_id"=>"client_name","client_id"=>"client_name"]);
    $this->insert("{{%oauth_sessions}}",["owner_type"=>"user", "owner_id"=>1,"client_id"=>"client_name"]);

    /**
     * oAuth2.0 Access Tokens
     */
    $this->createTable('{{%oauth_access_tokens}}', [
        'access_token' => Schema::TYPE_STRING . '(255) NOT NULL',
        'session_id' => Schema::TYPE_INTEGER. '(11) NOT NULL',
        'expire_time' => Schema::TYPE_INTEGER. '(10) NOT NULL',
    ], $tableOptions);

    $this->addPrimaryKey("access_token_pk","{{%oauth_access_tokens}}","access_token");

    $this->insert("{{%oauth_access_tokens}}",["access_token"=>"iamgod", "session_id"=>1,"expire_time"=>time()+86400]);
    $this->insert("{{%oauth_access_tokens}}",["access_token"=>"rominasat", "session_id"=>2,"expire_time"=>time()+86400]);
    /**
     * oAuth2.0 access token scopes
     */
    $this->createTable('{{%oauth_access_token_scopes}}', [
        'id' => Schema::TYPE_PK,
        'access_token' => Schema::TYPE_STRING. '(255) NOT NULL',
        'scope' => Schema::TYPE_STRING. '(25) NOT NULL',
    ], $tableOptions);


    $this->insert("{{%oauth_access_token_scopes}}",["access_token"=>"rominasat", "scope"=>"username"]);
    $this->insert("{{%oauth_access_token_scopes}}",["access_token"=>"rominasat", "scope"=>"email"]);
    $this->insert("{{%oauth_access_token_scopes}}",["access_token"=>"rominasat", "scope"=>"status"]);
    $this->insert("{{%oauth_access_token_scopes}}",["access_token"=>"iamgod", "scope"=>"email"]);
    /**
     * oAuth2.0 sessions scopes
     */
    $this->createTable('{{%oauth_session_scopes}}', [
        'id' => Schema::TYPE_PK,
        'session_id' =>  Schema::TYPE_INTEGER. '(11) NOT NULL',
        'scope' => Schema::TYPE_STRING. '(25) NOT NULL',
    ], $tableOptions);
    $this->insert("{{%oauth_session_scopes}}",["session_id"=>1, "scope"=>"username"]);


    /**
     * Foreign keys added at last because we don't want validation issues
     */
    $this->addForeignKey("oauth_sessions_client_foreign_key", "{{%oauth_sessions}}","client_id","{{%oauth_clients}}","id","CASCADE");
    $this->addForeignKey("oauth_access_token_fk", "{{%oauth_access_token_scopes}}","access_token","{{%oauth_access_tokens}}","access_token","CASCADE");
    $this->addForeignKey("oauth_access_token_scopes_fk", "{{%oauth_access_token_scopes}}","scope","{{%oauth_scopes}}","id","CASCADE");
    $this->addForeignKey("oauth_access_tokens_session_id_fk", "{{%oauth_access_tokens}}","session_id","{{%oauth_sessions}}","id","CASCADE");

    $this->addForeignKey("oauth_session_id_fk", "{{%oauth_session_scopes}}","session_id","{{%oauth_sessions}}","id","CASCADE");
    $this->addForeignKey("oauth_scope_fk", "{{%oauth_session_scopes}}","scope","{{%oauth_scopes}}","id","CASCADE");

}

    public function safeDown()
    {
        $tableToDrop=['{{%oauth_session_scopes}}','{{%oauth_access_token_scopes}}','{{%oauth_access_tokens}}','{{%oauth_sessions}}','{{%oauth_scopes}}','{{%oauth_clients}}','{{%user}}'];
        foreach($tableToDrop as $table){

            try{
                $this->dropTable($table);
            }catch (\yii\base\Exception $e){
                echo "Exception : maybe the table $table does not exist? ";
            }

        }
        return false;
    }
}
