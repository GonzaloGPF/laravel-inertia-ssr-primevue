<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class UserFilter extends Filter
{
    /** @var array<int, string> */
    public $relations = [];

    protected array $searchable = ['name', 'email', 'phone'];

    /**
     * @param  mixed  $value
     * @return Builder<Model>
     */
    public function email($value): Builder
    {
        $this->where('email', 'like', "%$value%");

        return $this->query;
    }

    /**
     * @param  mixed  $value
     * @return Builder<Model>
     */
    public function verified($value): Builder
    {
        $this->nullable('email_verified_at', $value);

        return $this->query;
    }

    /**
     * @param  mixed  $value
     * @return Builder<Model>
     */
    public function phone($value): Builder
    {
        $this->where('phone', 'like', "%$value%");

        return $this->query;
    }
}
