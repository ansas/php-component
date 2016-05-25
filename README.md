#Ansas\Component

Collection of cross-project PHP classes


## Ansas\Component\Convert\ConvertPrice
Convert "dirty" prices into Euro or Cent

Methods (without internal):
```php
public function __construct($price = null, $format = self::EURO)
public function __toString()
public function clearPrice()
public function getPrice($format = self::EURO)
public function setPrice($price, $format = self::EURO)
public function sanitize($price, $format = self::EURO)
final public static function getInstance()
```


## Ansas\Component\Convert\ConvertToNull
This trait can be used to "sanitize values" by setting empty values to null.

It holds methods which can be called in order to check if a field is empty
and set it to "null" before calling the parent setter method. By doing this
we have a more cleaned up object and also prevent e. g. the "versionable"
behavior from adding a new version (as "" !== null).

Methods:
```php
protected function convertEmptyToNull($value, array $considerNull = [''])
protected function convertToNull($value, array $considerNull, $trim = false)
protected function trimAndConvertEmptyToNull($value, array $considerNull = [''])
protected function trimAndConvertToNull($value, array $considerNull)
```


## Ansas\Component\Session\ThriftyFileSession
All you need is to configure the native PHP session settings as usual
(see http://php.net/manual/de/session.configuration.php).

After that load this class and call the static init() method:
Ansas\Component\Session\ThriftyFileSession::init();

This will automatically start the session and you can use the native
session handling functions and the super global variable $_SESSION
as usual.

The benefit of this class is that session storage on disk and session
cookies are set only if $_SESSION has data. Also the session cookie
will also be updated with every request, so it will not expire before
session.gc_maxlifetime and the cookie will be deleted automatically
if you destroy the session (both not the case with pure native PHP
sessions).

Methods (without internal):
```php
public static function cleanup($cleanup)
public static function exists()
public static function getInstance($start = false)
public static function init($start = true)
public static function kill()
public static function started()
public static function ttl($ttl)
```


## TODO
- Write tests
