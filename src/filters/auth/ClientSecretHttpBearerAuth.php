<?php

namespace verbi\yii2Oauth2Server\filters\auth;

use \Yii;

/**
 * @author Philip Verbist <philip.verbist@gmail.com>
 * @link https://github.com/verbi/Yii2-Oauth2-Server/
 * @license https://opensource.org/licenses/GPL-3.0
 */
class ClientSecretHttpBearerAuth extends \yii\filters\auth\HttpBearerAuth
{
    public function authenticate($user, $request, $response) {
        //die($this->getActionId());
        //die(print_r($request->getHeaders()->toArray(),true));
        $authHeader = $request->getHeaders()->get('Authorization');
        if ($authHeader !== null && preg_match('/^Bearer\s+(.*?)$/', $authHeader, $matches)) {
            $server = Yii::$app->getModule('api/oauth2')->getServer();
            $identity = $server->getStorage('client_credentials')->getUserByClientSecretToken($matches[1]);
            \Yii::$app->requestedParams['token']=$matches[1];
            //\Yii::$app->controller->bindActionParams(\Yii::$app->requestedAction, ['token'=>$matches[1]]);
            if ($identity === null) {
                $this->handleFailure($response);
            }
            else {
                $user->login( $identity );
            }
            return $identity;
        }
        return null;
    }
}