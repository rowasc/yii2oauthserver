<?php
/**
 * Created by PhpStorm.
 * User: rsuarez
 * Date: 7/26/15
 * Time: 12:25 AM
 */

namespace rowasc\yii2oauthserver\controllers;

use League\OAuth2\Server\Exception\UnauthorizedClientException;
use Yii;
use yii\filters\ContentNegotiator;
use yii\rest\ActiveController;
use yii\web\Response;

use rowasc\components\Log;

use rowasc\yii2oauthserver\components\OAuthBearerFilter;
class ApiController extends ActiveController {
    use Log;

    /**
     * By adding actions to the public_actions array, I make the specified action accesible to all public , even if they do not have a bearer in the authorization headers
     * @var array
     */
    protected $public_actions= [];

    /**
     * @var array
     */
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',

    ];
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'index' => [
                'class' => 'yii\rest\IndexAction',
                'modelClass' => $this->modelClass,
                'prepareDataProvider' => [$this, 'prepareDataProvider'],
                'checkAccess' => [$this,'checkApiAccess']

            ],
            'view' => [
                'class' => 'api\modules\v1\controllers\actions\ViewApiAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkApiAccess'],
            ],
            'options' => [
                'class' => 'yii\rest\OptionsAction'
            ],

            'update' => [
                'class' => 'yii\rest\UpdateAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this,'checkAuthorizedAccessByAuthor']
            ],
            'create' => [
                'class' => 'yii\rest\CreateAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this,'checkApiAccess']
            ],
            'delete' => [
                'class' => 'yii\rest\DeleteAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAuthorizedAccessByAuthor'],
            ],
        ];
    }

    public function behaviors() {

        $public_actions= (isset(Yii::$app->params["public-actions"])) ? Yii::$app->params["public-actions"] :  [];

        $behaviors = parent::behaviors();

        if (isset($public_actions[$this->className()])) {
            $this->public_actions = array_merge($this->public_actions, $public_actions[$this->className()]);
        }

        $behaviors['OAuth'] = [
            'class' => OAuthBearerFilter::className(),
            'except' => $this->public_actions,
        ];

        return array_merge($behaviors, [
                'contentNegotiator' => [
                    'class' => ContentNegotiator::className(),
                    'formats' => [
                        'application/json' => Response::FORMAT_JSON,
                    ],
                ],
            ]);


    }

    /**
     * Checks if the user can run a particular action in the specific model(s). This function depends on the model having an author relationship.
     *
     * @param string $action the ID of the action to be executed
     * @param object $model  the model to be accessed. If null, it means no specific model is being accessed.
     * @param array  $params additional parameters
     *
     * @return bool
     * @throws UnauthorizedClientException if the user does not have access
     */
    public function checkAuthorizedAccessByAuthor($action, $model = null, $params = []) {

        $return = false;
        if (!in_array($action, $this->public_actions)) {

            // To check default Yii rest actions
            if (!isset($params["{$this->id}_id"]) && isset($this->actionParams['id'])) {
                $params["{$this->id}_id"] = $this->actionParams['id'];
            }
            /* @var $user User */
            $user = Yii::$app->getUser()->getIdentity();
            if ($user && $user->id === $model->author->id){
                $return = true;
            }
        } else {
            // It is not an authenticated method
            $return = true;
        }

        if (!$return) {
            throw new UnauthorizedClientException(Yii::t("rowasc.oauth","You are not authorized to run the action '{actionName}' on this resource", ['actionName'=>$action]));
        }

        return $return;
    }

    /**
     * Checks  if the current user has enough privileges to access the API "public" functionality
     * @param string $action the ID of the action to be executed
     * @param object $model the model to be accessed. If null, it means no specific model is being accessed.
     * @param array $params additional parameters
     * @throws UnauthorizedClientException if the user does not have access
     * @return boolean
     */
    public function checkApiAccess($action, $model = null, $params = []) {

        $return = false;
        if (!in_array($action, $this->public_actions)) {

            // To check default Yii rest actions
            if (!isset($params["{$this->id}_id"]) && isset($this->actionParams['id'])) {
                $params["{$this->id}_id"] = $this->actionParams['id'];
            }
            /* @var $user User */
            $user = Yii::$app->getUser()->getIdentity();
            if ($user){
                $return = true;
            }
        } else {
            // It is not an authenticated method
            $return = true;
        }

        if (!$return) {
            throw new UnauthorizedClientException(Yii::t('rowasc.oauth', "You are not authorized to access the API"));
        }

        return $return;
    }

    /**
     * Checks  if the current user has enough privileges to access the API private functionality by User
     * @param string $action the ID of the action to be executed
     * @param object $model the model to be accessed. If null, it means no specific model is being accessed.
     * @param array $params additional parameters
     * @throws UnauthorizedClientException if the user does not have access
     * @return boolean
     */
    public function checkAuthorizedAccessByUser($action, $model, $params = []) {
        $return = false;
        if (!in_array($action, $this->public_actions)) {

            // To check default Yii rest actions
            if (!isset($params["{$this->id}_id"]) && isset($this->actionParams['id'])) {
                $params["{$this->id}_id"] = $this->actionParams['id'];
            }
            /* @var $user User */
            $user = Yii::$app->getUser()->getIdentity();
            if ($user && $user->id === $model->id){
                $return = true;
            }
        } else {
            // It is not an authenticated method
            $return = true;
        }

        if (!$return) {
            throw new UnauthorizedClientException(Yii::t('rowasc.oauth',"You are not authorized to run the action '{actionName}' on this resource",['actionName'=>$action]));
        }

        return $return;
    }
}
