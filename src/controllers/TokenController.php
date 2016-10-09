<?php

namespace verbi\yii2Oauth2Server\controllers;

use OAuth2\RequestInterface;
use OAuth2\ResponseInterface;

/**
 * @see OAuth2\Controller\TokenControllerInterface
 * @author Philip Verbist <philip.verbist@gmail.com>
 * @link https://github.com/verbi/Yii2-Oauth2-Server/
 * @license https://opensource.org/licenses/GPL-3.0
 */
class TokenController extends \yii\rest\Controller//extends \verbi\yii2Oauth2Server\base\controllers\TokenController
{
    public function actionIndex()
    {
        $response = $this->module->getServer()->handleTokenRequest();
        die($response->getParameters());
        return $response->getParameters();
    }
    
    
}
