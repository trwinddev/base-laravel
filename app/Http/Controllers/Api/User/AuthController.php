<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginUserRequest;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponseTrait;
use App\Traits\ThrottlesLogins;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    use ApiResponseTrait, ThrottlesLogins;

    protected $maxAttempts = 4;
    protected $decayMinutes = 5;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function login(LoginUserRequest $request)
    {
        $postData = $request->input();
        $type = $postData['type'];

        if ($type == User::TYPE_LOGIN_NORMAL) {
            return $this->_normalLogin($request);
        }

        if ($type == User::TYPE_LOGIN_FACEBOOK) {
            return $this->_checkFacebook($request->social_token, $postData);
        }

        if ($type == User::TYPE_LOGIN_GOOGLE) {
            return $this->_checkGoogle($request->social_token, $postData);
        }

        if ($type == User::TYPE_LOGIN_APPLE) {
            $firstName = $request->first_name ?? '';
            $lastName = $request->last_name ?? '';
            return $this->_checkApple($request->social_token, $firstName, $lastName);
        }
    }

    /**
     * Login APi
     */
    private function _normalLogin($request)
    {
        $login = strtolower($request->email);
        $password = $request->password;
        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            $credentials = ['email' => $login, 'password' => $password];
            $user = User::onlyTrashed()->where('email', $login)->first();
        } else {
            $credentials = ['login_name' => $login, 'password' => $password];
            $user = User::onlyTrashed()->where('login_name', $login)->first();
        }
        $token = Auth::guard('user')->attempt($credentials);

        $key = $this->throttleKey($request);
        $rateLimiter = $this->limiter();
        $attempts = $rateLimiter->attempts($key);

        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            $this->incrementLoginAttempts($request);
            Session::put('LOGIN_ATTEMPT',$attempts);
            if ($attempts <= 4 && $token) {
                $this->limiter()->clear($key);
                Session::forget('LOGIN_ATTEMPT');
                return $this->responseSuccess([
                    'user' => Auth::guard('user')->user(),
                    'token' => $token,
                    'is_sign_up' => false,
                ]);
            }
            return $this->responseErrorMessage('Đăng nhập sai 5 lần liên tiếp. Vui lòng thử lại sau 5 phút.', Response::HTTP_UNAUTHORIZED);
        }

        if (!is_null($user)) {
            return $this->responseErrorMessage('Tài khoản không tồn tại', Response::HTTP_UNAUTHORIZED);
        }

        if (!$token) {
            $this->incrementLoginAttempts($request);
            return $this->responseErrorMessage('Tài khoản hoặc mật khẩu không chính xác', Response::HTTP_UNAUTHORIZED);
        }

        $this->limiter()->clear($key);
        Session::forget('LOGIN_ATTEMPT');

        $user = Auth::guard('user')->user()->makeHidden('password', 'deleted_at');
        if (!is_null($user) && is_null($user->email_verified_at)) {
            return $this->responseErrorMessageWithCode(trans('Tài khoản của bạn chưa được kích hoạt. Vui lòng kiểm tra email để kích hoạt tài khoản. Nếu token hết hạn hoặc không nhận được email thì hãy click <a target="_blank" href="'
            .env('URL_FRONTEND_USER').'/email-verify/resend'.'">vào đây</a> để gửi lại'), 422, User::STATUS_HTTP_NOT_VERIFY);
        }
        $data = [
            'user' => $user,
            'token' => $token,
            'is_sign_up' => false,
        ];
        // (new UserToken())->processTokenLogin($user, $token, $tokenSso);
        return $this->responseSuccess($data);

    }

    /**
     * @param String $social_token
     * @return void
     */
    private function _checkGoogle($social_token, $postData)
    {
        try {
            $checkToken = $this->client->get("https://www.googleapis.com/oauth2/v3/userinfo?access_token=$social_token");
            $responseGoogle = json_decode($checkToken->getBody()->getContents(), true);
            $responseGoogle['first_name'] = $responseGoogle['given_name'] ?? '';
            $responseGoogle['last_name'] = $responseGoogle['family_name'] ?? '';
            $responseGoogle['avatar'] = $responseGoogle['picture'] ?? '';
            $responseGoogle['social_google_id'] = $responseGoogle['sub'] ?? '';
            $responseGoogle['type'] = User::TYPE_LOGIN_GOOGLE;

            return $this->_checkUserByEmail($responseGoogle);
        } catch (\Exception $e) {
            Log::error('Social login error: login google error '.$e->getMessage());
            return $this->responseErrorMessage(trans('messages.error_server'), 500);
        }
    }

    /**
     * @param String $social_token
     * @return void
     */
    private function _checkFacebook($social_token, $postData)
    {
        try {
            $checkToken = $this->client->get("https://graph.facebook.com/v3.1/me?fields=id,name,email,first_name,last_name&access_token=$social_token");
            $responseFacebook = json_decode($checkToken->getBody()->getContents(), true);
            $responseFacebook['avatar'] = "https://graph.facebook.com/" . $responseFacebook['id'] . "/picture?type=large";
            $responseFacebook['social_facebook_id'] = $responseFacebook['id'];
            $responseFacebook['type'] = User::TYPE_LOGIN_FACEBOOK;

            return $this->_checkUserByEmail($responseFacebook);
        } catch (\Exception $e) {
            Log::error('Social login error: login facebook error '.$e->getMessage());
            return $this->responseErrorMessage(trans('messages.error_server'), 500);
        }
    }

    /**
     * @param String $social_token
     * @return void
     */
    private function _checkApple($social_token, $firstName, $lastName)
    {
        try {
            $responseApple = [];
            $user = Socialite::driver('apple')->userFromToken($social_token);
            $responseApple['avatar'] = $user->avatar;
            $responseApple['first_name'] = $firstName;
            $responseApple['last_name'] = $lastName;
            $responseApple['email'] = $user->email;
            $responseApple['social_apple_id'] = $user['sub'] ?? '';
            $responseApple['type'] = User::TYPE_LOGIN_APPLE;
            $responseApple['is_private_email'] = $user['is_private_email'] ?? '';

            return $this->_checkUserByEmail($responseApple);
        } catch (\Exception $e) {
            Log::error('Social login error: login apple error '.$e->getMessage());
            return $this->responseErrorMessage(trans('messages.error_server'), 500);
        }
    }

    /**
     * @param $profile
     * @return void
     */
    private function _checkUserByEmail($profile)
    {
        try {
            $emailFacebookInvalid = false;
            $isLoginFacebook = $profile['type'] == User::TYPE_LOGIN_FACEBOOK;
            if ($isLoginFacebook) {
                if (isset($profile['email'])) {
                    $user = User::withTrashed()->where('email', $profile['email'])->first();
                } else {
                    $emailFacebookInvalid = true;
                    $user = User::withTrashed()->where('social_facebook_id', $profile['social_facebook_id'])->first();
                }
            }
            if ($profile['type'] == User::TYPE_LOGIN_GOOGLE) {
                $user = User::withTrashed()->where('email', $profile['email'])->first();
            }
            if ($profile['type'] == User::TYPE_LOGIN_APPLE) {
                if (isset($profile['social_apple_id']) && $profile['social_apple_id'] != '') {
                    $user = User::withTrashed()->where('social_apple_id', $profile['social_apple_id'])->first();
                }
            }

            $fileImage = $profile['avatar'] ?? null;
            if (!is_null($fileImage)) {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $type = $finfo->buffer(file_get_contents($fileImage));
                $type = str_replace('image/', '', $type);
                $fileName = Str::random(7) . '-'. Str::uuid().'.'.$type;
                $service = new UploadService();
                $uploadAvatar = $service->upload(config('aws.s3_folder.user.avatar'), $fileName, file_get_contents($fileImage));
                if (!$uploadAvatar) {
                    return $this->responseErrorMessage('Upload avatar fail');
                }
            }

            if ($isLoginFacebook && isset($profile['email'])) {
                if ($profile['email'] == '') {
                    $emailFacebookInvalid = true;
                } else {
                    $domainEmail = explode("@", $profile['email'])[1];
                    if (in_array($domainEmail, ['tfbnw.net', 'facebook.com', 'fb.com', 'fb.me'])) {
                        $profile['email'] = '';
                        $emailFacebookInvalid = true;
                    }
                }
            }

            if ($user && !is_null($user->deleted_at)) {
                return $this->responseErrorMessage('Tài khoản đã được thu hồi. Liên hệ với admin để được hỗ trợ');
            }

            if ($user && $user->email && is_null($user->email_verified_at)
                && ($profile['type'] == User::TYPE_LOGIN_FACEBOOK || $profile['type'] == User::TYPE_LOGIN_APPLE )) { // check khi đăng nhập từ lần thứ hai trở lên
                Log::error('Social login error: Check email error '.new Exception('Tài khoản của bạn chưa được kích hoạt'));
                return $this->responseErrorMessageWithCode(trans('Tài khoản của bạn chưa được kích hoạt.
                Vui lòng kiểm tra email để kích hoạt tài khoản. Nếu token hết hạn hoặc không nhận được email thì hãy click <a target="_blank" href="'
                .env('URL_FRONTEND_PUBLISHER').'/email-verify/resend'.'">vào đây</a> để gửi lại'), 422, User::STATUS_HTTP_NOT_VERIFY);
            }
            $is_sign_up = false;
            DB::beginTransaction();
            if (is_null($user)) {
                $ruleArr= [
                    'email' => Rule::unique('users'),
                ];
                $validator = Validator::make($profile, $ruleArr);
                if ($validator->fails()) {
                    return $this->responseErrorMessage($validator->errors()->first());
                }
                $data = [
                    'first_name' => $profile['first_name'] ?? '',
                    'last_name' => $profile['last_name'] ?? '',
                    'login_name' => '',
                    'phone_number' => '',
                    'email' => $profile['email'] ?? '',
                    'avatar' => $fileName ?? null,
                    'password' => '',
                    'social_facebook_id' => $profile['social_facebook_id'] ?? '',
                    'social_google_id' => $profile['social_google_id'] ?? '',
                    'social_apple_id' => $profile['social_apple_id'] ?? '',
                    'login_processing' => User::LOGIN_PROCESSING,
                ];
                if ($data['first_name'] == '' && $data['last_name'] == '') {
                    $data['first_name'] = 'user';
                    $data['last_name'] = (new User)->generalLastName() ?? throw new Exception('Cannot generate last name');
                }
                $user = User::create($data);
                $is_sign_up = true;
            }
            $token =  Auth::guard('user')->login($user);
            $data = [
                'user' => $user->makeHidden('password', 'deleted_at'),
                'token' => $token,
                'token_sso' => $tokenSso,
                'is_login_facebook' => !is_null($user) ? $user['login_processing'] : $isLoginFacebook,
                'is_sign_up' => $is_sign_up
            ];
            DB::commit();
            return $this->responseSuccess($data);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Social login error: Check email error '.$e->getMessage());
            return $this->responseErrorMessage(trans('messages.error_server'), 500);
        }
    }

    /**
     * Logout APi
     */
    public function logout()
    {
        try {
            Auth::guard('user')->logout();
            return $this->responseSuccessNoData();
        } catch (Exception $e) {
            return $this->responseErrorMessage(trans('messages.error_server'), 500);
        }
    }
}
