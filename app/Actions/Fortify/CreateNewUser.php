<?php

namespace App\Actions\Fortify;

use App\Http\Requests\IThinkItIsAVeryBadIdeaToCreateThisCustomRegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function create(array $input): User
    {
        $registerRequestOnlyToMeetTheUnreasonableSpecsThatForcesMeToUseFormRequest = new IThinkItIsAVeryBadIdeaToCreateThisCustomRegisterRequest;
        Validator::make(
            $input,
            $registerRequestOnlyToMeetTheUnreasonableSpecsThatForcesMeToUseFormRequest->rules(),
            attributes: $registerRequestOnlyToMeetTheUnreasonableSpecsThatForcesMeToUseFormRequest->attributes()
        )->validate();

        return User::create([
            'name'     => $input['name'],
            'email'    => $input['email'],
            'password' => Hash::make($input['password']),
        ]);
    }
}
