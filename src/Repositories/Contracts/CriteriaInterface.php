<?php

namespace VyDev\Repositories\Contracts;

use VyDev\Repositories\Criteria\Criteria;

interface CriteriaInterface
{
    public function applyCriteria();
    public function pushCriteria(Criteria $criteria);
    public function skipCriteria();
    public function getByCriteria(Criteria $criteria);
    public function pushManyCriterias(...$criterias);
    public function removeCriteria(Criteria $criteria);
    public function removeManyCriterias(...$criterias);
}