<?php

namespace VyDev\Repositories\Cacheable;

use Illuminate\Support\Facades\Storage;
use VyDev\Repositories\Cacheable\CacheableRepository;

trait HandleCache
{
    private $storeFile = 'cache/vydev';

    public function cacheMapping()
    {
        return [
            'first' => 'firstByCache',
            'get' => 'getByCache',
            'all' => 'allByCache',
            'paginate' => 'paginateByCache'
        ];
    }

    public function resolveCache()
    {
        extract($this->cacheBinding);
        $getBuilderFunction = array_flip($this->cacheMapping())[$method];
        $modelData = $this->{$getBuilderFunction}($arguments);
        $cacheRepository = new CacheableRepository($modelData, $this->generateKey());
        $this->storeKey();
        return $cacheRepository->remember();
    }

    public function generateKey()
    {
        $request = app('Illuminate\Http\Request');
        $args = serialize($this->cacheBinding['arguments']);
        $criteria = $this->getCriteriaSerialize();
        $cacheKey = sprintf('%s@%s-%s', $this->encodeClass(), $this->cacheBinding['method'], md5($args . $criteria . $request->fullUrl()));
        return $cacheKey;
    }

    public function storeKey()
    {
        if (!$this->loadKey()) {
            $file = Storage::get($this->getFileKey());
            $keyFile = json_decode($file, true);
            $keyFile[$this->generateKey()] = true;
            $keyFile = json_encode($keyFile);
            return Storage::put($this->getFileKey(), $keyFile);
        }
    }

    public function loadKey()
    {
        if (Storage::exists($this->getFileKey())) {
            $keyFile = json_decode(Storage::get($this->getFileKey()), true);
            return isset($keyFile[$this->generateKey()]);
        }
        Storage::put($this->getFileKey(), '');
        return false;
    }

    public function getFileKey()
    {
        $fileName = $this->encodeClass().'.json';
        return $this->storeFile.'/'.$fileName;
    }

    public function encodeClass()
    {
        return str_replace('\\', '_', get_called_class());
    }

}