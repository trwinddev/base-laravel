<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class ForgotPasswordController extends Controller
{
    use ApiResponseTrait;

    public function forgot(Request $request)
    {
        $ruleArr = [
            'email' => ['required', 'email', Rule::exists('users')->withoutTrashed()]
        ];
        $validator = Validator::make($request->all(), $ruleArr);
        if ($validator->fails()) {
            return $this->responseErrorMessage($validator->errors()->first());
        }
        $status = Password::broker('users')->sendResetLink($request->only('email'));
        return $status === Password::RESET_LINK_SENT
            ? $this->responseSuccessNoData()
            : $this->responseErrorMessage(trans('messages.error_server'), 500);
    }

    public function reset(Request $request)
    {
        $ruleArr = [
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'min:8', 'max:40', 'regex:/^(?=.*[A-Za-z])(?=.*\d)[^\s]{8,40}$/'],
            'password_confirmation' => 'required|same:password'
        ];
        $validator = Validator::make($request->all(), $ruleArr);
        if ($validator->fails()) {
            return $this->responseErrorMessage($validator->errors()->first());
        }
        $user = User::where('email', $request->email)->first();
        if (!is_null($user) && Hash::check($request->password, $user->password)) {
            return $this->responseErrorMessage("Mật khẩu mới không được trùng với mật khẩu cũ");
        }
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ]);

                $user->save();

                event(new PasswordReset($user));
            }
        );
        return $status === Password::PASSWORD_RESET
        ? $this->responseSuccessNoData()
        : $this->responseErrorMessage(trans('messages.error_server'), 500);
    }
}
