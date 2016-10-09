<?php
namespace verbi\yii2Oauth2Server\filters\auth;

/**
 * Description of ClientHttpBasicAuth
 * @author Philip Verbist <philip.verbist@gmail.com>
 * @link https://github.com/verbi/Yii2-Oauth2-Server/
 * @license https://opensource.org/licenses/GPL-3.0
 */
class SessionAuth extends \yii\filters\auth\AuthMethod {
    public function authenticate($user, $request, $response) {
        return $user->getIdentity();
    }
}