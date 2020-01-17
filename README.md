# Laravel Restful Controller
[![Build Status](https://travis-ci.com/TomHart/laravel-restful-controller.svg?branch=master)](https://travis-ci.com/TomHart/laravel-restful-controller)
[![codecov](https://codecov.io/gh/TomHart/laravel-restful-controller/branch/master/graph/badge.svg)](https://codecov.io/gh/TomHart/laravel-restful-controller)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/TomHart/laravel-restful-controller/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/TomHart/laravel-restful-controller/?branch=master)
![GitHub release (latest by date)](https://img.shields.io/github/v/release/TomHart/laravel-restful-controller?color=green)
[![License: GPL v3](https://img.shields.io/badge/License-GPLv3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)


This library adds an `AbstractRestfulController` to to be basic heavy lifting of a CRUD controller. 


* [Installation](#installation)
* [Usage](#usage)
  * [Relationships](#relationships)
    * [Loading Relationships](#loading-relationships)
    * [Accessing Relationships](#accessing-relationships)
  * [Restricting Access to Models](#restricting-access-to-models)
    * [Index page](#index-page)
    * [Show, Update, and Destroy Pages](#show-update-and-destroy-pages)
  * [Manipulating models before saving or updating](#manipulating-models-before-saving-or-updating)
  * [Pagination](#pagination)
  * [Filtering](#filtering) 
  * [HasLinks](#haslinks)
  * [Builder](#builder)



##Installation
You can install this package via composer using this command:

`composer require tomhart/laravel-restful-controller`


## Usage
* Create a controller extending from this, and implement the method `getModelClass`

```php
use TomHart\Restful\AbstractRestfulController;

class BlogController extends AbstractRestfulController 
{
    /**
     * What Model class to search for entities.
     * @return string
     */
    protected function getModelClass(): string
    {
        return Blog::class;
    }
}

```

* If you want it to render views for index, show, or store, add a `$views` property
```php

    /**
     * The views to render.
     * @var array
     */
    protected $views = [
        'index' => 'blog/index',
        'show' => 'blog/show',
        'store' => 'blog/store'
    ];
```
If `$views` is empty, the specified view doesn't exist, or the `Accept` header is `application/json`, then JSON is returned


* Define a resource route
```php
Route::resource('blogs', 'BlogController');
```
Note this also would define a `blogs.show.extra`, and `blogs.show.options` route 
which will be explained later.

Example response for: `/blogs/1`
```json
{
    "id": 1,
    "title":  "My Blog Post",
    "content":  "<h1>Title</h1><p>Some Content</p>"
}
```

### Relationships

#### Loading Relationships
The show route can return your models relationships. If you send a `X-Load-Relationship` header, 
with a comma separated value list of headers to load. See the `testRelationshipsCanBeReturned` test 
for an example.

Example response for: `/blogs/1` with `X-Load-Relationship: comments`
```json
{
    "id": 1,
    "title":  "My Blog Post",
    "content":  "<h1>Title</h1><p>Some Content</p>",
    "comments": [
        {
            "id": 1,
            "comment": "Great post!"        
        },
        { 
            "id": 2,
            "comment": "I enjoyed reading this"
        }
    ]  
}
```

#### Accessing Relationships
You can drill into a relationship using the `.show.extra` route mentioned above. If the first `comment` had an author 
and you wanted to see, via the blog resources, you can call `/blogs/1/comments[0]/author`
```json
{
    "id": 1,
    "name": "Joe Bloggs"
}
```
  
You can dynamically build the route using 
```php
route('blogs.show.extra', [
    'blog' => 1,
    'extra' => 'comments[0]/author'
]); 
```

### Restricting Access to Models
You'll most likely want to restrict access to certain models, e.g. only load the logged in
users posts. To do that, there's a few methods you can overwrite.

#### Index Page
In order to restrict the models returned by the index route, e.g. a paginated list of many models,
overwrite the `createModelQueryBuilder` method.

#### Show, Update, and Destroy Pages
In order to restrict which indiviual models can be shown, updated, or deleted, overwrite the
`findModel` method.

### Manipulating models before saving or updating
If you want to manipulate the model before they are saved, or updated, e.g. setting the user_id
to the current logged in user, override the `saveModel` method.


### Pagination
By default the `index` route, and any relationships it's trying to load will be paginated if possible.

Example response for: `/blogs`
```json
{
   "total": 50,
   "per_page": 15,
   "current_page": 1,
   "last_page": 4,
   "first_page_url": "http://laravel.app?page=1",
   "last_page_url": "http://laravel.app?page=4",
   "next_page_url": "http://laravel.app?page=2",
   "prev_page_url": null,
   "path": "http://laravel.app",
   "from": 1,
   "to": 15,
   "data":[
        {
            "id": 1
        },
        {
            "id": 2
        }
   ]
}
```

### Filtering
You can filter the `index` route via a query string, e.g. `?name=test`.

### HasLinks
This library also provides `HasLinks` interface, and a `HasLinksTrait` to provide a default implementation. If you apply
those to your models, the responses will contain a `_links` key to help your consumers navigate around and use your API.

Example `_links` for `/blogs/1`:
```json
{  
    "id": 1,
    "title": "My Blog",
    "content": "See some _links!",
    "_links": {
        "index": {
            "method":  "get",
            "href": {
                "relative": "/blogs/",
                "absolute": "https://api.example.com/blogs/"
            }
        },
        "create": {
            "method":  "get",
            "href": {
                "relative": "/blogs/",
                "absolute": "https://api.example.com/blogs/"
            }
        },
        "store": {
            "method":  "post",
            "href": {
                "relative": "/blogs/",
                "absolute": "https://api.example.com/blogs/"
            }
        },
        "show": {
            "method":  "get",
            "href": {
                "relative": "/blogs/1",
                "absolute": "https://api.example.com/blogs/1"
            }
        },
        "update": {
            "method":  "put",
            "href": {
                "relative": "/blogs/1",
                "absolute": "https://api.example.com/blogs/1"
            }
        },
        "destroy": {
            "method":  "delete",
            "href": {
                "relative": "/blogs/1",
                "absolute": "https://api.example.com/blogs/1"
            }
        }
    }
}
``` 

The `.options` route mentioned earlier will simply return the `index`, `create`, and `store` `_links` for the resource
so you can query the endpoint and get the URLs needing to interfacing with the API.

If you send `{"id": X}`, it'll also build the `show`, `update`, and `delete` routes with the ID supplied.

### Builder
This library also includes a `Builder` class to interface with the API from a consumer view.
It supports the standard `get`, `insert`, `update`, and `delete` methods.

Example:
```php
use TomHart\Restful\Builder;

$models = Builder::model(MyModel::class)->where('name', 'test')->get(); // Collection

$modelWasInserted = Builder::model(MyModel::class)->insert(['name' => 'test']); //bool

$modelWasUpdated = Builder::model(MyModel::class)->update(1, ['name' => 'test']); //bool

$modelWasDeleted = Builder::model(MyModel::class)->delete(1); //bool
``` 

To use it with your model simply add `implements Restful`, and use the trait `InteractsWithRest`