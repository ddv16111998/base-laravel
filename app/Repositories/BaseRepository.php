<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use App\Repositories\Contracts\BaseRepositoryInterface;
/**
 * Class BaseRepository.
 */
abstract class BaseRepository implements BaseRepositoryInterface
{
    protected $pageName = 'page';

    protected $pageNo = 1;
    /**
     * @var Model
     */
    protected $model;

    /**
     * The query builder.
     */
    protected $query;

    /**
     * Array of one or more where clause parameters.
     *
     * @var array
     */
    protected $wheres = [];

    /**
     * Array of related models to eager load.
     *
     * @var array
     */
    protected $relations = [];

    /**
     * Selected column list.
     *
     * @var array|null
     */
    protected $selectedColumns = [];

    /**
     * Array of one or more where in clause parameters.
     *
     * @var array
     */
    protected $whereIns = [];

    /**
     * Array of one or more where in clause parameters.
     *
     * @var array
     */
    protected $whereNotIns = [];

    protected $whereBetween = [];
    protected $whereNotBetween = [];

    /**
     * Array of one or more ORDER BY column/value pairs.
     *
     * @var array
     */
    protected $orderBys = [];

    /**
     * Alias for the query limit.
     *
     * @var int
     */
    protected $take;

    /**
     * Array of scope methods to call on the model.
     *
     * @var array
     */
    protected $scopes = [];

    /**
     * BaseRepository constructor.
     *
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * @inheritDoc
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    public function setModel(Model $model)
    {
        $this->model = $model;
    }

    protected function newQuery(): self
    {
        $this->query = $this->model->newQuery();

        return $this;
    }

    /**
     * Add relationships to the query builder to eager load.
     */
    protected function eagerLoad(): self
    {
        $this->query->with($this->relations);

        return $this;
    }

    /**
     * Set selected columns to the query builder.
     *
     * @return BaseRepository
     */
    protected function setSelect(): self
    {
        if ($this->selectedColumns) {
            $this->query->select($this->selectedColumns);
        }

        return $this;
    }

    /**
     * Reset the query clause parameter arrays.
     */
    protected function unsetClauses(): self
    {
        $this->wheres = [];
        $this->whereIns = [];
        $this->whereNotIns = [];
        $this->whereBetween = [];
        $this->whereNotBetween = [];
        $this->scopes = [];
        $this->relations = [];
        $this->take = null;

        return $this;
    }

    /**
     * Reset the selected column list.
     */
    protected function unsetSelect(): self
    {
        $this->selectedColumns = null;

        return $this;
    }

    public function all(): array
    {
        $this->newQuery()->eagerLoad()->setSelect();
        $result = $this->query->get();
        $this->unsetClauses()->unsetSelect();

        return $result;
    }

    public function get(): array
    {
        $this->newQuery()->eagerLoad()->setSelect()->setClauses()->loadScopes();
        $result = $this->query->get();
        $this->unsetClauses()->unsetSelect();

        return $result;
    }

    /**
     * @param array $columns
     * @return BaseRepositoryInterface
     */
    protected function select(array $columns = ['*']): BaseRepositoryInterface
    {
        $this->selectedColumns = $columns;

        return $this;
    }

    public function loadScopes(): self
    {
        foreach ($this->scopes as $method => $args) {
            $scopeMethod = Str::camel($method);
            if ($this->model->hasNamedScope($scopeMethod)) {
                $this->query->{$scopeMethod}($args);
            }
        }

        return $this;
    }

    public function setScopes(array $scopes = []): BaseRepositoryInterface
    {
        $this->scopes = $scopes;

        return $this;
    }

    /**
     * Set clauses on the query builder.
     *
     * @return BaseRepository
     */
    protected function setClauses(): self
    {
        foreach ($this->wheres as $where) {
            $this->query->where($where['column'], $where['operator'], $where['value']);
        }

        foreach ($this->whereIns as $whereIn) {
            $this->query->whereIn($whereIn['column'], $whereIn['values']);
        }

        foreach ($this->whereNotIns as $whereIn) {
            $this->query->whereNotIn($whereIn['column'], $whereIn['values']);
        }

        foreach ($this->whereBetween as $whereBetween) {
            $this->query->whereBetween($whereBetween['column'], $whereBetween['values']);
        }

        foreach ($this->whereNotBetween as $whereNotBetween) {
            $this->query->whereNotBetween($whereNotBetween['column'], $whereNotBetween['values']);
        }

        foreach ($this->orderBys as $orderBy) {
            $this->query->orderBy($orderBy['column'], $orderBy['direction']);
        }

        if (isset($this->take)) {
            $this->query->take($this->take);
        }

        return $this;
    }

    /**
     * @param int $limit
     * @return BaseRepository
     */
    public function limit(int $limit): self
    {
        $this->take = $limit;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return $this->get()->count();
    }

