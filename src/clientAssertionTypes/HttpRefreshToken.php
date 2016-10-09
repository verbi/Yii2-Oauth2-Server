<?php

namespace verbi\yii2Oauth2Server\clientAssertionTypes;

use OAuth2\RequestInterface;
use OAuth2\ResponseInterface;

/**
 * @author Philip Verbist <philip.verbist@gmail.com>
 * @link https://github.com/verbi/Yii2-Oauth2-Server/
 * @license https://opensource.org/licenses/GPL-3.0
 */
class HttpRefreshToken extends \OAuth2\ClientAssertionType\HttpBasic
{
    /**
     * Internal function used to get the client credentials from HTTP basic
     * auth or POST data.
     *
     * According to the spec (draft 20), the client_id can be provided in
     * the Basic Authorization header (recommended) or via GET/POST.
     *
     * @return
     * A list containing the client identifier and password, for example
     * @code
     * return array(
     *     "client_id"     => CLIENT_ID,        // REQUIRED the client id
     *     "client_secret" => CLIENT_SECRET,    // OPTIONAL the client secret (may be omitted for public clients)
     * );
     * @endcode
     *
     * @see http://tools.ietf.org/html/rfc6749#section-2.3.1
     *
     * @ingroup oauth2_section_2
     */
    public function getClientCredentials(RequestInterface $request, ResponseInterface $response = null)
    {
        $token = \Yii::$app->requestedParams['token'];
        if ($token) {
            $userClient = \verbi\yii2Oauth2Server\models\UserClient::findByRefreshToken($token);
            
            if( $userClient ) {
                return array('client_id' => $userClient->client_id, 'client_secret' => $userClient->client_secret);
            }
        }
        return parent::getClientCredentials( $request, $response );
    }
    
    public function validateRequest(RequestInterface $request, ResponseInterface $response)
    {
        return true;
    }
}
