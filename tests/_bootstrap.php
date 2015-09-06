<?php

// This is global bootstrap for autoloading

require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');


$kernel = \AspectMock\Kernel::getInstance();
$kernel->init([
        'debug' => true,
        'includePaths' => [__DIR__.'/../src']
    ]);

//
$application = new yii\console\Application( yii\helpers\ArrayHelper::merge(
    require('yii2config.php'),
    [
        'id' => 'app-common',
        'basePath' => dirname(__DIR__),

    ]
));
Yii::setAlias('@yii2oauthserver', dirname(__DIR__) ."/../src/");
Yii::setAlias('@tests', dirname(__DIR__));
