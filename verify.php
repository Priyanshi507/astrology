<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\AstroCalculator;
use App\Services\MuhratCalculator;
use App\Services\HinduFestivalCalculator;

$yr = 2026;
$mo = 5;
$dy = 27;
$lat = 28.6139;
$lon = 77.2090;
$utcOff = 5.5; // IST

$fullDay = MuhratCalculator::computeFullDay($yr, $mo, $dy, $lat, $lon, $utcOff);

echo "1. CORRECTED CHOGHADIYA FOR TODAY (Delhi, IST +5:30)\n";
echo "Day Choghadiya:\n";
foreach ($fullDay['choghadiya']['day'] as $c) {
    echo "  " . $c['name'] . ": " . $c['start'] . " - " . $c['end'] . "\n";
}
echo "Night Choghadiya:\n";
foreach ($fullDay['choghadiya']['night'] as $c) {
    echo "  " . $c['name'] . ": " . $c['start'] . " - " . $c['end'] . "\n";
}

echo "\n2. TODAY'S TITHI WITH EXACT END TIME\n";
$jdRise = $fullDay['jdRise'];
$tk = AstroCalculator::computeTithiKarana($jdRise);
$tithiEnd = AstroCalculator::findTithiEnd($jdRise, $utcOff);
echo "  Tithi: " . ($tk['tithi']['paksha'] ?? '') . " " . ($tk['tithi']['n'] ?? '') . "\n";
echo "  End Time: " . $tithiEnd . "\n";

echo "\n3. TODAY'S NAKSHATRA WITH EXACT END TIME\n";
$nakHi = $fullDay['nakHi'];
$nakEnd = AstroCalculator::findNakshatraEnd($jdRise, $utcOff);
echo "  Nakshatra: " . $nakHi . "\n";
echo "  End Time: " . $nakEnd . "\n";

echo "\n4. NEXT 5 UPCOMING FESTIVALS WITH EXACT DATES\n";
$festivals = HinduFestivalCalculator::calculateYear($yr, $lat, $lon, $utcOff)['festivals'];
$todayDate = sprintf('%04d-%02d-%02d', $yr, $mo, $dy);
$upcoming = array_filter($festivals, fn($f) => $f['date'] >= $todayDate);
usort($upcoming, fn($a, $b) => $a['date'] <=> $b['date']);
$upcoming5 = array_slice($upcoming, 0, 5);
foreach ($upcoming5 as $f) {
    echo "  - " . $f['date'] . ": " . $f['name'] . " (" . $f['name_hi'] . ") - " . $f['tithi'] . "\n";
}
echo "\nDONE.\n";
