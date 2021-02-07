<?php

namespace VyDev\Repositories\Formatter;

use Countable;
use Illuminate\Contracts\Pagination\Paginator;
use VyDev\Repositories\Contracts\FormatterInterface;
use VyDev\Repositories\Exceptions\RepositoryException;

trait BaseFormatter
{
    static $data;
    static $method;

    public static function apply($data, $method)
    {
        static::$data = $data;
        static::$method = $method;
        if ($data instanceof Countable) {
            if ($data instanceof Paginator) {
                return static::formatPaginate();
            }
            return static::formatCommon();
        }
        return static::formatItem($data);
    }

    public static function formatItem($item)
    {
        return static::{static::$method}($item);
    }

    public static function formatCommon()
    {
        return static::$data->map(function ($item, $key) {
            return $item = static::formatItem($item);
        })->filter(function ($item) {
            return isset($item);
        })->values();
    }

    public static function formatPaginate()
    {
        return array_merge(static::$data->toArray(), [
            'data' => static::formatCommon()
        ]);
    }

    public function __call($method, $arguments)
    {
        return call_user_func_array([
            new static,
            $method
        ], $arguments);
    }

}