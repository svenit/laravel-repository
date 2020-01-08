<?php

namespace VyDev\Repositories\Contracts;

interface RepositoryInterface
{
    public function get($columns = ['*']);

    public function first();

    public function export();

    public function hidden($columns = ['*']);

    public function visible($columns = ['*']);
}