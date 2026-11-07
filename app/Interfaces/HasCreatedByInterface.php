<?php

namespace App\Interfaces;

use App\Traits\HasCreatedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A model that records who created it, in its created_by column.
 *
 * @see HasCreatedBy
 */
interface HasCreatedByInterface
{
    public function createdBy(): BelongsTo;
}
