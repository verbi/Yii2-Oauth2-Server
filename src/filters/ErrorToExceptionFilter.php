<?php

namespace verbi\yii2Oauth2Server\filters;

use Yii;
use yii\base\Controller;
use filsh\yii2\oauth2server\Module;
use verbi\yii2Oauth2Server\exceptions\HttpException;

/**
 * @author Philip Verbist <philip.verbist@gmail.com>
 * @link https://github.com/verbi/Yii2-Oauth2-Server/
 * @license https://opensource.org/licenses/GPL-3.0
 */
class ErrorToExceptionFilter extends \filsh\yii2\oauth2server\filters\ErrorToExceptionFilter
{
    /**
     * @inheritdoc
     */
    public function events()
    {
        return [Controller::EVENT_AFTER_ACTION => 'afterAction'];
    }

    /**
     * @param ActionEvent $event
     * @return boolean
     * @throws HttpException when the request method is not allowed.
     */
    public function afterAction($event)
    {
        $response = Yii::$app->getModule('api/oauth2')->getServer()->getResponse();

        $isValid = true;
        if($response !== null) {
            $isValid = $response->isInformational() || $response->isSuccessful() || $response->isRedirection();
        }
        if(!$isValid) {
            throw new HttpException($response->getStatusCode(), $this->getErrorMessage($response), $response->getParameter('error_uri'));
        }
    }
    
    protected function getErrorMessage(\OAuth2\Response $response)
    {
        $message = Module::t('common', $response->getParameter('error_description'));
        if($message === null) {
            $message = Module::t('common', 'An internal server error occurred.');
        }
        return $message;
    }
}
