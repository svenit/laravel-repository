<?php

namespace VyDev\Repositories\Eloquent;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use VyDev\Repositories\Criteria\Criteria;
use Illuminate\Container\Container as App;
use VyDev\Repositories\Cacheable\HandleCache;
use VyDev\Repositories\Contracts\CriteriaInterface;
use VyDev\Repositories\Contracts\RepositoryInterface;
use VyDev\Repositories\Exceptions\RepositoryException;

/**
 * @author : Lê Quang Vỹ
 */
abstract class BaseRepository implements RepositoryInterface, CriteriaInterface
{

    use HandleCache;

    private $model;
    private $builderString = '';

    protected $globalCriteria;
    protected $criteria;
    protected $skipCriteria;
    protected $config;
    protected $cacheBinding = [];

    public function __construct()
    {
        $this->initialize();
        $this->makeModel();
        $this->boot();
    }

    public abstract function model();
    public abstract function boot();

    public function initialize()
    {
        $this->criteria = new Collection();
        $this->globalCriteria = new Collection();
        $this->config = (Object) config('repositories');
        $this->skipCriteria = false;
    }

    public function makeModel()
    {
        $model = app()->make($this->model());
        if (!$model instanceof Model) {
            throw new RepositoryException("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }
        return $this->model = $model;
    }

    public function applyCriteria()
    {
        if($this->skipCriteria) {
            $this->reset();
        } else {
            $this->mappingCriteria($this->globalCriteria);
            $this->mappingCriteria($this->criteria);
        }
        return $this;
    }

    public function mappingCriteria($criterias)
    {
        $criterias->each(function($criteria){
            if ($criteria instanceof Criteria) {
                $this->model = $criteria->apply($this->model,$this);
            }
        });
    }

    public function skipCriteria()
    {
        $this->skipCriteria = true;
    }

    public function getByCriteria(Criteria $criteria)
    {
        $this->reset();
        $this->model = $criteria->apply($this->model, $this);
        return $this;
    }

    public function reset()
    {
        $this->makeModel();
        $this->criteria = new Collection;
    }

    public function pushCriteria(Criteria $criteria)
    {
        if ($criteria instanceof Criteria) {
            $this->globalCriteria->push($criteria);
        }
        return $this;
    }

    public function pushManyCriterias(Criteria ...$criterias)
    {
        foreach ($criterias as $criteria) {
            $this->pushCriteria($criteria);
        }
        return $this;
    }

    public function pushCriteriaWhen(array $arguments)
    {
        foreach ($arguments as $condition => $criteria) {
            if ($condition && $criteria instanceof Criteria) {
                $this->pushCriteria($criteria);
            }
        }
    }
    public function popCriteria(Criteria $criteria)
    {
        $this->globalCriteria = $this->globalCriteria->reject(function($item) use ($criteria) {
            return $item == $criteria;
        });
    }

    public function popCriteriaWhen(array $arguments)
    {
        foreach ($arguments as $condition => $criteria) {
            if ($condition && $criteria instanceof Criteria) {
                $this->popCriteria($criteria);
            }
        }
    }

    public function popManyCriterias(Criteria ...$criterias)
    {
        foreach ($criterias as $criteria) {
            $this->removeCriteria($criteria);
        }
    }

    public function getCriteriaSerialize()
    {
        return $this->criteria . '-' . $this->globalCriteria;
    }

    public function __get($attribute)
    {
        return $this->getAttribute($attribute);
    }

    public function __set($attribute, $value)
    {
        return $this->setAttribute($attribute, $value);
    }

    public function getAttribute($attribute)
    {
        return $this->model->{$attribute};
    }

    public function setAttribute($attribute, $value)
    {
        $this->model->{$attribute} = $value;
    }

    public function exists()
    {
        $result = $this->model->exists();
        $this->reset();
        return $result;
    }

    public function cursor()
    {
        $this->applyCriteria();
        $this->model = $this->model->cursor();
        return $this;
    }

    public function all($columns = '*')
    {
        $this->applyCriteria();
        if ($this->model instanceof Builder) {
            $result = $this->model->get($columns);
        } else {
            $result = $this->model->all();
        }
        $this->reset();
        return $result;
    }

    public function get($columns = '*')
    {
        $this->applyCriteria();
        $result = $this->model->get($columns);
        $this->reset();
        return $result;
    }
    public function first()
    {
        $this->applyCriteria();
        $result = $this->model->first();
        $this->reset();
        return $result;
    }

    public function latest()
    {
        $this->applyCriteria();
        $result = $this->model->latest();
        $this->reset();
        return $result;
    }

    public function random()
    {
        $this->applyCriteria();
        $this->model = $this->model->inRandomOrder();
        return $this;
    }

    public function find($id)
    {
        $this->applyCriteria();
        $result = $this->model->find($id);
        $this->reset();
        return $result;
    }

    public function findOrFail($id)
    {
        $this->applyCriteria();
        $result = $this->model->findOrFail($id);
        $this->reset();
        return $result;
    }

    public function findOrNew($id, $columns = ['*'])
    {
        return $this->model->findOrNew($id, $columns = ['*']);
    }

    public function pluck($column, $key = null)
    {
        $this->applyCriteria();
        $this->model = $this->model->pluck($column, $key);
        return $this;
    }

    public function sync($attributes)
    {
        return $this->model->sync(Arr::wrap($attributes));
    }

    public function syncWithoutDetaching($attributes)
    {
        return $this->model->syncWithoutDetaching(Arr::wrap($attributes));
    }

    public function attach($attributes)
    {
        $model = $this->model->attach(Arr::wrap($attributes));
        return $model;
    }

    public function detach($attributes)
    {
        return $this->model->detach(Arr::wrap($attributes));
    }

    public function count()
    {
        $this->applyCriteria();
        return $this->model->count();
    }

    public function firstOrNew(array $attributes)
    {
        $this->applyCriteria();
        $this->model->firstOrNew($attributes);
        return $this->model;
    }

    public function firstOrCreate(array $attributes)
    {
        $this->applyCriteria();
        $this->model->firstOrCreate($attributes);
        return $this->model;
    }

    public function limit($arg = 15)
    {
        $this->applyCriteria();
        $this->model = $this->model->limit($arg);
        return $this;
    }

    public function take($arg = 15)
    {
        $this->applyCriteria();
        $this->model = $this->model->take($arg);
        return $this;
    }

    public function offset($arg = 15)
    {
        $this->applyCriteria();
        $this->model = $this->model->offset($arg);
        return $this;
    }

    public function skip($skip)
    {
        $this->applyCriteria();
        $this->model = $this->model->skip($skip);
        return $this;
    }

    public function paginate($limit = null, $columns = '*')
    {
        $this->applyCriteria();
        $result = $this->model->paginate($limit ?? $this->config->pagination['limit'], $columns);
        $this->reset();
        return $result;
    }

    public function where($field, $operator = null, $value = null)
    {
        $this->applyCriteria();
        $this->model = $this->model->where($field, $operator, $value);
        return $this;
    }

    public function whereNotNull($field)
    {
        $this->applyCriteria();
        $this->model = $this->model->whereNotNull($field);
        return $this;
    }

    public function whereNull($field)
    {
        $this->applyCriteria();
        $this->model = $this->model->whereNull($field);
        return $this;
    }

    public function when($field, ...$closure)
    {
        $this->applyCriteria();
        $this->model = $this->model->when($field, $closure);
        return $this;
    }

    public function whereIn($field, $values)
    {
        $this->applyCriteria();
        $this->model = $this->model->whereIn($field, Arr::wrap($values));
        return $this;
    }

    public function whereNotIn($field, $values)
    {
        $this->applyCriteria();
        $this->model = $this->model->whereNotIn($field, Arr::wrap($values));
        return $this;
    }

    public function whereBetween($field, $values)
    {
        $this->applyCriteria();
        $this->model = $this->model->whereBetween($field, Arr::wrap($values));
        return $this;
    }

    public function create($values)
    {
        $model = $this->model->create($values);
        return $model;
    }

    public function save()
    {
        return $this->model->save();
    }

    public function update($values)
    {
        $model = $this->model->update($values);
        return $model;
    }

    public function delete()
    {
        $model = $this->model->delete();
        return $model;
    }

    public function replicate()
    {
        $model = $this->model->replicate();
        return $model;
    }

    public function updateOrCreate(array $attributes, array $values = [])
    {
        $model = $this->model->updateOrCreate($attributes, $values);
        return $model;
    }

    public function has($relation)
    {
        $this->applyCriteria();
        $this->model = $this->model->has($relation);
        return $this;
    }

    public function with($relations)
    {
        $this->applyCriteria();
        $this->model = $this->model->with($relations);
        return $this;
    }

    public function withCount($relations)
    {
        $this->applyCriteria();
        $this->model = $this->model->withCount($relations);
        return $this;
    }

    public function loadCount($relations)
    {
        $this->applyCriteria();
        $this->model = $this->model->loadCount($relations);
        return $this;
    }

    public function whereHas($relation, $closure)
    {
        $this->model = $this->model->whereHas($relation, $closure);
        return $this;
    }

    public function orWhereHas($relation, $closure)
    {
        $this->model = $this->model->orWhereHas($relation, $closure);
        return $this;
    }

    public function orderBy($column, $direction = 'asc')
    {
        $this->model = $this->model->orderBy($column, $direction);
        return $this;
    }

    public function orderByDesc($field)
    {
        $this->model = $this->model->orderByDesc($field);
        return $this;
    }

    public function orderByAsc($field)
    {
        $this->model = $this->model->orderByAsc($field);
        return $this;
    }

    public function load($relation)
    {
        $this->applyCriteria();
        $this->model = $this->model->load($relation);
        return $this;
    }

    public function search($fields,$value)
    {
        $this->where(function (Builder $query) use ($fields, $value) {
            foreach (Arr::wrap($fields) as $attribute) {
                $query->when(
                    Str::contains($attribute, '.'),
                    function (Builder $query) use ($attribute, $value) {
                        [$relationName, $relationAttribute] = explode('.', $attribute);
                        $query->orWhereHas($relationName, function (Builder $query) use ($relationAttribute, $value) {
                            $query->where($relationAttribute, 'LIKE', "%{$value}%");
                        });
                    },
                    function (Builder $query) use ($attribute, $value) {
                        $query->orWhere($attribute, 'LIKE', "%{$value}%");
                    }
                );
            }
        });
        return $this;
    }
    public function hidden($columns = ['*'])
    {
        $this->model = $this->model->setHidden(Arr::wrap($columns));
        return $this;
    }

    public function visible($columns = ['*'])
    {
        $this->model = $this->model->setVisible(Arr::wrap($columns));
        return $this;
    }
    public function increment($field, $quantity = null)
    {
        return $this->model->increment($field, $quantity);
    }

    public function decrement($field, $quantity = null)
    {
        return $this->model->decrement($field, $quantity);
    }

    public function __call($method, $arguments)
    {
        $this->applyCriteria();
        if (in_array($method, array_values($this->cacheMapping()))) {
            $this->cacheBinding = [
                'method' => $method,
                'arguments' => $arguments
            ];
            return $this->resolveCache();
        }
        return call_user_func_array([$this->model, $method], $arguments);
    }

    public static function __callStatic($method, $arguments)
    {
        return call_user_func_array([new static(), $method], $arguments);
    }

}
