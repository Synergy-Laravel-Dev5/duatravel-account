<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Season;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;

// Mock login as user 1
$user = User::first();
if ($user) {
    Auth::login($user);
}
View::share('errors', new ViewErrorBag());

echo "Testing Season Views Rendering...\n";

try {
    $seasons = Season::all();
    $trashCount = Season::onlyTrashed()->count();
    $activeSeason = Season::current();
    $type = 'all';

    $htmlIndex = view('season.index', compact('seasons', 'type', 'trashCount', 'activeSeason'))->render();
    echo "✓ season.index rendered successfully (" . strlen($htmlIndex) . " bytes)\n";

    $currentYear = 2026;
    $htmlCreate = view('season.create', compact('currentYear'))->render();
    echo "✓ season.create rendered successfully (" . strlen($htmlCreate) . " bytes)\n";

    $season = $seasons->first();
    $htmlEdit = view('season.edit', compact('season'))->render();
    echo "✓ season.edit rendered successfully (" . strlen($htmlEdit) . " bytes)\n";

    $trashedSeasons = Season::onlyTrashed()->get();
    $htmlTrash = view('season.trash', ['seasons' => $trashedSeasons])->render();
    echo "✓ season.trash rendered successfully (" . strlen($htmlTrash) . " bytes)\n";

    echo "ALL VIEWS RENDERED CLEANLY WITH NO ERRORS!\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
