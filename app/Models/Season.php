<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Season extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'year'       => 'integer',
        'is_active'  => 'boolean',
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function scopeHajj($query)
    {
        return $query->where('season_type', 'hajj');
    }

    public function scopeUmrah($query)
    {
        return $query->where('season_type', 'umrah');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCurrentDefault($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get currently active season in user's session, or fall back to default active season.
     */
    public static function current()
    {
        $sessionSeasonId = session('selected_season_id');
        if ($sessionSeasonId) {
            $season = static::find($sessionSeasonId);
            if ($season) {
                return $season;
            }
        }

        $sessionPkg  = session('dashboard_package', 'hajj');
        $sessionYear = (int) session('dashboard_year', date('Y'));

        // Try to find matching season by package and year
        $season = static::where('season_type', $sessionPkg)
            ->where('year', $sessionYear)
            ->first();

        if ($season) {
            return $season;
        }

        // Try to find default is_active season for current package
        $season = static::where('season_type', $sessionPkg)
            ->where('is_active', true)
            ->first();

        if ($season) {
            return $season;
        }

        // Fallback: first season of this package
        return static::where('season_type', $sessionPkg)->latest('year')->first();
    }
}
