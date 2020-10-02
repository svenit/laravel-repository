<?php

namespace VyDev\Repositories\Events;

use VyDev\Repositories\Events\BaseEvent;


class EntityDeleted extends BaseEvent
{
    protected $action = 'deleted';
}