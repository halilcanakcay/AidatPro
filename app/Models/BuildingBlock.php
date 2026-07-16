<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BuildingBlock extends Model
{
    protected $fillable = ['site_id', 'name'];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function apartments(): HasMany
    {
        return $this->hasMany(Apartment::class);
    }
}
