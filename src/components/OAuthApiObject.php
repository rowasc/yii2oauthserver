<?php
/**
 * Created by PhpStorm.
 * User: Romina Suarez
 * Date: 1/19/15
 * Time: 4:03 PM
 */

namespace rowasc\yii2oauthserver\components;

use Yii;
use yii\base\Object;
use rowasc\components\Log;
class OAuthApiObject extends Object {
    use Log;
    protected $log_category = 'oauth';
}