    /**
     * @inheritDoc
     */
    public function with(array $relations = []): BaseRepositoryInterface
    {
        if (is_string($relations)) {
            $relations = func_get_args();
        }

        $this->relations = $relations;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function find($id): ?Model
    {
        $this->unsetSelect()->unsetClauses();

        $this->newQuery()->eagerLoad()->setSelect();

        return $this->query->find($id);
    }

    /**
     * @inheritDoc
     */
    public function findById($id): Model
    {
        $this->unsetSelect()->unsetClauses();

        $this->newQuery()->eagerLoad()->setSelect();

        return $this->query->findOrFail($id);
    }

    /**
     * @inheritDoc
     */
    public function findByColumn($item, $column): ?Model
    {
        $this->unsetSelect()->unsetClauses();

        $this->newQuery()->eagerLoad()->setSelect();

        return $this->query->where($column, $item)->first();
    }

    public function advancedPaginate($sorts = [], $page = 1, $limit = null, $pageName = null): LengthAwarePaginator
    {
        if ( ! empty($sorts)) {
            $this->orderBy($sorts);
        } else {
            $this->orderBy('id', 'desc');
        }

        if ( ! $limit) {
            $limit = config('pagination.per_page_number');
        }

        $this->setPageName($pageName ?? config('pagination.page_name'));

        return $this->setPageNo($page)->paginate($limit);
    }

    /**
     * @param string $pageName
     * @return BaseRepositoryInterface
     */
    protected function setPageName(string $pageName): BaseRepositoryInterface
    {
        $this->pageName = $pageName;

        return $this;
    }

    /**
     * @param int|null $pageNo
     * @return BaseRepositoryInterface
     */
    protected function setPageNo(?int $pageNo): BaseRepositoryInterface
    {
        $this->pageNo = $pageNo;

        return $this;
    }

    /**
     * @param int $limit
     * @return LengthAwarePaginator
     */
    protected function paginate(int $limit = 25): LengthAwarePaginator
    {
        $this->newQuery()->eagerLoad()->setSelect()->setClauses()->loadScopes();

        $paginator = $this->query->paginate($limit, $this->selectedColumns, $this->pageName, $this->pageNo);

        $this->unsetClauses()->unsetPaginator();

        return $paginator;
    }

    /**
     * Reset the query clause parameter arrays.
     *
     * @return BaseRepository
     */
    protected function unsetPaginator(): self
    {
        $this->pageName = null;
        $this->pageNo = null;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function create(array $attributes): Model
    {
        return $this->model->create($attributes);
    }

    /**
     * @inheritDoc
     */
    public function update(Model $model, array $attributes): Model
    {
        $model->update($attributes);
        $model->refresh();

        return $model;
    }

    /**
     * @inheritDoc
     */
    public function updateById($id, array $attributes): Model
    {
        $model = $this->findById($id);
        $this->update($model, $attributes);

        return $model;
    }

    /**
     * @inheritDoc
     */
    public function delete(Model $model): void
    {
        $model->delete();
    }

    /**
     * @inheritDoc
     */
    public function deleteById($id): void
    {
        $this->delete($this->findById($id));
    }

    public function first(): ?Model
    {
        $this->newQuery()->eagerLoad()->setSelect()->setClauses()->loadScopes();

        $model = $this->query->first();

        $this->unsetClauses()->unsetSelect();

        return $model;
    }

    /**
     * @param $column
     */
    public function sum($column)
    {
        $this->newQuery()->eagerLoad()->setSelect()->setClauses()->loadScopes();

        $sum = $this->query->sum($column);

        $this->unsetClauses()->unsetSelect();

        return $sum;
    }

    /**
     * @param $column
     * @param string $direction
     * @return BaseRepositoryInterface
     */
    public function orderBy($column, string $direction = 'asc'): BaseRepositoryInterface
    {
        if (func_num_args() == 1) {
            $orderBys = func_get_arg(0);
            foreach ($orderBys as $column => $direction) {
                $this->orderBys[] = compact('column', 'direction');
            }

            return $this;
        }

        $this->orderBys[] = compact('column', 'direction');

        return $this;
    }

    /**
     * @param $column
     * @param $value
     * @param string $operator
     * @return BaseRepositoryInterface
     */
    public function where($column, $value, string $operator = '='): BaseRepositoryInterface
    {
        $this->wheres[] = compact('column', 'value', 'operator');

        return $this;
    }

    /**
     * @param $column
     * @param $values
     * @return BaseRepositoryInterface
     */
    public function whereIn($column, $values): BaseRepositoryInterface
    {
        $values = is_array($values) ? $values : [$values];

        $this->whereIns[] = compact('column', 'values');

        return $this;
    }

    /**
     * @param $column
     * @param $values
     * @return BaseRepositoryInterface
     */
    public function whereNotIn($column, $values): BaseRepositoryInterface
    {
        $values = is_array($values) ? $values : [$values];

        $this->whereNotIns[] = compact('column', 'values');

        return $this;
    }

    public function whereBetween($column, $values): BaseRepositoryInterface
    {
        $values = is_array($values) ? $values : [$values];
        $this->whereBetween[] = compact('column', 'values');
        return $this;
    }

    public function whereNotBetween($column, $values): BaseRepositoryInterface
    {
        $values = is_array($values) ? $values : [$values];
        $this->whereNotBetween[] = compact('column', 'values');
        return $this;
    }

    /**
     * Add relationships to the query builder to eager load.
     *
     * @return BaseRepository
     */
    protected function withTrashed(): self
    {
        $this->query->withTrashed();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toArray($key, $column, $scope = null): array
    {
        if ($scope) {
            $this->scopes[$scope] = null;
        }

        return $this->get()->pluck($column, $key)->toArray();
    }

    /**
     * @inheritDoc
     */
    public function toArrayWithNone($key, $column, $scope = null): array
    {
        $list = $this->toArray($key, $column, $scope);

        return array_merge([
            0 => 'None',
        ], $list);
    }
}
