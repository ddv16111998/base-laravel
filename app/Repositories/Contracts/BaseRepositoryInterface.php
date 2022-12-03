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

    public function all(): array;

    public function get(): array;

    public function advancedPaginate(array $sorts = [], int $page = 1, int $limit = 25): LengthAwarePaginator;

    /**
     * @return int
     */
    public function count(): int;

    /**
     * @param array $relations
     * @return $this|BaseRepositoryInterface
     */
    public function with(array $relations = []): self;

    /**
     * @param int $id
     * @return Model|null
     */
    public function find(int $id): ?Model;

    public function first(): ?Model;

    /**
     * @param int $id
     * @return Model
     * @throws ModelNotFoundException
     */
    public function findById(int $id): Model;

    /**
     * @param $item
     * @param string $column
     * @return Model|null
     */
    public function findByColumn($item, string $column): ?Model;

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
     * @param int $id
     * @param array $attributes
     * @return Model
     */
    public function updateById(int $id, array $attributes): Model;

    /**
     * @param Model $model
     */
    public function delete(Model $model): void;

    /**
     * @param int $id
     */
    public function deleteById(int $id): void;

    /**
     * @param string $key
     * @param string $column
     * @param string|null $scope
     * @return array
     */
    public function toArray(string $key, string $column, string $scope = null): array;

    /**
     * @param string $key
     * @param string $column
     * @param string|null $scope
     * @return array
     */
    public function toArrayWithNone(string $key, string $column, string $scope = null): array;

    public function where(string $column, $value, string $operator = '='): self;

    public function whereIn(string $column, array $values): self;

    public function whereNotIn(string $column, array $values): self;

    public function whereBetween(string $column, array $values): self;
    public function whereNotBetween(string $column, array $values): self;

    public function orderBy(string $column, string $direction = 'asc'): self;

    public function setScopes(array $scopes): self;
}
