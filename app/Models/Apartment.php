<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Apartment extends Model
{
    protected $fillable = ['building_block_id', 'number', 'floor_no', 'status'];

    public function buildingBlock(): BelongsTo
    {
        return $this->belongsTo(BuildingBlock::class);
    }

    public function residents(): HasMany
    {
        return $this->hasMany(Resident::class);
    }

    public function activeResident(): HasOne
    {
        return $this->hasOne(Resident::class)->where('is_active', true);
    }

    public function dues(): HasMany
    {
        return $this->hasMany(Due::class);
    }
}
