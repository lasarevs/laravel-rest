<?php
namespace Lasarevs\LaravelRest\Controllers;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Lasarevs\LaravelRest\Traits\ItemsService;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

/**
 * Базовый контроллер для API
 *
 * Class ApiController
 * @package App\Http\Controllers
 *
 * @version 1.0.0
 * @author Bondarenko Kirill <bondarenko.kirill@gmail.com>
 */
class ApiController extends Controller
{
    use ItemsService;

    /**
     * Main data array for all views.
     *
     * @var array
     */
    public $data = [];

    /**
     * список необходимых Request для action
     *
     * Ключ - имя действия
     * Значение - необходимый класс Request
     *
     * Нужно перегружать свойство в дочерних классах, если им нужны не стандартные Request
     *
     * @var array
     */
    protected $requestClasses = [
        'update' => Request::class,
        'store' => Request::class
    ];

    /** @var Model */
    protected $modelClass;

    /**
     * @var int
     */
    protected $statusCode = 200;

    /**
     * @var JsonResource
     */
    protected $transformer;

    /**
     * @var array
     */
    protected $relations = [];

    /**
     * получить нужный объект Request для данного действия и контроллера
     *
     * @return string|bool
     */
    public function getRequestObject()
    {
        $action = $this->getActionName();

        if (in_array($action, array_keys($this->requestClasses))) {
            return app($this->requestClasses[$action]);
        }

        return app(Request::class);
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
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param int $statusCode
     * @return ApiController
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * @param string $message
     * @return Response
     */
    public function respondNotFound($message = 'Not found!')
    {
        return $this->setStatusCode(Response::HTTP_NOT_FOUND)->respondWithError($message);
    }

    /**
     * @param string $message
     * @return Response
     */
    public function respondBadRequest($message = 'Bad request!')
    {
        return $this->setStatusCode(Response::HTTP_BAD_REQUEST)->respondWithError($message);
    }

    /**
     * @param array $data
     * @return Response
     */
    public function respondValidationErrors($data)
    {
        return $this->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY)->respond($data);
    }

    /**
     * @param string $message
     * @return Response
     */
    public function respondInternalError($message = 'External error!')
    {
        return $this->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR)->respondWithError($message);
    }

    /**
     * @param $message
     * @return Response
     */
    public function respondWithError($message)
    {
        return $this->respond([
            'error' => [
                'message' => $message,
                'status_code' => $this->getStatusCode()
            ]
        ]);
    }

    /**
     * Ответ в виде json
     *
     * @param $data
     * @param array $headers
     *
     * @return Response
     */
    public function respond($data, $headers = [])
    {
        if ($data instanceof ResourceCollection) {
            return $data->toResponse(\request());
        }
        return response($data, $this->getStatusCode(), $headers);
    }

    /**
     * Render the current view.
     *
     * @param String $view
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function render($view)
    {
        if (view()->exists($view)) {
            return view($view, $this->data);
        }

        abort(404);
    }
}