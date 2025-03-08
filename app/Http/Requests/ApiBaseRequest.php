<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Http\Request;

class ApiBaseRequest extends Request
{
    public function expectsJson(): bool
    {
        if (request()->is('api/documentation') || request()->is('api/documentation/v*')) {
            return false;
        }

        return true;
    }

    public function wantsJson(): bool
    {
        if (request()->is('api/documentation') || request()->is('api/documentation/v*')) {
            return false;
        }

        return true;
    }
}
