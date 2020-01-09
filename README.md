# Easy Repository

[![Build Status](https://travis-ci.org/Juniorsz/easy-repository.svg?branch=master)](https://travis-ci.org/Juniorsz/easy-repository)

Laravel Repositories is a package for Laravel 5 which is used to abstract the database layer. This makes applications much easier to maintain.

## Installation

https://packagist.org/packages/vydev/easy-repository

Run the following command from you terminal:


 ```bash
$ composer require vydev/easy-repository
 ```
 
## Usage

In ```config/app.php```

Add ```VyDev\Providers\RepositoryServiceProvider::class```

```php


App\Providers\RouteServiceProvider::class,
/* */
VyDev\Providers\RepositoryServiceProvider::class

```
Then run command

```bash
php artisan vendor:publish --provider "VyDev\Providers\RepositoryServiceProvider"

```

Edit ```config/repositories.php``` if you want to change Repository store path

**Run command to generate repository file**

Create a new repository

```sh
$ php artisan make:repository UserRepository
```

Then you can enter the name of Model to automatic generate or skip by enter @

```php
<?php 

namespace App\Repositories\Eloquent;

use VyDev\Repositories\Eloquent\BaseRepository;

class UserRepository extends BaseRepository
{

    /**
     * @return string
     */
    public function model()
    {
        return 'App\\User';
    }

    public function boot()
    {
        // $this->useTransformer();
    }

    public function transform($model)
    {
        return [
            'id' => $model->id
            //
        ];
    }

}
```

Demo : ```App\Https\Controllers\User\UserController```

```php
<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Eloquent\UserRepository;

class IndexController extends Controller
{
    protected $user;

    public function __construct(UserRepository $user)
    {
        $this->user = $user;
    }
    public function index()
    {
        $users = $this->user->all()->export();
        $firstUserPosts = $this->user->with('posts')->get()->export();
    }
}
```

Create a new criteria
```sh
$ php artisan make:criteria User/UserActive
```

```php
<?php 

namespace App\Repositories\Criteria\User;

use VyDev\Repositories\Contracts\RepositoryInterface;
use VyDev\Repositories\Criteria\Criteria;

class UserActive extends Criteria {

    /**
     * @param            $model
     * @param Repository $repository
     *
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository)
    {
        $model = $model->where('active',1);
        return $model;
    }
}
```

To use Criteria

```php
namespace App\Http\Controllers\User;
....
public function index()
{
    $this->user->pushCriteria(new UserActive());
    return $this->user->all()->export();
}
```
Criteria Interface
```php
public function applyCriteria();
public function pushCriteria(Criteria $criteria);
public function skipCriteria();
public function getByCriteria(Criteria $criteria);
public function pushManyCriterias(...$criterias);
public function removeCriteria(Criteria $criteria);
public function removeManyCriterias(...$criterias);
```

## Transform

In ```Repositories\Eloquent```
```php

public function boot()
{
    $this->useTransformer(); // Use transform
}
public function transform($model)
{
    return [
        'id' => $model->id
        'name' => $model->first_name." ".$model->last_name,
        'point' => $model->point + 10
        //
    ];
}
```

## Global Criteria

To use global criteria 

```php
public function boot()
{
    $this->pushCriteria(new UserActive());
    // $this->useTransformer();
}
```

## Credits

This package was created by Le Quang Vy 

https://packagist.org/packages/vydev/easy-repository

