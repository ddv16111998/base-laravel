<?php

namespace App\Repositories\Cache;

use App\Repositories\BaseRepository;
use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use App\Exceptions\AttributeModelNotFoundException;

abstract class BaseCacheRepository implements BaseRepositoryInterface
{
    const TTL = 1440; # 1 day

    protected $cache;

    protected $model;

    protected $repository;

    public function __construct(BaseRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return false|string
     */
    private function getModelClass()
    {
        return get_class($this->model) . '.' . App::getLocale() . '.' . $this->model->getConnection()->getDatabaseName();
    }

    private function genKey($key)
    {
        return $this->getModelClass() . '.' . $key;
    }

    private function getLocalesHelper()
    {
        return app('translatable.locales');
    }

    protected function clearListCache()
    {
        $localeHelper = $this->getLocalesHelper();
        foreach ($localeHelper->all() as $locale) {
            App::setLocale($locale);
            $this->cache->tags($this->genKey('all'))->flush();
            $this->cache->tags($this->genKey('count'))->flush();
            $this->cache->tags($this->genKey('paginate'))->flush();
            $this->cache->tags($this->genKey('toArray'))->flush();
            $this->cache->tags($this->genKey('toArrayWithNone'))->flush();
            $this->cache->tags($this->genKey('column'))->flush();
        }
    }

    protected function clearItemCache($id)
    {
        $localeHelper = $this->getLocalesHelper();
        foreach ($localeHelper->all() as $locale) {
            App::setLocale($locale);
            $this->cache->tags($this->genKey($id))->flush();
        }
    }

    /**
     * @inheritDoc
     */
    public function all(): Collection
    {
        return $this->cache->tags($this->genKey('all'))->remember($this->genKey('all'), self::TTL, function () {
            return $this->repository->all();
        });
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return $this->cache->tags($this->genKey('count'))->remember($this->genKey('count'), self::TTL, function () {
            return $this->repository->count();
        });
    }

    /**
     * @inheritDoc
     */
    public function find($id): ?Model
    {
        return $this->cache->tags($this->genKey($id))->remember($this->genKey($id), self::TTL, function () use ($id) {
            return $this->repository->find($id);
        });
    }

    /**
     * @inheritDoc
     */
    public function findById($id): Model
    {
        return $this->cache->tags($this->genKey($id))->remember($this->genKey($id), self::TTL, function () use ($id) {
            return $this->repository->findById($id);
        });
    }

    /**
     * @inheritDoc
     */
    public function findByColumn($item, $column): ?Model
    {
        return $this->cache->tags($this->genKey('column'))->remember($this->genKey($item . '.' . $column), self::TTL, function () use ($item, $column) {
            return $this->repository->findByColumn($item, $column);
        });
    }

    /**
     * @param array $filters
     * @param array $sorts
     * @param int $page
     * @param int $limit
     * @param null $pageName
     * @return LengthAwarePaginator
     */
    public function advancedPaginate($filters = [], $sorts = [], $page = 1, $limit = null, $pageName = null): LengthAwarePaginator
    {
        if ( ! empty($filters) || ! empty($sorts)) {
            return $this->repository->advancedPaginate($filters, $sorts, $page, $limit, $pageName);
        }

        return $this->cache->tags($this->genKey('paginate'))->remember($this->genKey("paginate.{$page}.{$limit}"), self::TTL, function () use ($filters, $sorts, $page, $limit, $pageName) {
            return $this->repository->advancedPaginate($filters, $sorts, $page, $limit, $pageName);
        });
    }

    /**
     * @inheritDoc
     */
    public function create(array $attributes): Model
    {
        $this->clearListCache();

        return $this->repository->create($attributes);
    }

    /**
     * @inheritDoc
     */
    public function update(Model $model, array $attributes): Model
    {
        $this->clearListCache();
        $this->clearItemCache($model->id);

        return $this->cache->tags($this->genKey($model->id))->remember($this->genKey($model->id), self::TTL, function () use ($model, $attributes) {
            return $this->repository->update($model, $attributes);
        });
    }

    /**
     * @inheritDoc
     */
    public function delete(Model $model): void
    {
        $this->clearListCache();
        $this->clearItemCache($model->id);
        $this->repository->delete($model);
    }

    /**
     * @inheritDoc
     */
    public function toArray($key, $column, $scope = null): array
    {
        return $this->cache->tags($this->genKey("toArray"))->remember($this->genKey("array.{$key}.{$column}.{$scope}"), self::TTL, function () use ($key, $column, $scope) {
            return $this->repository->toArray($key, $column, $scope);
        });
    }

    /**
     * @inheritDoc
     */
    public function toArrayWithNone($key, $column, $scope = null): array
    {
        return $this->cache->tags($this->genKey("toArrayWithNone"))->remember($this->genKey("array.{$key}.{$column}.{$scope}"), self::TTL, function () use ($key, $column, $scope) {
            return $this->repository->toArrayWithNone($key, $column, $scope);
        });
    }

    /**
     * @inheritDoc
     */
    public function with($relations): BaseRepositoryInterface
    {
        return $this->repository->with($relations);
    }

    /**
     * @inheritDoc
     */
    public function getModel(): Model
    {
        return $this->repository->getModel();
    }

    /**
     * @param $model
     * @return mixed|void
     */
    public function setModel($model) {
        $this->repository->setModel($model);
    }

    /**
     * @inheritDoc
     */
    public function deleteById($id): void
    {
        $this->clearListCache();
        $this->clearItemCache($id);

        $this->repository->deleteById($id);
    }

    /**
     * @inheritDoc
     */
    public function updateById($id, array $attributes): Model
    {
        $this->clearListCache();
        $this->clearItemCache($id);

        return $this->repository->updateById($id, $attributes);
    }

    /**
     * @param $model
     * @return array
     * @throws AttributeModelNotFoundException
     */
    public function checkRelationDelete($model) {
        return $this->repository->checkRelationDelete($model);
    }
}
