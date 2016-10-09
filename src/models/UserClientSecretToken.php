<?php

namespace verbi\yii2Oauth2Server\models;

use Yii;
use verbi\yii2Oauth2Server\helpers\RequestOrigin;

/**
 * This is the model class for table "user_client_secret_token".
 *
 * @property string $token
 * @property integer $client_id
 * @property integer $user_id
 * @property string $origin
 * @property string $expires
 * @property string $scopes
 * 
 * @author Philip Verbist <philip.verbist@gmail.com>
 * @link https://github.com/verbi/Yii2-Oauth2-Server/
 * @license https://opensource.org/licenses/GPL-3.0
 */
class UserClientSecretToken extends \verbi\yii2ExtendedActiveRecord\db\XActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_client_secret_token';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['token', 'client_id', 'origin'], 'required'],
            //[['client_id'], 'integer'],
            [['expires'], 'safe'],
            [['client_id', 'token', 'origin'], 'string', 'max' => 255],
            [['scopes'], 'string', 'max' => 2000],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'token' => 'Token',
            'client_id' => 'Client ID',
            'origin' => 'Origin',
            'expires' => 'Expires',
            'scopes' => 'Scopes',
        ];
    }
    
    public static function generate( $client )
    {
        static::deleteObsolete();
        $token = new static;
        $token->token = \Yii::$app->getSecurity()->generateRandomString( rand ( 128 , 255 ) );
        $token->client_id = $client->client_id;
        $token->expires = date('Y-m-d H:i:s',time() + 120);
        $token->origin = RequestOrigin::getRequestOrigin();
        $token->save();
        return $token;
    }
    
    public static function deleteObsolete()
    {
        static::deleteAll([
                '<=',
                'expires',
                date('Y-m-d H:i:s',time()),
            ]);
    }
}
