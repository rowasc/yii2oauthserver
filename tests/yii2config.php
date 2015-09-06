<?php

$config = [
    'name'=>'yii2oauthserver',
    'vendorPath'=>dirname(dirname(__DIR__)).'/../../vendor',
    'extensions' => require(__DIR__ . '/../vendor/yiisoft/extensions.php'),
    'sourceLanguage'=>'en-US',
    'language'=>'en-US',
    'bootstrap' => ['log']
];
return $config;