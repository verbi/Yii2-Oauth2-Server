<?php

namespace verbi\yii2Oauth2Server\models;

use Yii;

/**
 * This is the model class for table "user_refresh_token".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $created
 * @property string $expire
 * @property string $origin
 * @property string $token
 *
 * @property User $user
 * 
 * @author Philip Verbist <philip.verbist@gmail.com>
 * @link https://github.com/verbi/Yii2-Oauth2-Server/
 * @license https://opensource.org/licenses/GPL-3.0
 */
class UserClientRefreshToken extends \verbi\yii2ExtendedActiveRecord\db\XActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_client_refresh_token';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['client_id', 'created', 'expire', 'origin', 'token'], 'required'],
            [['created', 'expire'], 'safe'],
            [['client_id', 'origin', 'token'], 'string', 'max' => 255],
            [['token'], 'unique'],
            [['client_id'], 'exist', 'skipOnError' => true, 'targetClass' => UserClient::className(), 'targetAttribute' => ['client_id' => 'client_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'client_id' => 'Client ID',
            'created' => 'Created',
            'expire' => 'Expire',
            'origin' => 'Origin',
            'token' => 'Token',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClient()
    {
        return $this->hasOne(UserClient::className(), ['client_id' => 'client_id']);
    }
    
    public static function getByClient( $client, $origin ) {
        static::deleteObsolete();
        $token = self::find()
                ->where(
                    [
                       'origin' => $origin,
                    ]
                        )
                ->andWhere(
                    [
                        'client_id' => $client->client_id,
                    ]
                        )
                ->andWhere(
                    [
                        '>=',
                        'expire',
                        date( 'Y-m-d H:i:s', time() + 300 ),
                    ]
                        )
                ->one();
        if( !$token ) {
            $token = new self;
            $token->client_id = $client->client_id;
            $token->origin = $origin;
            $token->created = date( 'Y-m-d H:i:s' );
            $token->expire = date( 'Y-m-d H:i:s', time() + 3600 * 24 * 30 );
            $token->token = \Yii::$app->getSecurity()->generateRandomString( rand ( 128 , 255 ) );
            $token->save();
        }
        return $token;
    }
    
    public static function deleteObsolete()
    {
        static::deleteAll([
                '<=',
                'expire',
                date('Y-m-d H:i:s',time()),
            ]);
    }
}
