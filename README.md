# Easy Repository Version 2

[![Build Status](https://travis-ci.org/Juniorsz/easy-repository.svg?branch=master)](https://travis-ci.org/Juniorsz/easy-repository)
[![Maintainability](https://api.codeclimate.com/v1/badges/69006185e63829b6a304/maintainability)](https://codeclimate.com/github/Juniorsz/easy-repository/maintainability)

Laravel Repositories is a package for Laravel 5 which is used to abstract the database layer. This makes applications much easier to maintain.

Current Version: 2.0.2

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

If console throw error ```Command "make:repository" is not defined."```

In : ```app/Console/Kernel.php```

```php

use VyDev\Commands\MakeCriteria;
use VyDev\Commands\MakeRepository;

protected $commands = [
    MakeCriteria::class,
    MakeRepository::class
];

```
Then run ```php artisan optimize:clear```


```php
<?php

namespace App\Repositories\Eloquent;

use VyDev\Repositories\Eloquent\BaseRepository;
use VyDev\Repositories\Criteria\AdvancedSearchCriteria;


class UserRepository extends BaseRepository
{

    protected $transform = false;

    public function model()
    {
        return 'App\\User';
    }

    public function boot()
    {
        /* Uncomment if you want to use advanced search */
        // $this->pushCriteria(new AdvancedSearchCriteria());
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

Repository Interface

```php

public function all();
public function get($columns = ['*']);
public function first();
public function latest();
public function random();
public function exists();
public function find($id);
public function findOrFail($id);
public function findOrNew($id, $columns = ['*']);
public function pluck($columns,$key);
public function sync($attributes);
public function syncWithoutDetaching($attributes);
public function attach($attributes);
public function detach($attributes);
public function count();
public function firstOrNew(array $attributes);
public function firstOrCreate(array $attributes);
public function limit($arg);
public function take($arg);
public function offset($arg);
public function paginate($limit = 15, $columns = ['*']);
public function where($field,$operator, $value = null);
public function whereIn($field,$values);
public function whereNotIn($field,$values);
public function whereBetween($field,$values);
public function create($values);
public function save();
public function update($values);
public function delete();
public function updateOrCreate(array $attributes, array $values = []);
public function has($relation);
public function with($relations);
public function withCount($relations);
public function loadCount($relations);
public function whereHas($relation, $closure);
public function orderBy($column, $direction = 'asc');
public function load($relation);
public function search($fields,$value);
public function hidden($columns = ['*']);
public function visible($columns = ['*']);
public function increment($field, $quantity = null);
public function decrement($field, $quantity = null);
public function export();

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
    $this->user->popCriteria(new UserHaveFivePosts());

    return $this->user->all()->export();
}

public function pushWithCondition(Request $request)
{
    $condition = $request->status == 'active' ? true : false;

    $this->user->pushCriteriaWhen([
        $condition => new FilterActiveUser(),
        // Somethings else
    ]);
}
```
Criteria Interface
```php
public function applyCriteria();
public function pushCriteria(Criteria $criteria);
public function pushCriteriaWhen($arguments);
public function pushManyCriterias(...$criterias);
public function skipCriteria();
public function getByCriteria(Criteria $criteria);
public function popCriteria(Criteria $criteria);
public function pushCriteriaWhen($arguments);
public function popManyCriterias(...$criterias);
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

use VyDev\Repositories\Criteria\AdvancedSearchCriteria;

public function boot()
{
    $this->pushCriteria(new AdvancedSearchCriteria());
}
```

Request all data without filter by request

```https://yourdomain.example/products```

```json
[
    {
        id: 1,
        cate_id: 2,
        producer_id: 3,
        name: "TV Sam Sung",
        price: 2200,
    },
    {
        id: 2,
        cate_id: 1,
        producer_id: 3,
        name: "Air Conditioner Sony",
        price: 3600,
    },
    {
        id: 3,
        cate_id: 1,
        producer_id: 3,
        name: "Air Conditioner Sam Sung",
        price: 3650,
    },
    {
        id: 4,
        cate_id: 7,
        producer_id: 5,
        name: "Smart Phone LG",
        price: 1200,
    }
]
```
Try it :

```https://yourdomain.example/products?search=name:LIKE:Sam%20Sung```

```json
[
    {
        id: 1,
        cate_id: 2,
        producer_id: 3,
        name: "TV Sam Sung",
        price: 2200,
    },
    {
        id: 3,
        cate_id: 1,
        producer_id: 3,
        name: "Air Conditioner Sam Sung",
        price: 3650,
    }
]
```

```https://yourdomain.example/products?search=name:LIKE:Sam%20Sung;id:=:3```

```json
[
    {
        id: 3,
        cate_id: 1,
        producer_id: 3,
        name: "Air Conditioner Sam Sung",
        price: 3650,
    }
]
```

```https://yourdomain.example/products?search=name:LIKE:Sam%20Sung;id:=:3&filter=name```

```json
[
    {
        name: "Air Conditioner Sam Sung",
    }
]
```

Load a relation and filter ( .* to get all data )

```https://yourdomain.example/products?search=name:LIKE:Sam%20Sung;id:=:3&filter=name;producer.id,name```

```json
[
    {
        name: "Air Conditioner Sam Sung",
        producer_id: 3,
        producer: {
            id: 3,
            name: "Sam Sung"
        }
    }
]
```

## Credits

This package was created by Lê Quang Vỹ

------------------- Contact ----------------------

Facebook : https://www.facebook.com/sven307

Email : lequangvy2k@gmail.com

https://packagist.org/packages/vydev/easy-repository

