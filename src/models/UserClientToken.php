<?php

namespace verbi\yii2Oauth2Server\models;

use Yii;

/**
 * This is the model class for table "user_client_token".
 *
 * @property integer $user_client_id
 * @property string $created
 * @property string $expire
 * @property string $origin
 * @property string $token
 * 
 * @author Philip Verbist <philip.verbist@gmail.com>
 * @link https://github.com/verbi/Yii2-Oauth2-Server/
 * @license https://opensource.org/licenses/GPL-3.0
 */
class UserClientToken extends \verbi\yii2ExtendedActiveRecord\db\XActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_client_token';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_client_id', 'created', 'expire', 'origin', 'token'], 'required'],
            [['created', 'expire'], 'safe'],
            [['user_client_id', 'origin', 'token'], 'string', 'max' => 255],
            [['token'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_client_id' => 'User Client ID',
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
        return $this->hasOne(UserClient::className(), ['client_id' => 'user_client_id']);
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
