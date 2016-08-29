# PHP components

[![Latest Stable Version](https://poser.pugx.org/ansas/php-component/v/stable)](https://packagist.org/packages/ansas/php-component)
[![Total Downloads](https://poser.pugx.org/ansas/php-component/downloads)](https://packagist.org/packages/ansas/php-component)
[![Latest Unstable Version](https://poser.pugx.org/ansas/php-component/v/unstable)](https://packagist.org/packages/ansas/php-component)
[![License](https://poser.pugx.org/ansas/php-component/license)](https://packagist.org/packages/ansas/php-component)

Collection of cross-project PHP classes.

Install:
```shell
$ composer require ansas/php-component
```


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


## Ansas\Component\Collection\Collection
Making handling of context data a bit easier.

Usage:
```php
use Ansas\Component\Collection\Collection;

// Create e. g. context
$context = new Collection([
    'key1' => 'value1',
]);

// Setter
$context->key2 = 'value2';
$context['key3'] = 'value3';
$context->set('key4', 'value4');
$context->append([
    'key4', 'value4',
    'key5', 'value5',
]);

// Getter
$key1 = $context->get('key1');
$key2 = $context['key2'];
$key3 = $context->key3;
$key4 = $context->need('key6'); // throws Exception if key does not exist

// Getter (array)
$array = $context->all();
$partArray1 = $context->only('key1,key3');
$partArray2 = $context->only(['key2', 'key4']);

// Count
$elemets = $context->count();
$elemets = count($context);

// Delete
$context->remove('key1');
unset($context->key1);
unset($context['key1']);

// Delete (extended)
$context->replace([
    'key4', 'value4',
    'key5', 'value5',
]); // replaces all existing elements with specified elements
$context->clear(); // deletes all elements

// Check
if ($context->has('key1')) {}
if (isset($context->key1)) {}
if (isset($context['key1'])) {}

// Iterate
foreach ($context as $key => $value) {}
foreach ($context->getIterator() as $key => $value) {}

// Special
$context->add('key6', 'value1'); // element key6 is string
$context->add('key6', 'value2'); // key6 is converted to array automatically
$keyArray = $context->keys(); // new numeric array containing keys only
$valueArray = $context->values(); // new numeric array containing values only
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


## Ansas\Monolog\Profiler
A small profiler (stop watch) for different profiles that are logged to any Monolog logger.

Methods (without internal):
```php
// object handling methods
public function __construct(Logger $logger, $level = Logger::DEBUG, callable $formatter = null)
public function __destruct()
public function __get($profile)
public function __toString()

// profile methods
public function add($profile)
public function context(Collection $context = null)
public function get($profile)
public function has($profile)
public function remove($profile)
public function removeAll()
public function set($profile)

// stopwatch methods
public function start($message = 'Start', $context = [])
public function start($context = [])
public function startAll($message = 'Start', $context = [])
public function lap($message = 'Lap', $context = [])
public function lap($context = [])
public function stop($message = 'Stop', $context = [])
public function stop($context = [])
public function stopAll($message = 'Stop', $context = [])
public function note($message = 'Note', $context = [])
public function note($context = [])
public function clear()
public function clearAll()
public function restart($message = 'Restart', $context = [])
public function restart($context = [])

// setter methods
public function setLogger(Logger $logger)
public function setLevel($level)
public function setFormatter(callable $formatter = null)

// time methods
public function timeCurrent()
public function timeStart()
public function timeStop()
public function timeTotal()
public function timeLap($lap = -1)

// default methods
public function defaultFormatter()

// helper methods
public function countLaps()
public function getFormatter()
public function getLaps()
public function getName()
public function getProfiles()
public function isRunning()
public function isStarted()
public function isStopped()
```

Usage:
```php
use Ansas\Monolog\Profiler;

$profiler = new Profiler($logger);

// Starts default profile (must be started before any other profiles)
$profiler->start();

sleep(1);

// Adds profile "testA" and logs default message "Start"
$profiler->add("testA")->start(); 

sleep(1);

// Gets profile "testA" and adds a lap incl. logging it with default message 
// "Lap"
$profiler->get("testA")->lap();

// Adds profile "anotherB" (add always returns new new profile)
$anotherB = $profiler->add("anotherB");

// Starts profile "anotherB" with individual message "B is rolling"
$anotherB->start("B is rolling");

sleep(1);

// Add lap for default profile with individual message
$profiler->lap("Lap for main / default profile");

// Stop profile "anotherB" and log with default message "Stop"
$anotherB->stop();

// Add subprofile "moreC" to profile "anotherB" and start with individual 
// message and context array (default Monolog / PSR3 signature)
$moreC = $anotherB->add("moreC")->start("Message", ['key' => 1]);

// Add profile "lastD"  to "anotherB" as well
$lastD = $profiler->get("anotherB")->add("lastD");

// Just log a note to profile "lastD"
$lastD->note("Starting soon");
sleep(1);

// Clear only profile "anotherB" and start it again
$anotherB->clear()->start("Restarting after clear");

// Stopping "testA" (will not be restarted with upcomming "startAll")
$testA = $profiler->get("testA");
$testA->stop();

// Just add a profile without doing anything (will be started with startAll)
$profiler->add("test123");

// Start all profiles incl. default that are not startet
$profiler->startAll("Starting all profiles not started yet");

// Add "veryLastE" to "lastD" but do not log it (as message is null)
$veryLastE = $lastD->add("veryLastE")->start(null);

sleep(1);

// Some more profiling
$profiler->get("anotherB")->get("moreC")->lap("Lapdance ;P");
$veryLastE->stop("Stopping E");

// Make things easier, just create profiles via set() or magic method __get()
$profiler->set("profilX")->start();
$profiler->set("profilX")->lap();
$profiler->profilX->lap();
$profiler->profilX->stop();

sleep(1);

// Clear "lastD" all subprofiles (no logging will be done now or on exit)
$lastD->clearAll("Clearing D and all subprofiles");

// Stops all counters (done automatically on script end)
$anotherB->stopAll();

// Display current profiling status / report
echo $profiler;

// All running profiles are lapped (if needed), stopped and logged if profiler
// is destroyed or program ends
$profiler = null;
gc_collect_cycles();
sleep(3);

exit;
```


## Ansas\Monolog\Processor\ConsoleColorProcessor
Adds colors to Monolog for console output via Processor. The `$record` parts `level_name` and `message` are colored by this processor

Usage:
```php
use Ansas\Monolog\Processor\ConsoleColorProcessor;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

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


## Contribute

Everybody can contribute to this package. Just:

1. fork it,
2. make your changes and
3. send a pull request.

Please make sure to follow [PSR-1](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md) and [PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md) coding conventions.


## License

__MIT license__ (see the [LICENSE](LICENSE.md) file for more information).
