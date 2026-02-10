<?php

namespace App\Services;

use Closure;
use Illuminate\Support\Facades\DB;

abstract class BaseService
{
    /**
     * 트레젝션 처리
     * @param Closure $callback
     * @return mixed
     */
    protected function transaction(Closure $callback): mixed
    {
        return DB::transaction($callback);
    }
}