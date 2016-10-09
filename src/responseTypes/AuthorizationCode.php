<?php

namespace verbi\yii2Oauth2Server\responseTypes;

/**
 * @author Philip Verbist <philip.verbist@gmail.com>
 * @link https://github.com/verbi/Yii2-Oauth2-Server/
 * @license https://opensource.org/licenses/GPL-3.0
 */
class AuthorizationCode extends \OAuth2\ResponseType\AuthorizationCode
{
    /**
     * Generates an unique auth code.
     *
     * Implementing classes may want to override this function to implement
     * other auth code generation schemes.
     *
     * @return
     * An unique auth code.
     *
     * @ingroup oauth2_section_4
     */
    protected function generateAuthorizationCode()
    {
        return \Yii::$app->getSecurity()->generateRandomString(rand ( 128 , 255 ));
    }
}
