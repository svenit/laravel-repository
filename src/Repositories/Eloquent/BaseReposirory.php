<?php

namespace VyDev\Repositories\Eloquent;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use VyDev\Repositories\Criteria\Criteria;
use Illuminate\Container\Container as App;
use VyDev\Repositories\Criteria\AdvancedSearchCriteria;
use VyDev\Repositories\Contracts\CriteriaInterface;
use VyDev\Repositories\Contracts\TransformInterface;
use VyDev\Repositories\Contracts\RepositoryInterface;
use VyDev\Repositories\Exceptions\RepositoryException;

/**
 * @author : Lê Quang Vỹ
 * @issues : https://github.com/Juniorsz/easy-repository/issues
 */
abstract class BaseRepository implements RepositoryInterface,CriteriaInterface,TransformInterface 
{

    private $model;

    protected $globalCriteria;
    protected $criteria;
    protected $transform;
    protected $skipCriteria;
    protected $storeKeys = [];
    protected $config;

    public function __construct()
    {
        $this->criteria = new Collection();
        $this->globalCriteria = new Collection();
        $this->config = (OBJECT)[
            'cache' => config('repositories.cache.clear'),
            'pagination' => config('repositories.pagination'),
            'parameters' => config('repositories.parameters')
        ];
        $this->initialize();
        $this->makeModel();
        $this->boot();
        $this->model = $this->filterAttributes($this->model);
        
    }
    public abstract function model();
    public abstract function boot();
    public abstract function transform(RepositoryInterface $repository);

    public function initialize()
    {
        $this->skipCriteria = false;
    }
    
