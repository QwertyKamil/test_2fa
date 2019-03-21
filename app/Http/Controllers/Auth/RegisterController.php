<?php

namespace App\Http\Controllers\Auth;

use App\Authy\Service;
use App\User;
use Sentinel;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use Session;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/two-factor';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Service $authy)
    {
        $this->authy = $authy;
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        $credentials = [
            'email' => $data['email'],
            'password' => $data['password'],
        ];
        $user = Sentinel::registerAndActivate($credentials);
        $user->update([
            'name' => $data['name'],
            'country_code' => $data['country_code'],
            'phone_number' => $data['phone_number'],
        ]);
        return $user;
    }

    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        event(new Registered($user = $this->create($request->all())));
        session([ 'id'=>$user->id,'register'=>true]);

        $authy_id = $this->authy->register($user->email, $user->phone_number, $user->country_code);

        $user->updateAuthyId($authy_id);

        if ($this->authy->verifyUserStatus($authy_id)->registered)
            $message = "Open Authy app in your phone to see the verification code";
        else {
            $this->authy->sendToken($authy_id);
            $message = "You will receive an SMS with the verification code";
        }

        return $this->registered($request, $user) ?: redirect($this->redirectPath())->with('message', $message);;
    }

    public function getTwoFactor()
    {
        if (!session('id') || !session('register')) {
            return redirect('/login')->with('message', 'Try again');
        }
        session()->forget('register');
        $message = session('message');

        return view('auth/two-factor', ['message' => $message]);
    }
}
