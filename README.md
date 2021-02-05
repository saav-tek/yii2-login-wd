Login Watchdog Extension
========================

Keep track of failed login attempts, and lock a user account and disable login after a given number of consecutive failed attempts.

The main functionality is implemented as validation rules.
- 'Lock' is the validation rule to use on the 'password' attribute of the LoginForm model to monitor for login attempts and lock accounts after a certain number of failed attempts.
- 'Unlock' is an optional validation rule that can be used on the ResetPasswordForm model to allow users to self-unlock their accounts when resetting their password.

Additionally, there are two helper static functions:
- Unlock::User() can be called from a Controller or Model to unlock a specific user.
- Unlock::All() can be called from a Controller or Model to unlock ALL users (delete all login attempts).

Original idea based on this behavior: https://github.com/giannisdag/yii2-check-login-attempts

Installation
------------

The preferred way to install this extension is through [composer](https://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist saavtek/yii2-login-wd "*"
```

or add the following line to the require section of your `composer.json` file.

```
"saavtek/yii2-login-wd": "*"
```

#

## Requirements
To use this extension, the [[yii\redis\Connection|Connection]] class must be configured in the Application configuration:
```php
return [
    //....
    'components' => [
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => 'localhost',
            'port' => 6379,
            'database' => 0,
        ],
    ]
];
```
For more details, please refer to the Redis extension documentation: https://github.com/yiisoft/yii2-redis

## Usage

### Validation
To limit login attempts, and lock accounts after repeated failed attempts, add the 'Lock' validation rule at the END of your LoginForm model rules(), after the password has been validated:
```php
[
    'password', // The atribute to be validated
    \saavtek\LoginWD\validators\Lock::className(), // The 'Lock' rule
    'skipOnError' => false,  //MANDATORY so that this validation rule is not skipped
    'attempts' => 5, // Optional - Max attempts alowed, default is 5 attempts
    'lockDuration' => 900, // Optional - Number of Seconds to disable login after exceeding `attemps`, default is 900 seconds
    'usernameAttribute' => 'username', // Optional - The attribute used for identifying a user for login, default is 'username'
]
```
Everytime a user fails to login (wrong username or password), an internal counter will be incremented. When a user fails to login 'attempts' times, the account will be locked for 'lockDuration' seconds.
#
If desired, add the Unlock validation rule at the END of your ResetPasswordForm model rules() to allow the user to self-unlock the account when reseting his/her password 
```php
[
    'password', // The atribute to be validated
    \saavtek\LoginWD\Unlock::className(), // The 'Unlock' rule
    'username' => $this->_user->username, //MANDATORY - the username used for login. In this example, it is being obtained from the model's $_user private var
    'usernameAttribute' => 'username', // Optional - The attribute used for identifying a user for login, default is 'username'
]
```
#
### Maintenance
You can call the Unlock::User() static function from a Controller to unlock a particular user:
```php
\saavtek\LoginWD\Unlock::User($username, $category, $usernameAttribute)
```
Where:
- $usernameAttribute is the attribute used for identifying a user for login (optional, default is 'username').
- $username is the value of the usernameAttribute used for login (REQUIRED).
- $category is the category used for logging (optional, default is null, which will generate a log with 'application' category).
#
You can call the Unlock::All() static function from a Controller to delete all the existing attempts
```php
\saavtek\LoginWD\Unlock::All();
```
You can use the LoginAttempt model for other common AR operations:
```php
use \saavtek\LoginWD\models\LoginAttempt;

$totalAttempts = LoginAttempt::find()->sum('attempts');
$uniqueAttempts = LoginAttempt::find()->count();
$lockedAccounts = LoginAttempt::find()->where(['>', 'lock_until', time()])->count();
```