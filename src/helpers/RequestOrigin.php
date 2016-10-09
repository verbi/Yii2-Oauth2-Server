<?php

namespace verbi\yii2Oauth2Server\helpers;

use \Yii;

/**
 * @author Philip Verbist <philip.verbist@gmail.com>
 * @link https://github.com/verbi/Yii2-Oauth2-Server/
 * @license https://opensource.org/licenses/GPL-3.0
 */
class RequestOrigin {
    public function getRequestOrigin() {
        return Yii::$app->getRequest()->getUserAgent()
                . Yii::$app->getRequest()->getUserHost()
                . Yii::$app->getRequest()->getUserIP();
    }
}