<?php
namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface IRepository {
  public function all(bool $toArray): Collection|array|null;
  public function findById($id): mixed;
  public function create($attributes = []): mixed;
  public function update($id, array $attributes = []): mixed;
  public function updateWhere(array $attributes, array $params): mixed;
  public function findBy(array $filter, bool $toArray): Collection|array|null;
  public function findOneBy(array $filter): mixed;
  public function with($relationship): mixed;
  public function delete($id): mixed;
  public function getQuery(): Builder|\Illuminate\Database\Query\Builder;
  public function clearQuery(): \Illuminate\Database\Query\Builder;
  public function paginate($page): LengthAwarePaginator;
}
