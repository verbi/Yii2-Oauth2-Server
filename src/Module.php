<?php

namespace verbi\yii2Oauth2Server;

use verbi\yii2Oauth2Server\Server;

/**
 * For example,
 * 
 * ```php
 * 'oauth2' => [
 *     'class' => 'filsh\yii2\oauth2server\Module',
 *     'tokenParamName' => 'accessToken',
 *     'tokenAccessLifetime' => 3600 * 24,
 *     'storageMap' => [
 *         'user_credentials' => 'common\models\User',
 *     ],
 *     'grantTypes' => [
 *         'user_credentials' => [
 *             'class' => 'OAuth2\GrantType\UserCredentials',
 *         ],
 *         'refresh_token' => [
 *             'class' => 'OAuth2\GrantType\RefreshToken',
 *             'always_issue_new_refresh_token' => true
 *         ]
 *     ]
 * ]
 * ```
 * 
 * @author Philip Verbist <philip.verbist@gmail.com>
 * @link https://github.com/verbi/Yii2-Oauth2-Server/
 * @license https://opensource.org/licenses/GPL-3.0
 */
class Module extends \filsh\yii2\oauth2server\Module {
    public $controllerNamespace = 'verbi\yii2Oauth2Server\controllers';

    /**
     * Gets Oauth2 Server
     * 
     * @return \filsh\yii2\oauth2server\Server
     * @throws \yii\base\InvalidConfigException
     */
    public function getServer()
    {
        if(!$this->has('server')) {
            $storages = [];
            foreach(array_keys($this->storageMap) as $name) {
                $storages[$name] = \Yii::$container->get($name);
            }
            $grantTypes = [];
            //die(print_r($this->grantTypes,true));
            foreach($this->grantTypes as $name => $options) {
                if(!isset($storages[$name]) || empty($options['class'])) {
                    throw new \yii\base\InvalidConfigException('Invalid grant types configuration.');
                }

                $class = $options['class'];
                unset($options['class']);

                $reflection = new \ReflectionClass($class);
                $config = array_merge([0 => $storages[$name]], [$options]);

                $instance = $reflection->newInstanceArgs($config);
                $grantTypes[$name] = $instance;
            }
            $array = [];
            if($this->tokenParamName) {
                $array['token_param_name'] = $this->tokenParamName;
            }
            if($this->tokenAccessLifetime) {
                $array['access_lifetime'] = $this->tokenAccessLifetime;
            }
            $server = \Yii::$container->get(Server::className(), [
                $this,
                $storages,
                $array,
                $grantTypes
            ]);

            $this->set('server', $server);
        }
        return $this->get('server');
    }
}
