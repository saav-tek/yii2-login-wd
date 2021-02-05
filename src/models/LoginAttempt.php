<?php

namespace saavtek\LoginWD\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for Login attempts
 *
 * @property string $username
 * @property integer $attempts
 * @property integer $lock_until
 * @property integer $updated_at
 * @property integer $created_at
 * 
 * @author SAAV-Tek https://github.com/saav-tek
 * @since 1.0
 */

class LoginAttempt extends \yii\redis\ActiveRecord
{
    /**
     * Returns the list of all attribute names of the model.
     * @return array list of attribute names.
     */
    public function attributes()
    {
        return ['username', 'attempts', 'lock_until'];
    }

    /**
     * Returns the primary key name(s) for this AR class.
     * @return string[] the primary keys of this record.

     */
    public static function primaryKey()
    {
        return ['username'];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username'], 'unique'],
            [['username'], 'required'],
            [['username'], 'string', 'max' => 255],
            [['attempts', 'lock_until'], 'integer'],
        ];
    }
}