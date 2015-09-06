Coming soon! 

1. Copy the migration file to your own migrations directory (inside of your yii2 project) , and run the migrations. 
Make sure you update the client_id and client_secret before using it in your project, since they are not safe the way they are added in the migration! 

2. Edit your yii2 configuration to include the following under "components"


```php
    'ResourceServerComponent'=> [
        'class' => 'rowasc\yii2oauthserver\components\ResourceServerComponent'
    ],
    'AuthServerComponent'=> [
        'class' => 'rowasc\yii2oauthserver\components\AuthServerComponent'
    ],
```

3. Edit your yii2 configuration to include the following under "modules"

```php
     'yii2oauthserver' => [
               'class' => '@vendor\rowasc\yii2oauthserver\Module',
     ]
```

4. In your .htaccess file, allow the Authorization headers to pass, since they will be stripped most of the time witouth this line


```php
    SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1
```

5. Have your base api controller extend " \rowasc\yii2oauthserver\controllers\ApiController" . 

6. Create an AuthorizationController, and extend \rowasc\yii2oauthserver\controllers\AuthorizationController in it.

7. In your User model, extend rowasc\yii2oauthserver\models\User

This will create an "/authorization/login" and a "/authorization/logout" endpoint which will allow you to get and expire bearer tokens for your api auth. 

### Examples: getting a new bearer token


POST /v1/authorization/login HTTP/1.1
Host: api.yii2-starter-kit.dev
Content-Type: application/json
Cache-Control: no-cache
Postman-Token: 7dd28588-b30d-a252-5c7e-5ecc9d1ab740

```json 
{
    "client_id": "client_name",
    "client_secret": "client_secret",
    "username": "webmaster",
    "password": "webmaster" 
}
```

Response: 

```json 
{
    "access_token": "ENMTcmTSgQTmwCpVbaO3AHHbhbJYgziiZzjWzWUd",
    "token_type": "Bearer",
    "expires_in": 43200
}
```

### Examples: invalidating the token so users do not have access to the api


POST /v1/authorization/logout HTTP/1.1
Host: api.yii2-starter-kit.dev
Content-Type: application/json
Authorization: Bearer ENMTcmTSgQTmwCpVbaO3AHHbhbJYgziiZzjWzWUd
Cache-Control: no-cache
Postman-Token: 9b86bad2-dd05-898b-5888-6047e0dd2772
```json
{
    "client_id": "client_name",
    "client_secret": "client_secret",
    "username": "webmaster",
    "password": "webmaster" 
}
```

Response: 
```json
{
  "status": true
}
```


### Help wanted: 

#### This project is in need of better tests.

 

