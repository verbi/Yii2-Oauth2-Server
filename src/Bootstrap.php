<?php

namespace verbi\yii2Oauth2Server;

/**
 * @author Philip Verbist <philip.verbist@gmail.com>
 * @link https://github.com/verbi/Yii2-Oauth2-Server/
 * @license https://opensource.org/licenses/GPL-3.0
 */
class Bootstrap implements \yii\base\BootstrapInterface
{
    /**
     * @var array Model's map
     */
    protected $_modelMap = [
        'OauthClients'               => 'verbi\yii2Oauth2Server\storages\OauthClients',
        'OauthAccessTokens'          => 'verbi\yii2Oauth2Server\storages\OauthAccessTokens',
        'OauthAuthorizationCodes'    => 'verbi\yii2Oauth2Server\storages\OauthAuthorizationCodes',
        'OauthRefreshTokens'         => 'verbi\yii2Oauth2Server\storages\OauthRefreshTokens',
        'OauthScopes'                => 'verbi\yii2Oauth2Server\storages\OauthScopes',
    ];
    
    /**
     * @var array Storage's map
     */
    protected $_storageMap = [
        'access_token'          => '\verbi\yii2Oauth2Server\storages\GenericStorage',
        'authorization_code'    => 'filsh\yii2\oauth2server\storage\Pdo',
        'client_credentials'    => '\verbi\yii2Oauth2Server\storages\GenericStorage',
        'client'                => '\verbi\yii2Oauth2Server\storages\GenericStorage',
        'refresh_token'         => '\verbi\yii2Oauth2Server\storages\GenericStorage',
        'user_credentials'      => '\verbi\yii2Oauth2Server\storages\GenericStorage',
        'public_key'            => 'filsh\yii2\oauth2server\storage\Pdo',
        'jwt_bearer'            => 'filsh\yii2\oauth2server\storage\Pdo',
        'scope'  
    ];
    
    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        /** @var $module Module */
        if ($app->hasModule('api/oauth2') && ($module = $app->getModule('api/oauth2')) instanceof Module) {
            $this->_modelMap = array_merge($this->_modelMap, $module->modelMap);
            foreach ($this->_modelMap as $name => $definition) {
                \Yii::$container->set("verbi\\yii2Oauth2Server\\models\\" . $name, $definition);
                $module->modelMap[$name] = is_array($definition) ? $definition['class'] : $definition;
            }
            
            $this->_storageMap = array_merge($this->_storageMap, $module->storageMap);
            foreach ($this->_storageMap as $name => $definition) {
                \Yii::$container->set($name, $definition);
                $module->storageMap[$name] = is_array($definition) ? $definition['class'] : $definition;
            }
            $module->controllerNamespace = 'verbi\yii2Oauth2Server\controllers';
            if ($app instanceof \yii\console\Application) {
                $module->controllerNamespace = 'filsh\yii2\oauth2server\commands';
            }
        }
    }
}