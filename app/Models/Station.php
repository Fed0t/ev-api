<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Station extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'company_id',
        'position',
        'address',
        'latitude',
        'longitude',
    ];

    protected $hidden = [
        'company_id',
        'position',
        'created_at',
        'updated_at',
    ];

    /**
     * Get position geom accessor
     */
    protected function position(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value,
        );
    }

    /**
     * Relationship with companies
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public static function getNearby($lat, $lng, $distance = 50)
    {
        // radius of earth; @note: the earth is not perfectly spherical, but this is considered the 'mean radius'
        $radius = 6371.009; // in kilometers

        // latitude boundaries
        $maxLat = (float) $lat + rad2deg($distance / $radius);
        $minLat = (float) $lat - rad2deg($distance / $radius);

        // longitude boundaries (longitude gets smaller when latitude increases)
        $maxLng = (float) $lng + rad2deg($distance / $radius / cos(deg2rad((float) $lat)));
        $minLng = (float) $lng - rad2deg($distance / $radius / cos(deg2rad((float) $lat)));

        return self::where('latitude', '>', $minLat)
            ->where('latitude', '<', $maxLat)
            ->where('longitude', '>', $minLng)
            ->where('longitude', '<', $maxLng)
            ->orderBy(DB::raw('ABS(latitude - '.(float) $lat.') + ABS(longitude - '.(float) $lng.') '), 'ASC')
            ->groupBy('latitude', 'longitude', 'id');
    }
}
