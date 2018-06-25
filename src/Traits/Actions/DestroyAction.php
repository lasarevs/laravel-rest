<?php
namespace Lasarevs\LaravelRest\Traits\Actions;

use Illuminate\Http\Response;

/**
 * Delete entity
 *
 * Class DestroyAction
 * @package Nemesis\LaravelRest\Traits\Actions
 *
 * @version 1.0.0
 * @author Bondarenko Kirill <bondarenko.kirill@gmail.com>
 */
trait DestroyAction
{
    /**
     * @param $id
     * @return Response
     */
    public function destroy($id)
    {
        $request = $this->getRequestObject();

        $model = $this->getItem($id, $request, [], false);
        if ( ! $model) {
            return $this->respondNotFound();
        }

        $model->delete();

        return $this->setStatusCode(Response::HTTP_NO_CONTENT)->respond([]);
    }
}