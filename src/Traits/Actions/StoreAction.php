<?php
namespace Lasarevs\LaravelRest\Traits\Actions;

use Illuminate\Http\Response;

/**
 * Save entity
 *
 * Class StoreAction
 * @package Nemesis\LaravelRest\Traits\Actions
 *
 * @version 1.0.0
 * @author Bondarenko Kirill <bondarenko.kirill@gmail.com>
 */
trait StoreAction
{
    /**
     * @return Response
     */
    public function store()
    {
        $request = $this->getRequestObject();

        $model = $this->modelClass;
        $item = $model::create($request->all());

        return $this->setStatusCode(Response::HTTP_CREATED)->respond($item);
    }
}