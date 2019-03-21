<?php

namespace App\Http\Controllers\Auth;

use App\Authy\Service;
use App\Http\Controllers\Controller;
use App\Http\Requests\TwoFactorRequest;
use Cartalyst\Sentinel\Checkpoints\ThrottlingException;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\User;
use App\OneTouch;
use Auth;
use Sentinel;
use Symfony\Component\HttpKernel\Exception\HttpException;

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

    /**
     * Where to redirect users after login.
     *
     * @var stringuse Illuminate\Http\Request;
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @param Service $authy
     */
    public function __construct(Service $authy)
    {
        $this->authy = $authy;
        $this->middleware('guest')->except('logout');
    }


    public function postTwoFactor(TwoFactorRequest $request)
    {
        if (!session('id')) {
            return redirect('/login');
        }

        $user = Sentinel::findById(session('id'));
        try {
            if ($this->authy->verifyToken($user->authy_id, $request->input('token'))) {
                //Auth::login($user);
                Sentinel::login($user);
                return redirect()->intended('/home');
            } else {
                return redirect('/login')->with([
                    'error' => 'The token you entered is incorrect',
                ]);
            }
        } catch (\Exception $e) {
            return redirect('/login')->with([
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        try {
            $user = Sentinel::authenticate($credentials);
        } catch (ThrottlingException $exception) {
            return response()->json([
                'status' => 'failed',
                'message' => $exception->getMessage(),
            ]);
        }

        Sentinel::logout();
        if ($user) {
            session(['id' => $user->id]);

            if ($this->authy->verifyUserStatus($user->authy_id)->registered) {
                $uuid = $this->authy->sendOneTouch($user->authy_id, 'Request to Login to Twilio demo app');

                OneTouch::create(['uuid' => $uuid]);

                session(['one_touch_uuid' => $uuid]);

                return response()->json(['status' => 'ok']);
            } else {
                return response()->json(['status' => 'verify']);
            }
        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'The email and password combination you entered is incorrect.'
            ]);
        }
    }

    public function sendToken(Request $request)
    {
        $user = Sentinel::findById(session('id'));
        if ($this->authy->verifyUserStatus($user->authy_id)->registered)
            $message = "Open Authy app in your phone to see the verification code";
        else {
            $this->authy->sendToken($user->authy_id);
            $message = "You will receive an SMS with the verification code";
        }
        $status = "ok";

        return response()->json(['status' => $status, 'message' => $message], 200);
    }
}
