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
        $action = ucfirst($this->getActionName());

        $model = $this->modelClass;

        $query = $this->isModelUseFilter() ? $model::setFilterAndRelationsAndSort($request, $params) : new $model;
        $query = $this->baseQueryFilter($query);

        $relations = $this->getRelations();
        if (!empty($relations)) {
            $query = $query->with($relations);
        }

        $items = $query->paginate($this->getPaginate($request));
        $items->appends($request->all());

        if ($this->transformer) {
            $collection = ($this->transformer)::collection($items);

            $methodName = 'getAdditional' . $action;
            if (method_exists($this, $methodName)) {
                $collection = $collection->additional($this->{$methodName}($request));
            }
        } else {
            $collection = $items->toArray();
        }

        return $collection;
    }

    /**
     * Получение сущностей
     *
     * @param $id
     */
    public function getItem($id, Request $request = null, $params = [], $needTransform = true)
    {
        $action = ucfirst($this->getActionName());

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
                $model = $query->findOrFail($id);
            } else {
                $model = $modelClass::findOrFail($id);
            }
        } catch (ModelNotFoundException $e) {
            return false;
        }

        if ($needTransform && $this->transformer) {
            $data = ['data' => new $this->transformer($model)];
        } else {
            $data = ['data' => $model];
        }

        $methodName = 'getAdditional' . $action;
        if (method_exists($this, $methodName)) {
            $data = array_merge($data, $this->{$methodName}($request));
        }

        if (in_array($this->getActionName(), ['update', 'destroy'])) {
            return $data['data'];
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

        if (in_array($action, ['update', 'destroy'])) {
            return [];
        }

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