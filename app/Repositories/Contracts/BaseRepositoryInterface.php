<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

interface BaseRepositoryInterface
{
    /**
     * @return Model
     */
    public function getModel(): Model;

    public function all();

    public function get();

    public function advancedPaginate($sorts = [], $page = 1, $limit = 25): LengthAwarePaginator;

    /**
     * @return int
     */
    public function count(): int;

    /**
     * @param $relations
     * @return $this|BaseRepositoryInterface
     */
    public function with(array $relations = []): self;

    /**
     * @param $id
     * @return Model|null
     */
    public function find($id);

    public function first();

    /**
     * @param $id
     * @return Model
     * @throws ModelNotFoundException
     */
    public function findById($id): Model;

    /**
     * @param $item
     * @param $column
     * @return Model|null
     */
    public function findByColumn($item, $column): ?Model;

    /**
     * @param array $attributes
     * @return Model
     */
    public function create(array $attributes): Model;

    /**
     * @param Model $model
     * @param array $attributes
     * @return Model
     */
    public function update(Model $model, array $attributes): Model;

    /**
     * @param $id
     * @param array $attributes
     * @return Model
     */
    public function updateById($id, array $attributes): Model;

    /**
     * @param Model $model
     */
    public function delete(Model $model): void;

    /**
     * @param $id
     */
    public function deleteById($id): void;

    /**
     * @param $key
     * @param $column
     * @param null $scope
     * @return array
     */
    public function toArray($key, $column, $scope = null): array;

    /**
     * @param $key
     * @param $column
     * @param null $scope
     * @return array
     */
    public function toArrayWithNone($key, $column, $scope = null): array;

    public function where($column, $value, $operator = '=');

    public function whereIn($column, $values);

    public function whereNotIn($column, $values);

    public function orderBy($column, $direction = 'asc');

    public function setScopes($scopes);
}
