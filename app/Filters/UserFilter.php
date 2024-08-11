<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class UserFilter extends Filter
{
    public $relations = [];

    public function email($value): Builder
    {
        $this->where('email', 'like', "%$value%");

        return $this->query;
    }

    public function verified($value): Builder
    {
        $this->nullable('email_verified_at', $value);

        return $this->query;
    }

    public function phone($value): Builder
    {
        $this->where('phone', 'like', "%$value%");

        return $this->query;
    }
}
