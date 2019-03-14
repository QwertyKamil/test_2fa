<?php namespace App\Http\Controllers\Auth;

use App\OneTouch;
use Auth;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthyController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Check One Touch authorization status
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function status(Request $request)
    {
        $oneTouch = OneTouch::where('uuid', '=', session('one_touch_uuid'))->firstOrFail();
        $status = $oneTouch->status;
        if ($status == 'approved') {
            $user = Sentinel::findById(session('id'));
            Sentinel::login($user);
        }
        return response()->json(['status' => $status]);
    }

    /**
     * Public webhook for Authy
     *
     * @param Request $request
     * @return string
     */
    public function callback(Request $request)
    {
        $uuid = $request->input('uuid');
        $oneTouch = OneTouch::where('uuid', '=', $uuid)->first();
        if ($oneTouch != null) {
            $oneTouch->status = $request->input('status');
            $oneTouch->save();
            return "ok";
        }
        return "invalid uuid: $uuid";
    }
}
