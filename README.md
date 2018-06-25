## Laravel REST actions

Package included CRUD action traits, service trait, ApiController and transformer

How to install:

```bash
composer require lasarevs/laravel-rest
```

or add in **composer.json** record to require block **require**:

```
For Laravel >=5.6
```
```json
"lasarevs/laravel-rest": "*"
```

## Using

Extend You controllers from Lasarevs\LaravelRest\Controllers\ApiController and use action traits for him/

In Lasarevs\LaravelRest\Traits\Actions\* isset CRUD operations for controller

You need to set $modelClass variable in controller and using action traits

```php
    class SomeController
    {
        use IndexAction, ShowAction, StoreAction, UpdateAction, DestroyAction;
    
        protected $modelClass = SomeModel::class;    
    }
```

If any of the methods that do not need it, just remove it

If you need all action, use GeneralActions

## baseQueryFilter

if you need filtering data by default conditions, use baseQueryFilter method on you controller:

```php
    public function baseQueryFilter($query)
    {
        return $query->where('owner', Auth::user()->id);
    }
```


## default paginate

ItemService trait have static variable $defaultPaginate. You will change it in your controllers  



## Filter using

Package include [filter](https://github.com/nemesis1988/FilterAndSorting|filter).
