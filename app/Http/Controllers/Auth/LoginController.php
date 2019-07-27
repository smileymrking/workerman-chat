<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\JWT;
use Tymon\JWTAuth\JWTGuard;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    const PASSWORD_ERROR = 0;
    const USERNAME_ERROR = 1;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    protected $authService;

    protected $errorType = self::PASSWORD_ERROR;

    /**
     * LoginController constructor.
     * @param AuthService $authService
     */
    public function __construct(AuthService $authService)
    {
        $this->middleware('guest')->except(['logout', 'refresh']);
        $this->authService = $authService;
    }

    /**
     * @return JWTGuard|JWTAuth|JWT
     * @author: King
     * @version: 2019/7/25 17:20
     */
    protected function guard()
    {
        return Auth::guard();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @author: King
     * @version: 2019/7/25 17:19
     */
    protected function sendLoginResponse(Request $request)
    {
        $this->clearLoginAttempts($request);

        $this->authenticated($request, $this->guard()->user())
            ?: redirect()->intended($this->redirectPath());

        $tokenResponse = $this->tokenResponse($this->guard()->getToken()->get());

        return $this->successWithData(collect([
            'user' => $this->guard()->user()
        ])->merge($tokenResponse)->toArray());
    }

    /**
     * @param Request $request
     * @author: King
     * @version: 2019/7/26 16:55
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        $validations = $this->errorType === self::USERNAME_ERROR
        ? [$this->username() => [__('auth.username_failed')]]
        : ['password' => [__('auth.password_failed')]];

        throw ValidationException::withMessages($validations);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @author: King
     * @version: 2019/7/26 16:55
     */
    protected function sendLockoutResponse(Request $request)
    {
        $seconds = $this->limiter()->availableIn(
            $this->throttleKey($request)
        );

        return $this->failedWithMessage(__('auth.throttle', ['seconds' => $seconds]));
    }

    /**
     * @param Request $request
     * @return bool
     * @author: King
     * @version: 2019/7/25 17:19
     */
    protected function attemptLogin(Request $request)
    {
        $credentials = $this->credentials($request);
        return collect(['email'])->contains(function ($value) use ($credentials) {
            $this->errorType = !$this->authService->userExists([$value => $credentials[$this->username()]])
                ? self::USERNAME_ERROR
                : self::PASSWORD_ERROR;
            return $this->guard()->attempt([
                $value => $credentials[$this->username()],
                'password' => $credentials['password']
            ]);
        });
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @author: King
     * @version: 2019/7/25 18:15
     */
    public function refresh()
    {
        return $this->successWithData($this->tokenResponse($this->guard()->refresh()));
    }

    /**
     * @param $token
     * @return array
     * @author: King
     * @version: 2019/7/25 18:15
     */
    protected function tokenResponse($token)
    {
        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => $this->guard()->factory()->getTTL() * 60
        ];
    }

    /**
     * @return string
     * @author: King
     * @version: 2019/7/26 16:55
     */
    public function username()
    {
        return 'username';
    }
}
