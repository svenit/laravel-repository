# Easy Repository

[![Build Status](https://travis-ci.org/Juniorsz/easy-repository.svg?branch=master)](https://travis-ci.org/Juniorsz/easy-repository)
[![Maintainability](https://api.codeclimate.com/v1/badges/69006185e63829b6a304/maintainability)](https://codeclimate.com/github/Juniorsz/easy-repository/maintainability)

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

/*
* Package Service Providers...
*/

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

    protected $transform = false;

    public function model()
    {
        return 'App\\User';
    }

    public function boot()
    {
        // $this->pushCriteria(new YourCriteria());
    }

    public function transform($model)
    {
        return [
            'id' => [
                'field' => 'id',
                'value' => $model->id,
                'visible' => true
            ]
            //
        ];
    }

    /* You can define your customize function bellow */

    public function countPosts()
    {
        return $this->withCount('posts');
    }

}
```

Demo : ```App\Https\Controllers\User\UserController```

```php
<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Eloquent\UserRepository as User;

class IndexController extends Controller
{
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }
    public function index()
    {
        $users = $this->user->all()->export();
        $firstUserPosts = $this->user->with('posts')->get()->export();
        /* If you want set cache in range of a time */
        $cacheKey = 'first-user-posts';
        $time = 10;
        $firstUserPosts = $this->user->with('posts')->get()->exportWithCache($cacheKey,$time);
        /* Set cache forever */
        $firstUserPosts = $this->user->with('posts')->get()->exportWithCache($cacheKey); // Set only cache key

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

class UserActive extends Criteria 
{
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

use App\Repositories\Criteria\UserActive;

public function testPushCriteria()
{
    /* Uncomment to view the change ^^ */
    $this->user->pushCriteria(new UserActive());
    // return $this->user->all();
    $this->user->skipCriteria();
    // return $this->user->all();
}
public function testGetByCriteria()
{
    /* If you only want to load a specific Criteria, let do this */
    return $this->user->getByCriteria(new UserActive());
}

public function testRemoveCriteria()
{
    /* If you have 2 or more criteria and you want to remove one of theme */
    $this->user->pushManyCriterias(new UserActive(),new UserHaveFivePosts());
    /* Delete a specific criteria */
    $this->user->removeCriteria(new UserHaveFivePosts());

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

In ```App\Repositories\Eloquent\UserRepository```
```php

protected $transform = true; // Define true to use transfomer

public function transform($model)
{
    return [
        'id' => [
            'field' => 'id', // The id field in users table
            'value' => $model->id // The new data for id field, Example : value => $model->id + 10,
            'visible' => true // Show or hidden, if you delete this key, default value is true
        ]
        // Some...
    ];
}
```

## Global Criteria

To use global criteria or somethings else

```php

use App\Repositories\Criteria\UserActive;

public function boot()
{
    $this->pushCriteria(new UserActive());
}
```

## Credits

This package was created by Lê Quang Vỹ

------------------- Contact ----------------------

Facebook : https://www.facebook.com/sven307

Email : lequangvy2k@gmail.com

https://packagist.org/packages/vydev/easy-repository

