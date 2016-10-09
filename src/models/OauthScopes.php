<?php

namespace verbi\yii2Oauth2Server\models;

use Yii;

/**
 * This is the model class for table "oauth_scopes".
 *
 * @property string $scope
 * @property integer $is_default
 * 
 * @author Philip Verbist <philip.verbist@gmail.com>
 * @link https://github.com/verbi/Yii2-Oauth2-Server/
 * @license https://opensource.org/licenses/GPL-3.0
 */
class OauthScopes extends \filsh\yii2\oauth2server\models\OauthScopes
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%oauth_scopes}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['scope', 'is_default'], 'required'],
            [['is_default'], 'integer'],
            [['scope'], 'string', 'max' => 2000]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'scope' => 'Scope',
            'is_default' => 'Is Default',
        ];
    }
}