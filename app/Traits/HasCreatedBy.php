<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Trait HasCreatedBy
 */
trait HasCreatedBy
{
    public static function bootHasCreatedBy(): void
    {
        static::creating(function (self $model) {
            $model->created_by = $model->created_by ?? auth()->id();
        });
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
