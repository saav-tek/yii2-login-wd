<?php

namespace saavtek\LoginWD;

use Yii;
use yii\validators\Validator;
use saavtek\LoginWD\models\LoginAttempt;

/**
 * Unlock is a custom Validator for unlocking user accounts
 * 
 * @author SAAV-Tek https://github.com/saav-tek
 * @since 1.0
 */


class Unlock extends Validator
{
    public $usernameAttribute = 'username';
    public $username = null;

    public function validateAttribute($model, $attribute)
    {
        if (!$model->hasErrors() and $this->username) {
            static::User($this->username, null, $this->usernameAttribute);
        }
    }

    public static function User($username, $category=null, $usernameAttribute='username')
    {
        $loginAttempt = LoginAttempt::findOne([$usernameAttribute => $username]);
        if ($loginAttempt and ($loginAttempt->delete() > 0))
            Yii::info("Unlocked account: " . $username, $category ?? __METHOD__);
    }

    public static function All()
    {
        LoginAttempt::deleteAll();
        Yii::info("Deleted ALL login attempts.", __METHOD__);
    }
}