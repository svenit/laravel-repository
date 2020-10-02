<?php

namespace VyDev\Repositories\Events;

use VyDev\Repositories\Events\BaseEvent;


class EntityCreated extends BaseEvent
{
    protected $action = 'created';
}