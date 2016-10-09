<?php

namespace verbi\yii2Oauth2Server\responseTypes;

use OAuth2\Storage\AccessTokenInterface as AccessTokenStorageInterface;
use OAuth2\Storage\RefreshTokenInterface;
use verbi\yii2Oauth2Server\models\UserClientToken;
use verbi\yii2Oauth2Server\helpers\RequestOrigin;

/**
 * @author Philip Verbist <philip.verbist@gmail.com>
 * @link https://github.com/verbi/Yii2-Oauth2-Server/
 * @license https://opensource.org/licenses/GPL-3.0
 */
class AccessToken extends \OAuth2\ResponseType\AccessToken
{
    /**
     * @inherit
     */
    public function __construct(AccessTokenStorageInterface $tokenStorage, RefreshTokenInterface $refreshStorage = null, array $config = array())
    {
        parent::__construct( 
                $tokenStorage,
                $refreshStorage,
                array_merge(
                    array(
                        'token_param_name'  => 'access-token',
                        'access_lifetime'        => 600,
                        'refresh_token_lifetime' => 3600 * 24 * 365,
                    ),
                    $config
                )
            );
    }
    
    /**
     * Handle the creation of access token, also issue refresh token if supported / desirable.
     *
     * @param $client_id                client identifier related to the access token.
     * @param $user_id                  user ID associated with the access token
     * @param $scope                    OPTIONAL scopes to be stored in space-separated string.
     * @param bool $includeRefreshToken if true, a new refresh_token will be added to the response
     *
     * @see http://tools.ietf.org/html/rfc6749#section-5
     * @ingroup oauth2_section_5
     */
    public function createAccessToken($client_id, $user_id, $scope = null, $includeRefreshToken = true)
    {
        /*$token = array(
            "access_token" => $this->generateAccessToken(),
            "expires_in" => $this->config['access_lifetime'],
            "token_type" => $this->config['token_type'],
            "scope" => $scope
        );*/
        UserClientToken::deleteObsolete();
        $model = UserClientToken::find()
                ->where([ 'origin' => RequestOrigin::getRequestOrigin() ])
                ->andWhere([ '>=', 'expire', date( 'Y-m-d H:i:s', time() + $this->config['access_lifetime'] * 0.1 )])
                ->andWhere([ 'user_client_id' => $client_id ])
                ->one();
        if(!$model) {
            $model = new UserClientToken();
            $model->user_client_id = $client_id;
            $model->token = \Yii::$app->getSecurity()->generateRandomString( rand( 128 , 255 ) );
            $model->created = date( 'Y-m-d H:i:s' );
            $model->expire = date( 'Y-m-d H:i:s', $this->config['access_lifetime'] ? time() + $this->config['access_lifetime'] : null );
            $model->origin = RequestOrigin::getRequestOrigin();
            $model->save();
        }
        $ttl = strtotime( $model->expire ) - time();
        return [ 'token' => $model->token, 'ttl' => $ttl - $this->config['access_lifetime'] * 0.1, ];
        $token = $this->tokenStorage->setAccessToken($client_id, $user_id, $ttl, $scope);

        //$this->tokenStorage->setAccessToken($token["access_token"], $client_id, $user_id, $this->config['access_lifetime'] ? time() + $this->config['access_lifetime'] : null, $scope);

        return $token;
    }
    
    /**
     * @inherit
     */
    protected function generateAccessToken()
    {
        return \Yii::$app->getSecurity()->generateRandomString(rand ( 128 , 255 ));
    }
}
