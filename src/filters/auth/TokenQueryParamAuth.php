<?php


namespace verbi\yii2Oauth2Server\filters\auth;

use \Yii;

/**
 * @author Philip Verbist <philip.verbist@gmail.com>
 * @link https://github.com/verbi/Yii2-Oauth2-Server/
 * @license https://opensource.org/licenses/GPL-3.0
 */
class TokenQueryParamAuth extends \yii\filters\auth\QueryParamAuth
{
    public function authenticate($user, $request, $response)
    {
        $accessToken = $request->get($this->tokenParam);
        if (is_string($accessToken)) {
            $server = Yii::$app->getModule('api/oauth2')->getServer();
            $identity = $server->getStorage('client_credentials')->getUserByAccessToken($accessToken);
            \Yii::$app->requestedParams['token']=$request->get($this->tokenParam);
            if ($identity !== null) {
                return $identity;
            }
        }
        if ($accessToken !== null) {
            $this->handleFailure($response);
        }
        return null;
    }
}