<?php

use Ansas\Util\File;
use Ansas\Util\Path;
use Ansas\Util\Text;

/**
 * @link https://feiertage-api.de/
 */
const VALID_COUNTRIES = ['DE'];
const VALID_STATES    = ['BW', 'BY', 'BE', 'BB', 'HB', 'HE', 'HH', 'MV', 'NI', 'NW', 'RP', 'SL', 'SN', 'ST', 'SH', 'TH', 'NATIONAL'];

try {
    if (!file_exists($file = __DIR__ . '/../../../autoload.php') && !file_exists($file = __DIR__ . '/../autoload.php')) {
        throw new Exception('Cannot find autoloader');
    }

    require_once $file;

    $longOpts = [
        "periodFrom:",
        "periodUntil:",
        "template:",
        "country:",
        "state:",
        "path:",
        "force::",
    ];

    $options = getopt('', $longOpts);
    $errors  = [];

    $periodFrom   = $options['periodFrom'] ?? null;
    $periodUntil  = $options['periodUntil'] ?? null;
    $country      = $options['country'] ?? null;
    $template     = $options['template'] ?? 'default';
    $statesString = $options['state'] ?? null;
    $states       = $statesString != null ? explode(',', $statesString) : VALID_STATES;
    $path         = $options['path'] ?? null;
    $force        = Text::toBool($options['force'] ?? false);

    $minYear = date('Y') - 10;
    $maxYear = date('Y') + 3;

    if (!($periodFrom > $minYear && $periodFrom < $maxYear)) {
        $errors[] = "Invalid: periodFrom / must be between '$minYear' and '$maxYear'";
    }

    if (!($periodUntil > $minYear && $periodUntil < $maxYear)) {
        $errors[] = "Invalid: periodUntil / must be between '$minYear' and '$maxYear'";
    }

    if ($periodFrom > $periodUntil) {
        $errors[] = "Invalid: periodFrom & periodUntil / must be periodFrom <= periodUntil";
    }

    if (!in_array($country, VALID_COUNTRIES)) {
        $errors[] = "Invalid: country / choose from: " . implode(", ", VALID_COUNTRIES);
    }

    foreach ($states as $state) {
        if (!in_array($state, VALID_STATES)) {
            $errors[] = "Invalid: states / choose from: " . implode(", ", VALID_STATES);
            break;
        }
    }

    if (!preg_match('/^[^*?"<>|:]+$/', $path)) {
        $errors[] = "Invalid: path";
    }

    if (!preg_match('/^[^*?"<>|:]*$/', $template)) {
        $errors[] = "Invalid: template";
    }

    $filename = Path::combine($path, $template . '.json');
    if (File::exists($filename) && !$force) {
        $errors[] = "Invalid: template / already exists / use '--force=true' to overwrite the template";
    }

    if ($errors) {
        throw new Exception(implode("\n", $errors));
    }

    $holidays = [];

    for ($year = $periodFrom; $year <= $periodUntil; $year++) {
        foreach ($states as $state) {
            $url = "https://feiertage-api.de/api?jahr={$year}&nur_land={$state}";

            $data = file_get_contents($url);
            $data = json_decode($data, true);
            if (!$data) {
                throw new Exception("Cannot get date from: '{$url}'");
            }

            foreach ($data as $key => $value) {
                $date      = $value['datum'] ?? null;
                $isHoliday = holidayHintsToIsHoliday($value['hinweis'] ?? null);

                // Ignore entry?
                if (!$date) {
                    continue;
                }
                if (!$isHoliday) {
                    continue;
                }

                if (!preg_match('/^\d{4}-\d{2}-\d{2}/u', $date)) {
                    throw new Exception("Invalid date: " . json_encode($value));
                }

                $holidays[$date] = $key;
            }
        }
    }

    ksort($holidays);

    Path::create($path);
    Path::chdir($path);
    File::putContent($filename, json_encode($holidays));

    fwrite(STDOUT, "DONE" . "\n");
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(500);
}

function holidayHintsToIsHoliday(?string $hint): bool
{
    $hint = trim($hint ?? '');

    // Always holiday if no hint provided
    if (!$hint) {
        return true;
    }

    $hintMap = [
        'haben Schüler am Gründonnerstag und am Reformationstag schulfrei' => false,
        'ist nur im Stadtgebiet' => false,
        'Buß- und Bettag' => false,
        'ist kein gesetzlicher Feiertag' => false,
        'Mariä Himmelfahrt' => true,
        'Einmaliger Feiertag' => true,
    ];
    foreach ($hintMap as $neede => $isHoliday) {
        if (false !== stripos($hint, $neede)) {
            return $isHoliday;
        }
    }

    throw new Exception("Cannot map hint: '{$hint}'");
}
