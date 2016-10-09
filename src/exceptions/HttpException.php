<?php

namespace verbi\yii2Oauth2Server\exceptions;

/**
 * @author Philip Verbist <philip.verbist@gmail.com>
 * @link https://github.com/verbi/Yii2-Oauth2-Server/
 * @license https://opensource.org/licenses/GPL-3.0
 */
class HttpException extends \filsh\yii2\oauth2server\exceptions\HttpException
{
    public function __construct($status, $message = null, $errorUri = null, $code = 0, \Exception $previous = null)
    {
        
        parent::__construct($status, $message, $code, $previous);
    }
}