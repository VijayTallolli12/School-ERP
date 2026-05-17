<?php

namespace App\Core\Base;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository
{
    public function __construct(protected Model $model) {}

    public function query(): Builder
    {
        return $this->model->newQuery();
    }

    public function findOrFail(int|string $id): Model
    {
        return $this->query()->findOrFail($id);
    }

    public function create(array $data): Model
    {
        return $this->query()->create($data);
    }

    public function update(Model $model, array $data): Model
    {
        $model->fill($data)->save();

        return $model->refresh();
    }
}
