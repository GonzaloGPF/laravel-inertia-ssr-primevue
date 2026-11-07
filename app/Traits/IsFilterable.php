<?php

namespace App\Traits;

use EloquentFilter\Filterable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

trait IsFilterable
{
    use Filterable;

    public int $maxLimit = 1000;

    /**
     * @param  Builder<Model>  $query
     * @return Collection<int, Model>|LengthAwarePaginator<int, Model>
     */
    public function scopeGetOrPaginate(Builder $query): Collection|LengthAwarePaginator
    {
        $perPage = request('per_page') ?? config('app.default_items_by_page');
        $columns = request('columns') ?? ['*'];
        $limit = request('limit');

        if ($perPage > $this->maxLimit) {
            $perPage = config('app.default_items_by_page');
        }

        if ($limit && $limit <= $this->maxLimit) {
            return $query->limit($limit)->get();
        } else {
            $paginator = $query->paginate($perPage, $columns, 'page', request('page'));

            $paginator->appends($this->filtered);

            return $paginator;
        }
    }
}
