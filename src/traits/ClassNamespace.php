<?php

namespace filsh\yii2\oauth2server\traits;

/**
 * @author Philip Verbist <philip.verbist@gmail.com>
 * @link https://github.com/verbi/Yii2-Oauth2-Server/
 * @license https://opensource.org/licenses/GPL-3.0
 */
trait ClassNamespace
{
    public static function className()
    {
        return get_called_class();
    }
}