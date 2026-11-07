<?php

namespace App\Interfaces;

use App\Models\User;
use App\Traits\HasCreatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A model that records who created it, in its created_by column.
 *
 * @see HasCreatedBy
 */
interface HasCreatedByInterface
{
    /**
     * @return BelongsTo<User, covariant Model>
     */
    public function createdBy(): BelongsTo;
}
