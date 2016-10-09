<?php

namespace verbi\yii2Oauth2Server\storages;

use verbi\yii2Oauth2Server\models\UserClient;
use verbi\yii2Oauth2Server\models\UserClientToken;
use verbi\yii2Oauth2Server\models\UserClientRefreshToken;

/**
 * @author Philip Verbist <philip.verbist@gmail.com>
 * @link https://github.com/verbi/Yii2-Oauth2-Server/
 * @license https://opensource.org/licenses/GPL-3.0
 */
class GenericStorage extends \Yii\base\Component
implements \OAuth2\Storage\UserCredentialsInterface, 
        \OAuth2\Storage\ClientCredentialsInterface, 
        \OAuth2\Storage\AccessTokenInterface, 
        \OAuth2\Storage\RefreshTokenInterface
{
    public function getIdentityClass() {
        return Yii::$app->getUser()->getIdentityClass();
    }
    
    public function checkUserCredentials($username, $password) {
        $identityClass = $this->getIdentityClass();
        return $identityClass::validateLogin($username, $password);
    }

    public function getUserDetails($username) {
        $user = static::findByEmail($username);
        return ['user_id' => $user->getId()];
    }

    public function checkClientCredentials($client_id, $client_secret = null) {
        return UserClient::find()
                        ->where([
                            'client_id' => $client_id,
                            'client_secret' => $client_secret
                        ])
                        ->one() ? true : false;
    }

    public function getClientDetails($client_id) {
        $client = UserClient::find()->one();
        if ($client) {
            return [
                'client_id' => $client->client_id,
                'user_id' => $client->user_id,
                'scope' => $client->scope,
            ];
        }
        return false;
    }

    public function isPublicClient($client_id) {
        return false;
    }

    public function getClientScope($client_id) {
        
    }
    
    public function getUserByClientSecretToken( $token ) {
        $client = UserClient::findBySecretToken( $token );
        if( $client ) {
            return $client->user;
        }
        return null;
    }
    
    public function getUserByRefreshToken( $token ) {
        UserClientRefreshToken::deleteObsolete();
        $refreshToken = UserClientRefreshToken::findOne( ['token'=>$token] );
        if( $refreshToken ) {
            $client = $refreshToken->getClient()->one();
            return $client->user;
        }
        return null;
    }
    
    public function getUserByAccessToken( $token ) {
        UserClientToken::deleteObsolete();
        $refreshToken = UserClientToken::findOne( ['token'=>$token] );
        if( $refreshToken ) {
            $client = $refreshToken->getClient()->one();
            return $client->user;
        }
        return null;
    }

    public function checkRestrictedGrantType($client_id, $grant_type) {
        return true;
    }

    public function getAccessToken($oauth_token) {
        
    }

    public function setAccessToken($oauth_token, $client_id, $user_id, $expires, $scope = NULL) {
        
    }

    public function getRefreshToken($refresh_token) {
        
    }

    public function setRefreshToken($refresh_token, $client_id, $user_id, $expires, $scope = NULL) {
        
    }

    public function unsetRefreshToken($refresh_token) {
        
    }

}
