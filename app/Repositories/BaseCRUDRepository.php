<?php

namespace App\Repositories;

use App\Helper\Trait\RequestLoggerTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BaseCRUDRepository
{
    use RequestLoggerTrait;

    public function __construct(private Model $model) {}

    public function __get($name)
    {
        if ($name == 'table') {
            return $name = $this->model->getTable();
        }
    }

    public function query(array $relations = []): Builder
    {
        return $this->model::query()->with($relations)->latest();
    }

    public function paginate(array $columns = ['*'], array $relations = [], $perPage = 20)
    {
        try {
            return $this->model::query()->with($relations)->latest()->select($columns)->paginate($perPage);
        } catch (\Exception $e) {
            $this->error("Something went wrong in $this->table ".$e->getMessage());
        }
    }

    public function all(array $columns = ['*'], array $relations = [])
    {
        return $this->model::with($relations)->latest()->get($columns);
    }

    public function store($request)
    {
        try {
            return $this->model::create($request);
        } catch (\Exception $e) {
            $this->error("Something went wrong in $this->table ".$e->getMessage());

            return false;
        }
    }

    public function show($id, array $relations = [])
    {
        try {
            return $this->model::with($relations)->find($id);
        } catch (\Exception $e) {
            $this->error("Something went wrong in $this->table ".$e->getMessage());

            return false;
        }
    }

    public function update($id, $request)
    {
        try {
            $data = $this->model::find($id);

            if (! $data) {
                return false;
            }

            return $data->update($request);
        } catch (\Exception $e) {
            $this->error("Something went wrong in $this->table ".$e->getMessage());

            return false;
        }
    }

    public function delete($id): bool
    {
        try {
            $model = $this->model::find($id);
            if ($model) {
                $model->delete();

                return true;
            }

            return false;
        } catch (\Exception $e) {
            $this->error("Something went wrong in $this->table ".$e->getMessage());

            return false;
        }
    }
}