    public function makeModel()
    {
        $model = app()->make($this->model());
        if(!$model instanceof Model)
        {
            throw new RepositoryException("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }
        return $this->model = $model;
    }

    public function applyCriteria()
    {
        if($this->skipCriteria)
        {
            $this->reset();
        }
        else
        {
            $this->mappingCriteria($this->globalCriteria);
            $this->mappingCriteria($this->criteria);
        }
        return $this;
    }

    public function mappingCriteria($criterias)
    {
        $criterias->each(function($criteria){
            if($criteria instanceof Criteria)
            {
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
        $this->model = $criteria->apply($this->model,$this);
        return $this;
    }

    public function reset()
    {
        $this->criteria = new Collection;
        $this->makeModel();
    }

    public function pushCriteria(Criteria $criteria)
    {
        if($criteria instanceof Criteria)
        {
            $this->globalCriteria->push($criteria);
        }
        return $this;
    } 

    public function pushManyCriterias(...$criterias)
    {
        foreach($criterias as $criteria)
        {
            $this->pushCriteria($criteria);
        }
        return $this;
    }

    public function pushCriteriaWhen(array $arguments)
    {
        foreach($arguments as $condition => $criteria)
        {
            if($condition && $criteria instanceof Criteria)
            {
                $this->pushCriteria($criteria);
            }
        }
    }
    public function popCriteria(Criteria $criteria)
    {
        $this->globalCriteria = $this->globalCriteria->reject(function($item) use ($criteria){
            return $item == $criteria;
        });
    }

    public function popCriteriaWhen(array $arguments)
    {
        foreach($arguments as $condition => $criteria)
        {
            if($condition && $criteria instanceof Criteria)
            {
                $this->popCriteria($criteria);
            }
        }
    }

    public function popManyCriterias(...$criterias)
    {
        foreach($criterias as $criteria)
        {
            $this->removeCriteria($criteria);
        }
    }

    public function setTransform()
    {
        if($this->transform)
        {
            if(isset($this->model[0]))
            {
                $this->model->map(function($item,$key){
                    collect($this->transform($item))->map(function($trans,$index) use ($key){
                        if(array_key_exists('visible',$trans))
                        {
                            if($trans['visible'])
                            {
                                unset($this->model[$key][$trans['field']]);
                                return $this->model[$key][$index] = $trans['value'];
                            }
                        }
                        else
                        {
                            unset($this->model[$key][$trans['field']]);
                            return $this->model[$key][$index] = $trans['value'];
                        }
                    });
                });
            }
            else
            {
                collect($this->transform($this->model))->map(function($trans,$index){
                    if(array_key_exists('visible',$trans))
                    {
                        if($trans['visible'])
                        {
                            unset($this->model[$trans['field']]);
                            return $this->model[$index] = $trans['value'];
                        }
                    }
                    else
                    {
                        unset($this->model[$trans['field']]);
                        return $this->model[$index] = $trans['value'];
                    }
                });
            }
            return $this;
        }
    }

    public function all($columns = '*')
    {
        $this->applyCriteria();
        if($this->model instanceof Builder)
        {
            $this->model = $this->model->get($columns);
        }
        else
        {
            $this->model = $this->model->all();
        }
        return $this;
    }

    public function get($columns = '*')
    {
        $this->applyCriteria();
        $this->model = $this->model->get($columns);
        return $this;
    }
    public function first()
    {
        $this->applyCriteria();
        $this->model = $this->model->first();
        return $this;
    }

    public function lastest()
    {
        $this->applyCriteria();
        $this->model = $this->model->lastest();
        return $this;
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
        $this->model = $this->model->find($id);
        return $this;
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
        return $this->model->attach(Arr::wrap($attributes));
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
        if(config('repositories.cache.clear.created'))
        {
            $this->flushCaches();
        }
        return $this->model;
    }

    public function firstOrCreate(array $attributes)
    {
        $this->applyCriteria();
        $this->model->firstOrCreate($attributes);
        if(config('repositories.cache.clear.created'))
        {
            $this->flushCaches();
        }
        return $this->model;
    }

    public function limit($limit = 15)
    {
        $this->applyCriteria();
        $this->model = $this->model->limit($limit);
        return $this;
    }

    public function take($take = 15)
    {
        $this->applyCriteria();
        $this->model = $this->model->take($take);
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
        $this->model = $this->model->paginate($limit ?? $this->config->pagination['limit'], $columns);
        return $this;
    }

    public function where($field,$operator, $value = null)
    {
        $this->applyCriteria();
        $this->model = $this->model->where($field,$operator,$value);
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

    public function when($field,...$closure)
    {
        $this->applyCriteria();
        $this->model = $this->model->when($field, $closure);
        return $this;
    }
    
    public function whereIn($field,$values)
    {
        $this->applyCriteria();
        $this->model = $this->model->whereIn($field,Arr::wrap($values));
        return $this;
    }

    public function whereNotIn($field,$values)
    {
        $this->applyCriteria();
        $this->model = $this->model->whereNotIn($field,Arr::wrap($values));
        return $this;
    }

    public function whereBetween($field,$values)
    {
        $this->applyCriteria();
        $this->model = $this->model->whereBetween($field,Arr::wrap($values));
        return $this;
    }

    public function create($values)
    {
        $this->model->create($values);
        if($this->config->cache['created'])
        {
            $this->flushCaches();
        }
        return $this->model;
    }

    public function update($values)
    {
        $this->model->update($values);
        if($this->config->cache['updated'])
        {
            $this->flushCaches();
        }
        return $this->model;
    }

    public function delete()
    {
        $this->model->delete();
        if($this->config->cache['deleted'])
        {
            $this->flushCaches();
        }
        return $this->model;
    }

    public function replicate()
    {
        $this->model->delete();
        if($this->config->cache['created'])
        {
            $this->flushCaches();
        }
        return $this->model;
    }

    public function updateOrCreate(array $attributes, array $values = [])
    {
        $this->model->updateOrCreate($attributes, $values);
        if($this->config->cache['created'] || $this->config->cache['updated'])
        {
            $this->flushCaches();
        }
        return $this->model;
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
    
    public function relation($relation)
    {
        $this->applyCriteria();
        $this->model = $this->model->{$relation}()->get();
        return $this;
    }

    public function search($fields,$value)
    {
        $this->where(function (Builder $query) use ($fields, $value) 
        {
            foreach(Arr::wrap($fields) as $attribute) 
            {
                $query->when(
                    Str::contains($attribute, '.'),
                    function (Builder $query) use ($attribute, $value) 
                    {
                        [$relationName, $relationAttribute] = explode('.', $attribute);
    
                        $query->orWhereHas($relationName, function (Builder $query) use ($relationAttribute, $value) 
                        {
                            $query->where($relationAttribute, 'LIKE', "%{$value}%");
                        });
                    },
                    function (Builder $query) use ($attribute, $value) 
                    {
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

    public function filterAttributes($model)
    {
        $searchCriteria = new AdvancedSearchCriteria();
        $filter = app()->request->{$this->config->parameters['filter']};

        $findSearchCriteria = collect($this->globalCriteria)->filter(function($item) use ($searchCriteria){
            return get_class($item) == get_class($searchCriteria);
        });

        return $searchCriteria->filterAttributes($model,$findSearchCriteria);
        
    }

    public function export()
    {
        $this->setTransform();
        $result = $this->model;
        $this->reset();
        return $result;
    }

    public function exportWithCache($cacheKeys,$time = null)
    {
        $this->storeKeys[] = $cacheKeys;

        $this->setTransform();
        $result = $this->model;
        $this->reset();

        if(is_int($time))
        {
            return Cache::remember("{$this->model()}.$cacheKeys", $time, function () use($result) {
                return $result;
            });
        }
        else
        {
            return Cache::rememberForever("{$this->model()}.$cacheKeys", function () use($result) {
                return $result;
            });
        }
    }
    public function getCache($cacheKey)
    {
        return Cache::get("{$this->model()}.$cacheKey");
    }
    /**
     * Remove a specific cache key
     * @param : $cacheKeys
     */
    public function forgetCache($cacheKeys)
    {
        $this->storeKeys = array_diff($this->storeKeys,[$cacheKeys]);
        return Cache::forget("{$this->model()}.$cacheKeys");
    }

    /**
     * Flush all cache
     */
    public function flushCaches()
    {
        Cache::flush();
    }
    
    /**
     * Call default Model methods when user trigger undefined method in repository
     */
    public static function __callStatic($method, $arguments)
    {
        return call_user_func_array([new static(), $method], $arguments);
    }

    public function __call($method, $arguments)
    {
        $this->applyCriteria();
        return call_user_func_array([$this->model, $method], $arguments);
    }

}
