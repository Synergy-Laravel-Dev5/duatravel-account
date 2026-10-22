<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Season;

echo "=== SEASONS VERIFICATION ===\n";

$seasons = Season::all();
echo "Total Seasons: " . $seasons->count() . "\n";
foreach ($seasons as $s) {
    echo "- ID: {$s->id} | Name: {$s->name} | Type: {$s->season_type} | Year: {$s->year} | Hijri: {$s->hijri_year} | Active: " . ($s->is_active ? 'YES' : 'NO') . "\n";
}

echo "\n=== HELPER FUNCTIONS ===\n";
echo "currentSeasonName(): " . currentSeasonName() . "\n";
echo "currentSeasonType(): " . currentSeasonType() . "\n";
echo "currentSeasonYear(): " . currentSeasonYear() . "\n";

echo "\n=== ROUTE CHECK ===\n";
echo "season.index route: " . route('season.index') . "\n";
echo "season.create route: " . route('season.create') . "\n";
if ($seasons->first()) {
    echo "season.edit route: " . route('season.edit', $seasons->first()->id) . "\n";
    echo "season.switch route: " . route('season.switch', $seasons->first()->id) . "\n";
}

echo "\nAll checks passed successfully!\n";
