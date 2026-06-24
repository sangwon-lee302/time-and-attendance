<?php

namespace App\Http\Requests;

use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Requests\LoginRequest;
use Override;

class AdminLoginRequest extends LoginRequest
{
    #[Override]
    protected function prepareForValidation(): void
    {
        $this->whenHas(Fortify::username(), function () {
            $this->merge([
                Fortify::username() => Str::lower($this->{Fortify::username()}),
            ]);
        });
    }
}
