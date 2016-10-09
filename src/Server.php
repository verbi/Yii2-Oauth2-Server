<?php

namespace verbi\yii2Oauth2Server;


use verbi\yii2Oauth2Server\responseTypes\AccessToken;
use verbi\yii2Oauth2Server\responseTypes\JwtAccessToken;
use verbi\yii2Oauth2Server\responseTypes\AuthorizationCode as AuthorizationCodeResponseType;


use OAuth2\OpenID\ResponseType\AuthorizationCode as OpenIDAuthorizationCodeResponseType;
use OAuth2\OpenID\Storage\AuthorizationCodeInterface as OpenIDAuthorizationCodeInterface;

use OAuth2\OpenID\ResponseType\IdToken;
use OAuth2\OpenID\ResponseType\IdTokenToken;
use OAuth2\Storage\JwtAccessToken as JwtAccessTokenStorage;


use verbi\yii2Oauth2Server\base\controllers\TokenController;

/**
* Server class for OAuth2
* This class serves as a convience class which wraps the other Controller classes
* 
 * @author Philip Verbist <philip.verbist@gmail.com>
 * @link https://github.com/verbi/Yii2-Oauth2-Server/
 * @license https://opensource.org/licenses/GPL-3.0
 */
class Server extends \filsh\yii2\oauth2server\Server
{
    protected function getDefaultResponseTypes()
    {
        $responseTypes = array();

        if ($this->config['allow_implicit']) {
            $responseTypes['token'] = $this->getAccessTokenResponseType();
        }

        if ($this->config['use_openid_connect']) {
            $responseTypes['id_token'] = $this->getIdTokenResponseType();
            if ($this->config['allow_implicit']) {
                $responseTypes['id_token token'] = $this->getIdTokenTokenResponseType();
            }
        }

        if (isset($this->storages['authorization_code'])) {
            $config = array_intersect_key($this->config, array_flip(explode(' ', 'enforce_redirect auth_code_lifetime')));
            if ($this->config['use_openid_connect']) {
                if (!$this->storages['authorization_code'] instanceof OpenIDAuthorizationCodeInterface) {
                    throw new \LogicException("Your authorization_code storage must implement OAuth2\OpenID\Storage\AuthorizationCodeInterface to work when 'use_openid_connect' is true");
                }
                $responseTypes['code'] = new OpenIDAuthorizationCodeResponseType($this->storages['authorization_code'], $config);
                $responseTypes['code id_token'] = new CodeIdToken($responseTypes['code'], $responseTypes['id_token']);
            } else {
                $responseTypes['code'] = new AuthorizationCodeResponseType($this->storages['authorization_code'], $config);
            }
        }

        if (count($responseTypes) == 0) {
            throw new \LogicException("You must supply an array of response_types in the constructor or implement a OAuth2\Storage\AuthorizationCodeInterface storage object or set 'allow_implicit' to true and implement a OAuth2\Storage\AccessTokenInterface storage object");
        }

        return $responseTypes;
    }
    
    /**
     * For Resource Controller
     */
    protected function createDefaultJwtAccessTokenStorage()
    {
        if (!isset($this->storages['public_key'])) {
            throw new \LogicException("You must supply a storage object implementing OAuth2\Storage\PublicKeyInterface to use crypto tokens");
        }
        $tokenStorage = null;
        if (!empty($this->config['store_encrypted_token_string']) && isset($this->storages['access_token'])) {
            $tokenStorage = $this->storages['access_token'];
        }
        // wrap the access token storage as required.
        return new JwtAccessTokenStorage($this->storages['public_key'], $tokenStorage);
    }

    /**
     * For Authorize and Token Controllers
     */
    protected function createDefaultJwtAccessTokenResponseType()
    {
        if (!isset($this->storages['public_key'])) {
            throw new \LogicException("You must supply a storage object implementing OAuth2\Storage\PublicKeyInterface to use crypto tokens");
        }

        $tokenStorage = null;
        if (isset($this->storages['access_token'])) {
            $tokenStorage = $this->storages['access_token'];
        }

        $refreshStorage = null;
        if (isset($this->storages['refresh_token'])) {
            $refreshStorage = $this->storages['refresh_token'];
        }

        $config = array_intersect_key($this->config, array_flip(explode(' ', 'store_encrypted_token_string issuer access_lifetime refresh_token_lifetime')));

        return new JwtAccessToken($this->storages['public_key'], $tokenStorage, $refreshStorage, $config);
    }

    protected function createDefaultAccessTokenResponseType()
    {
        if (!isset($this->storages['access_token'])) {
            throw new \LogicException("You must supply a response type implementing OAuth2\ResponseType\AccessTokenInterface, or a storage object implementing OAuth2\Storage\AccessTokenInterface to use the token server");
        }

        $refreshStorage = null;
        if (isset($this->storages['refresh_token'])) {
            $refreshStorage = $this->storages['refresh_token'];
        }

        $config = array_intersect_key($this->config, array_flip(explode(' ', 'access_lifetime refresh_token_lifetime')));
        $config['token_type'] = $this->tokenType ? $this->tokenType->getTokenType() :  $this->getDefaultTokenType()->getTokenType();

        return new AccessToken($this->storages['access_token'], $refreshStorage, $config);
    }

    protected function createDefaultIdTokenResponseType()
    {
        if (!isset($this->storages['user_claims'])) {
            throw new \LogicException("You must supply a storage object implementing OAuth2\OpenID\Storage\UserClaimsInterface to use openid connect");
        }
        if (!isset($this->storages['public_key'])) {
            throw new \LogicException("You must supply a storage object implementing OAuth2\Storage\PublicKeyInterface to use openid connect");
        }

        $config = array_intersect_key($this->config, array_flip(explode(' ', 'issuer id_lifetime')));

        return new IdToken($this->storages['user_claims'], $this->storages['public_key'], $config);
    }

    protected function createDefaultIdTokenTokenResponseType()
    {
        return new IdTokenToken($this->getAccessTokenResponseType(), $this->getIdTokenResponseType());
    }
    
    protected function createDefaultTokenController()
    {
        if (0 == count($this->grantTypes)) {
            $this->grantTypes = $this->getDefaultGrantTypes();
        }

        if (is_null($this->clientAssertionType)) {
            // see if HttpBasic assertion type is requred.  If so, then create it from storage classes.
            foreach ($this->grantTypes as $grantType) {
                if (!$grantType instanceof ClientAssertionTypeInterface) {
                    if (!isset($this->storages['refresh_token'])) {
                        throw new \LogicException("You must supply a storage object implementing OAuth2\Storage\ClientCredentialsInterface to use the token server");
                    }
                    $config = array_intersect_key($this->config, array_flip(explode(' ', 'allow_credentials_in_request_body allow_public_clients')));
                    $this->clientAssertionType = new \verbi\yii2Oauth2Server\clientAssertionTypes\HttpRefreshToken($this->storages['refresh_token'], $config);
                    break;
                }
            }
        }

        if (!isset($this->storages['client'])) {
            throw new \LogicException("You must supply a storage object implementing OAuth2\Storage\ClientInterface to use the token server");
        }

        $accessTokenResponseType = $this->getAccessTokenResponseType();

        return new TokenController($accessTokenResponseType, $this->storages['client'], $this->grantTypes, $this->clientAssertionType, $this->getScopeUtil());
    }
}
