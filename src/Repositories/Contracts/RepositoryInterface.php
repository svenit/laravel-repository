<?php

namespace VyDev\Repositories\Contracts;

interface RepositoryInterface
{
    public function all();
    public function get($columns = ['*']);
    public function first();
    public function latest();
    public function random();
    public function exists();
    public function find($id);
    public function findOrFail($id);
    public function findOrNew($id, $columns = ['*']);
    public function pluck($column, $key = null);
    public function sync($attributes);
    public function syncWithoutDetaching($attributes);
    public function attach($attributes);
    public function detach($attributes);
    public function count();
    public function firstOrNew(array $attributes);
    public function firstOrCreate(array $attributes);
    public function limit($arg = 15);
    public function take($arg = 15);
    public function offset($arg = 15);
    public function paginate($limit = 15, $columns = ['*']);
    public function where($field, $operator = null, $value = null);
    public function whereIn($field,$values);
    public function whereNotIn($field,$values);
    public function whereBetween($field,$values);
    public function create($values);
    public function save();
    public function update($values);
    public function delete();
    public function updateOrCreate(array $attributes, array $values = []);
    public function has($relation);
    public function with($relations);
    public function withCount($relations);
    public function loadCount($relations);
    public function whereHas($relation, $closure);
    public function orderBy($column, $direction = 'asc');
    public function load($relation);
    public function search($fields,$value);
    public function hidden($columns = ['*']);
    public function visible($columns = ['*']);
    public function increment($field, $quantity = null);
    public function decrement($field, $quantity = null);
}