<?php

namespace App\Models;

use App\AssetStatus;
use Database\Factories\AssetFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['asset_code', 'name', 'category', 'brand', 'model', 'serial_number', 'status', 'assigned_user_id', 'purchase_date', 'warranty_end_date', 'notes'])]

class Asset extends Model
{
    /** @use HasFactory<AssetFactory> */
    use HasFactory, SoftDeletes;

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class);
    }

    protected function casts(): array
    {
        return [
            'status' => AssetStatus::class,
            'purchase_date' => 'date',
            'warranty_end_date' => 'date',
        ];
    }
}
