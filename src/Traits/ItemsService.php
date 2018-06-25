<?php
namespace Lasarevs\LaravelRest\Traits;

use App\Http\Controllers\ApiController;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Базовый класс для сервисов. Если метода нет в сервисе - будет вызван метод из репозитория
 *
 * Class BaseService
 * @package App\Services
 *
 * @author Bondarenko Kirill <bondarenko.kirill@gmail.com>
 */
trait ItemsService
{
    /**
     * @var int
     */
    protected static $defaultPaginate = 10;

    /**
     * Условия фильтрации по умолчанию
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function baseQueryFilter($query)
    {
        return $query;
    }

    /**
     * Получение коллекций
     *
     * @param Request $request
     * @param array $params параметры для фильтра
     * @param bool $paginate
     * @return
     */
    public function getItems(Request $request = null, $params = [], $paginate = true)
    {
        $model = $this->modelClass;

        $query = $this->isModelUseFilter() ? $model::setFilterAndRelationsAndSort($request, $params) : new $model;
        $query = $this->baseQueryFilter($query);

        $relations = $this->getRelations();
        if (!empty($relations)) {
            $query = $query->with($relations);
        }

        $items = $query->paginate($this->getPaginate($request));

        $items->appends($request->all());

        $collection = $items->toArray();

        if ($this->transformer) {
            $collection['data'] = ($this->transformer)::collection($items);
        } else {
            $collection['data'] = $items->items();
        }

        return $collection;
    }

    /**
     * Получение сущностей
     *
     * @param $id
     * @return bool
     */
    public function getItem($id, Request $request = null, $params = [], $needTransform = true)
    {
        $modelClass = $this->modelClass;

        // небольшой костыль
        try {
            $query = $this->isModelUseFilter() ?
                $modelClass::setFilterAndRelationsAndSort($request, $params) :
                new $modelClass;

            $query = $this->baseQueryFilter($query);

            $relations = $this->getRelations();

            if (!empty($relations)) {
                $query = $query->with($relations);
            }

            $model = $query->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return false;
        }

        if ($needTransform && $this->transformer) {
            $data = new $this->transformer($model);
        } else {
            $data = $model;
        }

        return $data;
    }

    /**
     * Получить список связей из expand
     *
     * @param string $type
     * @param Request $request
     *
     * @return array
     */
    public function getRelations(Request $request = null)
    {
        $action = $this->getActionName();
        if (is_array(array_values($this->relations)[0] ?? null) && in_array($action, array_keys($this->relations))) {
            $relations = $this->relations[$action];
        } else {
            $relations = $this->relations;
        }

        if ($request && $request->get('expand')) {
            $relations = array_merge($relations, explode(',', $request->get('expand')));
        }

        return $relations;
    }

    /**
     * @return string
     */
    public function getActionName()
    {
        list(, $action) = explode('@', \Route::getCurrentRoute()->getActionName());

        return $action;
    }

    /**
     * Получить пагинацию
     *
     * @param Request|null $request
     * @return int|mixed
     */
    public function getPaginate(Request $request = null)
    {
        return ($request && $request->has('limit')) ? $request->get('limit') : static::$defaultPaginate;
    }

    /**
     * check used filter in model
     *
     * @return bool
     */
    protected function isModelUseFilter()
    {
        return method_exists($this->modelClass, 'scopeSetFilterAndRelationsAndSort');
    }
}
