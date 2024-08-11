<?php

namespace App\Filters;

use App\Interfaces\FilterableMorph;
use App\Interfaces\HasCreatedByInterface;
use Carbon\Carbon;
use EloquentFilter\ModelFilter;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Filter extends ModelFilter
{
    private const MIN_PREFIX = 'min_'; // Prefix used by front to indicate a min value in a range filter

    private const MAX_PREFIX = 'max_'; // Prefix used by front to indicate a max value in a range filter

    private const NOT_PREFIX = 'not_'; // Prefix used by front to indicate excluded values

    private const WITH = 'with'; // Input key to specify eager load relations

    private const HAVING = 'having'; // Filter by relationship existence

    private const DOESNT_HAVE = 'doesnt_have'; // Filter by relationship absence

    private const COUNT = 'count'; // Input key to specify counting relations

    private const RANDOMIZE = 'randomize'; // Applies random order

    private const SORT = 'sort'; // Sorting

    private const LIMIT = 'limit'; // Limit results

    private array $errors;

    private array $enumColumns;

    public function __construct($query, array $input = [], $relationsEnabled = true)
    {
        parent::__construct($query, $input, $relationsEnabled);

        $this->enumColumns = collect($this->query->getModel()->getCasts())
            ->filter(fn (string $cast) => Str::startsWith($cast, 'App\Enums'))
            ->keys()
            ->toArray();
    }

    public function handle(): Builder
    {
        $query = parent::handle();

        $this->applyRanges();

        $this->applyNotFilters();

        $this->applyWith();

        $this->applyHaving();

        $this->applyDoesntHave();

        $this->applyConstants();

        $this->applyCounts();

        $this->applySorting();

        $this->applyLimit();

        return $query;
    }

    private function applyRanges(): void
    {
        collect(array_keys($this->input))
            ->filter(fn (string $input) => Str::startsWith($input, [self::MIN_PREFIX, self::MAX_PREFIX]) && ! method_exists($this, Str::camel($input)))
            ->map(fn (string $input) => $this->applyRange($input, $this->input[$input]));
    }

    private function applyNotFilters(): void
    {
        collect(array_keys($this->input))
            ->filter(fn (string $input) => Str::startsWith($input, [self::NOT_PREFIX]) && ! method_exists($this, Str::camel($input)))
            ->map(fn (string $input) => $this->applyNotFilter($input, $this->input[$input]));
    }

    private function applyWith(): void
    {
        $relations = collect($this->input[self::WITH] ?? [])
            ->mapWithKeys(fn ($relation, $key) => is_numeric($key) ? [$key => Str::camel($relation)] : [Str::camel($key) => $relation])
            ->toArray();

        $this->query->with($relations);
    }

    private function applyHaving(): void
    {
        collect($this->input[self::HAVING] ?? [])
            ->map(fn ($relation) => Str::camel($relation))
            ->each(fn (string $relation) => $this->query->whereHas($relation));
    }

    private function applyDoesntHave(): void
    {
        collect($this->input[self::DOESNT_HAVE] ?? [])
            ->map(fn ($relation) => Str::camel($relation))
            ->each(fn (string $relation) => $this->query->whereDoesntHave($relation));
    }

    private function applyConstants(): void
    {
        collect($this->enumColumns)
            ->filter(fn (string $enumColumn) => $this->canFilterByConstant($enumColumn))
            ->each(function (string $enumColumn) {
                $value = $this->input[$enumColumn];
                if (is_array($value)) {
                    $this->query->whereIn($enumColumn, $value);
                } else {
                    $this->query->where($enumColumn, $value);
                }
            });
    }

    private function applyCounts(): void
    {
        $input = collect($this->input[self::COUNT] ?? [])
            ->map(fn ($relation) => Str::camel($relation));

        $rootCounts = $input
            ->filter(fn ($relation) => ! str_contains($relation, '.'))
            ->toArray();

        $nestedCounts = $input
            ->filter(fn ($relation) => str_contains($relation, '.'))
            ->mapWithKeys(function ($relation) {
                $parts = explode('.', $relation);

                return [$parts[0] => fn ($relation) => $relation->withCount($parts[count($parts) - 1])];
            })
            ->toArray();

        if (count($nestedCounts)) {
            $this->query->with($nestedCounts);
        }

        $this->query->withCount($rootCounts);
    }

    private function applyRange(string $input, $value): void
    {
        // Clean up prefixes to determine the table's column name
        $column = str_replace(self::MIN_PREFIX, '', str_replace(self::MAX_PREFIX, '', $input));

        $operator = Str::startsWith($input, self::MIN_PREFIX)
            ? '>='
            : '<=';

        if (isDateString($value)) {
            try {
                $date = Carbon::parse($value);
                $dateString = $operator === '>='
                    ? $date->startOfDay()->toDateTimeString()
                    : $date->endOfDay()->toDateTimeString();

                $this->query->where($column, $operator, $dateString);
            } catch (Exception $exception) {
                $this->errors[$input] = trans('exceptions.wrong_format', ['value' => $value]);
            }
        } else {
            $this->query->where($column, $operator, (float) $value);
        }
    }

    private function applyNotFilter(string $input, $value): void
    {
        // Clean up prefixes to determine the table's column name
        $column = str_replace(self::NOT_PREFIX, '', $input);

        if (is_array($value)) {
            $this->whereIn($column, $value, 'and', true); // Where not in
        } else {
            $this->where($column, '!=', $value);
        }
    }

    /**
     * Orders by a 'sort' string if it's present in request
     * This sort string must be with the format 'attribute|orderDirection'
     * for example: a string 'name|asc' means: ordering by 'name' in 'ascending' order.
     * By default, it will order by created_at
     */
    private function applySorting(): void
    {
        $table = $this->query->getModel()->getTable();
        $sortString = $this->input[self::SORT] ?? null;

        if ($this->input[self::RANDOMIZE] ?? false) {
            $this->query->inRandomOrder();
        } elseif ($sortString && str_contains($sortString, '|')) {
            $this->query->getQuery()->orders = []; // clean up any previous ordering

            [$attribute, $direction] = explode('|', $sortString);

            $this->query->orderBy("$table.$attribute", $direction);
        } else {
            $this->query->latest("$table.created_at"); // default ordering
        }
    }

    /**
     * Apply limit
     */
    private function applyLimit(): void
    {
        $limit = $this->input[self::LIMIT] ?? null;

        if ($limit) {
            $this->query->limit($limit);
        }
    }

    public function createdBy($userIds): Builder
    {
        if ($this->query->getModel() instanceof HasCreatedByInterface) {
            $this->whereIn('created_by', $userIds);
        }

        return $this->query;
    }

    public function ids($ids): Builder
    {
        $this->filterMultiple('id', $ids);

        return $this->query;
    }

    public function exceptIds($ids): Builder
    {
        $this->whereIn('id', $ids, 'and', true);

        return $this->query;
    }

    public function id($id): Builder
    {
        $this->where('id', $id);

        return $this->query;
    }

    protected function modelType($modelType): Builder
    {
        $model = $this->query->getModel();

        if (method_exists($model, 'getMorphColumn')) {
            $column = $model->getMorphColumn();
            $this->where("{$column}_type", modelByString($modelType));
        }

        return $this->query;
    }

    protected function model($modelId): Builder
    {
        $model = $this->query->getModel();

        if ($model instanceof FilterableMorph) {
            $column = $model->getMorphColumn();
            $this->where("{$column}_id", $modelId);
        }

        return $this->query;
    }

    protected function filterMultiple(string $column, $values): Builder
    {
        if (is_string($values) && str_contains($values, ',')) {
            $values = explode(',', $values);
        }

        if (! is_array($values)) {
            $values = [$values];
        }

        return $this->whereIn($column, $values);
    }

    /**
     * @param  string  $column
     * @param  mixed  $values
     * @param  string  $boolean
     * @param  false  $not
     */
    public function whereIn($column, $values, $boolean = 'and', $not = false): Builder
    {
        if (empty($values)) {
            return $this->query;
        }

        if (! is_array($values)) {
            $values = [$values];
        }

        $values = collect($values)
            ->filter() // TODO: filter 'null' strings
            ->toArray();

        $table = $this->query->getModel()->getTable();

        if (count($values)) {
            parent::whereIn("$table.$column", $values, $boolean, $not);
        }

        return $this->query;
    }

    public function name($name): Builder
    {
        collect($name)
            ->each(fn ($value) => $this->where('name', 'like', "%$value%"));

        return $this->query;
    }

    public function nullable(string $column, $value): Builder
    {
        $value
            ? $this->whereNotNull($column)
            : $this->whereNull($column);

        return $this->query;
    }

    public function boolean(string $column, $value, ?Builder $customBuilder = null): Builder
    {
        $builder = $customBuilder ?? $this;

        $value
            ? $builder->where(fn (Builder $q) => $q->where($column, '!=', 0)->orWhere($column, '!=', false))
            : $builder->where(fn (Builder $q) => $q->where($column, 0)->orWhere($column, false)->orWhereNull($column));

        return $this->query;
    }

    private function canFilterByConstant(string $column): bool
    {
        return ! method_exists($this, Str::camel($column))
            && isset($this->input[$column]);
    }
}
