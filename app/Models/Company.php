<?php

namespace App\Models;

use App\Traits\NestedSetTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;
    use NestedSetTrait;

    protected $fillable = [
        'name',
        'left',
        'right',
        'parent_id',
    ];

    protected $hidden = [
        'left',
        'right',
        'parent_id',
        'created_at',
        'updated_at',
    ];

    /**
     * Create a new company with left right boundaries
     */
    public static function newCompany($data)
    {
        $leftRight = self::getLeftRight();

        return self::firstOrCreate([
            'name' => $data['name'],
            'left' => data_get($leftRight, 'left', 0),
            'right' => data_get($leftRight, 'right', 0),
        ]);
    }
}
