<?php
namespace Lasarevs\LaravelRest\Traits\Actions;

use Illuminate\Http\Response;

/**
 * General actions traits
 *
 * Class GeneralActions
 * @package Nemesis\LaravelRest\Traits\Actions
 *
 * @version 1.0.0
 * @author Bondarenko Kirill <bondarenko.kirill@gmail.com>
 */
trait GeneralActions
{
    use StoreAction, IndexAction, ShowAction, UpdateAction, DestroyAction;
}