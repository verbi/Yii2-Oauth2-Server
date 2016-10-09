<?php

namespace verbi\yii2Oauth2Server\grantTypes;

use OAuth2\Storage\RefreshTokenInterface;
use OAuth2\ResponseType\AccessTokenInterface;
use OAuth2\RequestInterface;
use OAuth2\ResponseInterface;
use verbi\yii2Oauth2Server\models\UserClient;

/**
 * @author Philip Verbist <philip.verbist@gmail.com>
 * @link https://github.com/verbi/Yii2-Oauth2-Server/
 * @license https://opensource.org/licenses/GPL-3.0
 */
class RefreshToken extends \OAuth2\GrantType\RefreshToken implements \OAuth2\ClientAssertionType\ClientAssertionTypeInterface
{
    private $refreshToken;

    protected $storage;
    protected $config;

    /**
     * @param OAuth2\Storage\RefreshTokenInterface $storage REQUIRED Storage class for retrieving refresh token information
     * @param array                                $config  OPTIONAL Configuration options for the server
     *                                                      <code>
     *                                                      $config = array(
     *                                                      'always_issue_new_refresh_token' => true, // whether to issue a new refresh token upon successful token request
     *                                                      'unset_refresh_token_after_use' => true // whether to unset the refresh token after after using
     *                                                      );
     *                                                      </code>
     */
    public function __construct(RefreshTokenInterface $storage, $config = array())
    {
        $this->config = array_merge(array(
            'always_issue_new_refresh_token' => false,
            'unset_refresh_token_after_use' => false
        ), $config);

        // to preserve B.C. with v1.6
        // @see https://github.com/bshaffer/oauth2-server-php/pull/580
        // @todo - remove in v2.0
        if (isset($config['always_issue_new_refresh_token']) && !isset($config['unset_refresh_token_after_use'])) {
            $this->config['unset_refresh_token_after_use'] = $config['always_issue_new_refresh_token'];
        }

        $this->storage = $storage;
    }

    public function getQuerystringIdentifier()
    {
        return 'refresh_token';
    }

    public function validateRequest(RequestInterface $request, ResponseInterface $response)
    {
        return true;
    }

    public function getClientId()
    {
        $clientId=null;
        if(($client = UserClient::findByRefreshToken(\Yii::$app->requestedParams['token']))) {
            $clientId = $client->client_id;
        }
        $this->refreshToken['client_id'] = $clientId;
        return $this->refreshToken['client_id'];
    }

    public function getUserId()
    {
        return isset($this->refreshToken['user_id']) ? $this->refreshToken['user_id'] : null;
    }

    public function getScope()
    {
        return isset($this->refreshToken['scope']) ? $this->refreshToken['scope'] : null;
    }

    public function createAccessToken(AccessTokenInterface $accessToken, $client_id, $user_id, $scope)
    {
        /*
         * It is optional to force a new refresh token when a refresh token is used.
         * However, if a new refresh token is issued, the old one MUST be expired
         * @see http://tools.ietf.org/html/rfc6749#section-6
         */
        $issueNewRefreshToken = $this->config['always_issue_new_refresh_token'];
        $unsetRefreshToken = $this->config['unset_refresh_token_after_use'];
        $token = $accessToken->createAccessToken($client_id, $user_id, $scope, $issueNewRefreshToken);

        if ($unsetRefreshToken) {
            $this->storage->unsetRefreshToken($this->refreshToken['refresh_token']);
        }

        return $token;
    }
}
