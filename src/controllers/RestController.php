<?php

namespace verbi\yii2Oauth2Server\controllers;

use Yii;
use verbi\yii2Oauth2Server\filters\ErrorToExceptionFilter;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use verbi\yii2Oauth2Server\filters\auth\SessionAuth;
use verbi\yii2Oauth2Server\filters\auth\ClientSecretCompositeAuth;
use verbi\yii2Oauth2Server\filters\auth\ClientSecretHttpBearerAuth;
use verbi\yii2Oauth2Server\filters\auth\ClientSecretQueryParamAuth;
use verbi\yii2Oauth2Server\filters\auth\RefreshTokenCompositeAuth;
use verbi\yii2Oauth2Server\filters\auth\RefreshTokenHttpBearerAuth;
use verbi\yii2Oauth2Server\filters\auth\RefreshTokenQueryParamAuth;
use verbi\yii2Oauth2Server\filters\auth\ClientHttpBasicAuth;
use verbi\yii2Oauth2Server\models\UserClient;
use verbi\yii2Oauth2Server\models\UserClientRefreshToken;
use verbi\yii2Oauth2Server\models\UserClientSecretToken;
use verbi\yii2Oauth2Server\helpers\RequestOrigin;

/**
 * @author Philip Verbist <philip.verbist@gmail.com>
 * @link https://github.com/verbi/Yii2-Oauth2-Server/
 * @license https://opensource.org/licenses/GPL-3.0
 */
class RestController extends \filsh\yii2\oauth2server\controllers\RestController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        //\Yii::$app->user->enableSession = false;
        $behaviors = parent::behaviors();
        switch (\Yii::$app->requestedAction->id) {
            case 'token':
                $behaviors['authenticator'] = [
                    'class' => RefreshTokenCompositeAuth::className(),
                    'authMethods' => [
                        //HttpBasicAuth::className(),
                        RefreshTokenHttpBearerAuth::className(),
                        RefreshTokenQueryParamAuth::className(),
                    ],
                ];
                break;
            case 'clientid':
                $behaviors['authenticator'] = [
                    'class' => CompositeAuth::className(),
                    'authMethods' => [
                        SessionAuth::className(),
                        HttpBasicAuth::className(),
                    ],
                ];
                break;
            case 'clientsecret':
                $behaviors['authenticator'] = [
                    'class' => ClientSecretCompositeAuth::className(),
                    'authMethods' => [
                        //HttpBasicAuth::className(),
                        ClientSecretHttpBearerAuth::className(),
                        ClientSecretQueryParamAuth::className(),
                    ],
                ];
                break;
            case 'refreshtoken':
                $behaviors['authenticator'] = [
                    'class' => ClientHttpBasicAuth::className(),
                    'auth' => function ($client_id, $client_secret) {
                        $user = null;
                        \Yii::$app->requestedParams['client_id']=$client_id;
                        $userClient = UserClient::find()
                            ->where([
                                'client_id' => $client_id,])
                            ->andWhere([
                                'client_secret' => $client_secret,
                            ])
                            ->one();
                        if( $userClient ) {
                            $user = $userClient->getUser()->one();
                        }
                        //die($user);
                        return $user;
                    },
                ];
                break;
            default:
        }
        
        $behaviors['exceptionFilter'] = [
                'class' => ErrorToExceptionFilter::className()
            ];
        
        return $behaviors;
        
    }
    
    public function actionToken()
    {
        $response = $this->module->getServer()->handleTokenRequest();
        return $response->getParameters();
    }
    
    public function actionClientid()
    {
        $user = \Yii::$app->getUser()->getIdentity();
        if(!$user) {
            throw new \yii\web\HttpException(400, 'User was not found.', 405);
        }
        $client = UserClient::getByUser( $user );
        return [
            'client_id' => $client->client_id,
            'token' => $client->getSecretToken()->token,
            ];
    }
    
    public function actionClientsecret() {
        $secret = null;
        $crypted = null;
        if( isset( \Yii::$app->requestedParams['token'] ) ) {
            $token = \Yii::$app->requestedParams['token'];
            $client = UserClient::findBySecretToken( $token );
            $secret = $client->client_secret;
            UserClientSecretToken::deleteAll( [ 'token' => $token ] );
        }
        return [
            'client_secret' =>  $secret,
        ];
    }
    
    public function actionRefreshtoken() {
        $user = Yii::$app->user->identity;
        $client = UserClient::find()
                ->where([
                    'client_id' =>  \Yii::$app->requestedParams['client_id'],
                ])
                ->one();
        $origin = RequestOrigin::getRequestOrigin();
        $refreshToken = UserClientRefreshToken::getByClient($client,$origin);
        //die(print_r($refreshToken,true));
        return [
            'token' => $refreshToken->token,
            'ttl' => strtotime($refreshToken->expire)-time()-300,
        ];
    }
    
    public function encrypt($string, $key) {
        
    }
}