<?php

namespace OTGH\LaravelWhois\Facades;

use Illuminate\Support\Facades\Facade;

class LaravelWhois extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \OTGH\LaravelWhois\LaravelWhois::class;
    }
}
