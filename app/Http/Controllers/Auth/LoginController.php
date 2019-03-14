<?php

namespace App\Http\Controllers\Auth;

use App\Authy\Service;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\User;
use App\OneTouch;
use Auth;
use Sentinel;

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
     * @return void
     */
    public function __construct(Service $authy)
    {
        $this->authy = $authy;
        $this->middleware('guest')->except('logout');
    }


    public function getTwoFactor()
    {
        $message = session('message'); //Session::get('message');

        return view('auth/two-factor', ['message' => $message]);
    }

    public function postTwoFactor(Request $request)
    {
        if (!session('password_validated') || !session('id')) {
            return redirect('/login');
        }

        if (isset($_POST['token'])) {
            $user = Sentinel::findById( session('id'));
            if ($this->authy->verifyToken($user->authy_id, $request->input('token'))) {
                //Auth::login($user);
                Sentinel::login($user);
                return redirect()->intended('/home');
            } else {
                return redirect('/two-factor')->withErrors([
                    'token' => 'The token you entered is incorrect',
                ]);
            }
        }
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $user =  Sentinel::authenticate($credentials);
        Sentinel::logout();
        if ($user) {
            //$user = User::where('email', '=', $request->input('email'))->firstOrFail();

            session(['password_validated' => true, 'id' => $user->id]);

            if ($this->authy->verifyUserStatus($user->authy_id)->registered) {
                $uuid = $this->authy->sendOneTouch($user->authy_id, 'Request to Login to Twilio demo app');

                OneTouch::create(['uuid' => $uuid]);

                session([ 'one_touch_uuid' => $uuid]);

                return response()->json(['status' => 'ok']);
            } else {
                    return response()->json(['status' => 'verify']);
                }
        } else {
            return response()->json([
                'status' => '
                failed',
                'message' => 'The email and password combination you entered is incorrect.'
            ]);
        }
    }
}
