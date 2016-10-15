<?php

namespace verbi\yii2Oauth2Server\models;

use Yii;
use verbi\yii2Oauth2Server\helpers\RequestOrigin;

/**
 * This is the model class for table "user_client".
 *
 * @property string $client_id
 * @property string $client_secret
 * @property integer $user_id
 * @property string $scope
 *
 * @property User $user
 * 
 * @author Philip Verbist <philip.verbist@gmail.com>
 * @link https://github.com/verbi/Yii2-Oauth2-Server/
 * @license https://opensource.org/licenses/GPL-3.0
 */
class UserClient extends \verbi\yii2ExtendedActiveRecord\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_client';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['client_id', 'client_secret', 'user_id'], 'required'],
            [['user_id'], 'integer'],
            [['client_id', 'client_secret', 'scope'], 'string', 'max' => 255],
            [['client_secret'], 'unique'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => \Yii::$app->getUser()->getIdentity()->className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'client_id' => 'Client ID',
            'client_secret' => 'Client Secret',
            'user_id' => 'User ID',
            'scope' => 'Scope',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(\Yii::$app->getUser()->identityClass, ['id' => 'user_id']);
    }
    
    /**
    * @return \yii\db\ActiveQuery
    */
    public function getUserClientSecretTokens()
    {
            return $this->hasMany(UserClientSecretToken::className(), ['client_id' => 'client_id']);
    }
    
    /**
    * @return \yii\db\ActiveQuery
    */
    public function getUserClientRefreshTokens()
    {
            return $this->hasMany(UserClientRefreshToken::className(), ['client_id' => 'client_id']);
    }
    
    public static function getByUser( $user ) {
        $client = static::find()
            ->where([
                'user_id' => $user->id,
            ])
            ->one();
        if( !$client ) {
            $className = static::className();
            //die($className);
            $client = new static;
            $client->client_id = \Yii::$app->getSecurity()->generateRandomString( rand ( 128 , 255 ) );
            $client->client_secret = \Yii::$app->getSecurity()->generateRandomString( rand ( 128 , 255 ) );
            $client->user_id = $user->id;
            $client->save();
        }
        return $client;
    }
    
    public function getSecretToken() {
        return \verbi\yii2Oauth2Server\models\UserClientSecretToken::generate( $this );
    }
    
    public function getRefreshToken() {
        return \verbi\yii2Oauth2Server\models\UserClientRefreshToken::generate( $this );
    }
    
    public static function findBySecretToken( $token ) {
        \verbi\yii2Oauth2Server\models\UserClientSecretToken::deleteObsolete();
        return static::find()
                ->joinWith( 'userClientSecretTokens' )
                ->where([
                    'user_client_secret_token.token' => $token,])
                ->andWhere([
                    'user_client_secret_token.origin' => RequestOrigin::getRequestOrigin(),
                ])
                ->andWhere([
                    '>=',
                    'user_client_secret_token.expires',
                    date( 'Y-m-d H:i:s', time() ),
                ])
                ->with( 'user' )
                ->one();
    }
    
    public static function findByRefreshToken( $token ) {
        \verbi\yii2Oauth2Server\models\UserClientRefreshToken::deleteObsolete();
        return static::find()
                ->joinWith( 'userClientRefreshTokens' )
                ->where([
                    'user_client_refresh_token.token' => $token,])
                ->andWhere([
                    'user_client_refresh_token.origin' => RequestOrigin::getRequestOrigin(),
                ])
                ->andWhere([
                    '>=',
                    'user_client_refresh_token.expire',
                    date( 'Y-m-d H:i:s', time() ),
                ])
                ->with( 'user' )
                ->one();
    }
}
