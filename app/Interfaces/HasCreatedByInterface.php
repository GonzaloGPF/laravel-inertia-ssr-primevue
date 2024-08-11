<?php

namespace App\Interfaces;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Interface HasCreatedByInterface
 *
 * @property int created_by
 * @property int payment_state
 * @property User createdBy
 */
interface HasCreatedByInterface
{
    public function createdBy(): BelongsTo;
}
