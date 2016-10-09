<?php

namespace verbi\yii2Oauth2Server\filters\auth;

use \Yii;

/**
 * @author Philip Verbist <philip.verbist@gmail.com>
 * @link https://github.com/verbi/Yii2-Oauth2-Server/
 * @license https://opensource.org/licenses/GPL-3.0
 */
class CompositeAuth extends \yii\filters\auth\CompositeAuth
{
    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $server = Yii::$app->getModule('api/oauth2')->getServer();
        $server->verifyResourceRequest();

        return parent::beforeAction($action);
    }
}