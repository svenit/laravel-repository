<?php

namespace VyDev\Repositories\Cacheable;

use Illuminate\Support\Facades\Cache;

class CacheableRepository
{
    private $data;
    private $cacheKey = null;

    public function __construct($data, $cacheKey)
    {
        $this->data = $data;
        $this->cacheKey = $cacheKey;
    }

    public function remember()
    {
        return Cache::remember($this->cacheKey, 100, function () {
            return $this->data;
        });;
    }
}