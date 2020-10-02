<?php

namespace VyDev\Repositories\Contracts;

interface FormatterInterface
{
    public static function apply($data, $method);
    public static function formatItem($item);
    public static function formatCommon();
    public static function formatPaginate();
}