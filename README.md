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
public static function getInstance()
public static function init()
public static function force(boolean $force)
public static function ttl(int $ttl)
public static function cleanup(callable $cleanup)
public static function kill()
```

## Ansas\Monolog\Processor\ConsoleColorProcessor
Adds colors to Monolog for console output via Processor. The `$record` parts `level_name` and `message` are colored by this processor

Usage:
```php
$loggerFormat   = "[%datetime%] %level_name% %message% %context%\n";
$loggerLevel    = getenv('DEBUG') ? Logger::DEBUG : Logger::NOTICE;
$loggerTimeZone = new DateTimeZone('Europe/Berlin');

$formatter = new LineFormatter($loggerFormat, $loggerTimeFormat);
$formatter->ignoreEmptyContextAndExtra(true);

$defaultHandler = new StreamHandler('php://stdout', $loggerLevel, $bubble = false);
$defaultHandler->setFormatter($formatter);

$errorHandler = new StreamHandler('php://stderr', Logger::ERROR, $bubble = false);
$errorHandler->setFormatter($formatter);

$logger = new Logger('console');
$logger->pushHandler($defaultHandler);
$logger->pushHandler($errorHandler);
$logger->pushProcessor(new ConsoleColorProcessor());
$logger->useMicrosecondTimestamps(true);

$logger->debug(str_repeat("Xx ", rand(5, 40)));
$logger->info(str_repeat("Xx ", rand(5, 40)));
$logger->notice(str_repeat("Xx ", rand(5, 40)));
$logger->warning(str_repeat("Xx ", rand(5, 40)));
$logger->error(str_repeat("Xx ", rand(5, 40)));
$logger->critical(str_repeat("Xx ", rand(5, 40)));
$logger->alert(str_repeat("Xx ", rand(5, 40)));
$logger->emergency(str_repeat("Xx ", rand(5, 40)));
```


## TODO
- Write tests
