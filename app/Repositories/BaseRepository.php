<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

abstract class BaseRepository
{
    abstract protected function getModel(): Model;

    /**
     * 특정 ID로 모델 조회
     * @param int $id
     * @return object|Model|null
     */
    public function findById(int $id): ?Model
    {
        return $this->getModel()->find($id);
    }

    /**
     * 특정 ID로 모델 조회, 없으면 예외 발생
     * @param int $id
     * @return Model
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int $id): Model
    {
        return $this->getModel()->findOrFail($id);
    }

    /**
     * 모든 모델 조회
     * @return Collection
     */
    public function all(): Collection
    {
        return $this->getModel()->all();
    }

    /**
     * 모델 생성
     * @param array $data
     * @return Model
     */
    public function create(array $data): Model
    {
        return $this->getModel()->create($data);
    }

    /**
     * 모델 업데이트
     * @param Model $model
     * @param array $data
     * @return bool
     */
    public function update(Model $model, array $data): bool
    {
        return $model->update($data);
    }

    /**
     * 모델 삭제
     * @param Model $model
     * @return bool
     */
    public function delete(Model $model): bool
    {
        return $model->delete();
    }
}