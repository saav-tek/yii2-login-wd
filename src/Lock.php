<?php

namespace saavtek\LoginWD;

use Yii;
use yii\validators\Validator;
use saavtek\LoginWD\models\LoginAttempt;

/**
 * Lock is a custom Validator for locking user accounts after repeated failed login attempts
 * 
 * @author SAAV-Tek https://github.com/saav-tek
 * @since 1.0
 */


class Lock extends Validator
{
    public $attempts = 5;
    public $lockDuration = 900;
    public $usernameAttribute = 'username';

    public function validateAttribute($model, $attribute)
    {
        if (!$loginAttempt = LoginAttempt::find()->where(['username' => $model->username])->one()) {
            $loginAttempt = new LoginAttempt;
            $loginAttempt->username = $model->username;
            $loginAttempt->lock_until = 0;
        }

        $loginAttempt->attempts++;

        if ($loginAttempt->lock_until > time()) {
        //Account is locked
            $loginAttempt->lock_until = time() + $this->lockDuration;
            $loginAttempt->save();

            //Remove error from password field - account is locked
            $model->clearErrors($attribute);

            $this->addError($model, $this->usernameAttribute, 'Your account is temporarily locked.');
            $this->addError($model, $this->usernameAttribute, 'Please reset your password to unlock your account or try again later.');
            Yii::warning("Account LOCKED - Failed login attempt " . $loginAttempt->attempts . ": $model->username", get_class($this));

        } elseif ($model->hasErrors($attribute)) {
        //Failed login attempt

            if ($loginAttempt->lock_until > 0) { 
            //lock timer expired, reset counters
                $loginAttempt->attempts = 1;
                $loginAttempt->lock_until = 0;
            }

            $this->addError($model, $attribute, "Failed login attempt " . $loginAttempt->attempts . " of $this->attempts.");
            
            if ($loginAttempt->attempts >= $this->attempts) {
            //Lock account
                $loginAttempt->lock_until = time() + $this->lockDuration;
                $this->addError($model, $attribute, "PLEASE NOTE: your account has been locked temporarily. You can reset your password to unlock it or try again later.");
                Yii::warning("Account LOCKED - Failed login attempt " . $loginAttempt->attempts . ": $model->username", get_class($this));
            } else {
                $this->addError($model, $attribute, "PLEASE NOTE: your account will be locked temporarily after $this->attempts consecutive failed login attempts.");
                Yii::warning("Failed login attempt " . $loginAttempt->attempts . "/$this->attempts: $model->username", get_class($this));
            }

            $loginAttempt->save();
        
        } elseif (!$model->hasErrors()) {
        //No errors - login succeeded, clear previous failed attempts
            LoginAttempt::deleteAll(['username' => $model->username]);
        }   
    }
}