<?php
namespace Lasarevs\LaravelRest\Traits\Actions;

use Illuminate\Http\Response;

/**
 * Update entity
 *
 * Class UpdateAction
 * @package Nemesis\LaravelRest\Traits\Actions
 *
 * @version 1.0.0
 * @author Bondarenko Kirill <bondarenko.kirill@gmail.com>
 */
trait UpdateAction
{
    /**
     * @param $id
     * @return Response
     */
    public function update($id)
    {
        $request = $this->getRequestObject();

        $model = $this->getItem($id, $request, [], false);
        if (!$model) {
            return $this->respondNotFound();
        }

        $model->fill($request->all());
        $model->save();

        return $this->setStatusCode(Response::HTTP_CREATED)->respond($model);
    }
}