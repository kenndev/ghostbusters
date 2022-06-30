<?php

namespace App\Http\Controllers;

use App\Http\Requests\Login;
use App\Http\Requests\updateProfile;
use App\Http\Requests\UserRequest;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Auth;

use App\Models\User;

use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Mail\Message;

class AuthController extends Controller
{
    public function register(UserRequest $request)
    {

        $post_data = $request->all();

        $user = User::create([
            'first_name' => $post_data['first_name'],
            'last_name' => $post_data['first_name'],
            'email' => $post_data['email'],
            'password' => Hash::make($post_data['password']),
        ]);

        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function login(Login $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'error' => 'Login information is invalid.'
            ], 401);
        }

        $user = User::where('email', $request['email'])->firstOrFail();
        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function updateProfile(updateProfile $request)
    {
        $user = User::findorFail($request->input('user_id'));
        $user->first_name = $request->input('first_name');
        $user->last_name = $request->input('last_name');
        $user->phonenumber = $request->input('phonenumber');
        $user->save();
        return response()->json([
            'message' => 'Profile was updated successfully',
        ]);
    }

    public function forgot_password(Request $request)
    {
        $input = $request->all();
        $rules = array(
            'email' => "required|email",
        );
        $validator = Validator::make($input, $rules);
        if ($validator->fails()) {
            $arr = array("status" => 400, "message" => $validator->errors()->first(), "data" => array());
        } else {
            try {
                $response = Password::sendResetLink($request->only('email'), function (Message $message) {
                    $message->subject($this->getEmailSubject());
                });
                switch ($response) {
                    case Password::RESET_LINK_SENT:
                        return response()->json(array("status" => 200, "message" => trans($response), "data" => array()));
                    case Password::INVALID_USER:
                        return response()->json(array("status" => 400, "message" => trans($response), "data" => array()));
                }
            } catch (\Swift_TransportException $ex) {
                $arr = array("status" => 400, "message" => $ex->getMessage(), "data" => []);
            } catch (Exception $ex) {
                $arr = array("status" => 400, "message" => $ex->getMessage(), "data" => []);
            }
        }
        return response()->json($arr);
    }

    public function sendEmail(Request $request)
    {
        $this->validate($request, [
            'email'    => 'required',
        ]);

        $user =  User::where('email', $request->input('email'))->first();

        $credentials = ['email' => $request->input('email')];
        $response = Password::sendResetLink($credentials, function (Message $message) {
            $message->subject($this->getEmailSubject());
        });

        switch ($response) {
            case Password::RESET_LINK_SENT:
                return response()->json([
                    'status'        => 'success',
                    'message' => 'Password reset link send into mail.',
                    'data' => ''
                ], 201);
            case Password::INVALID_USER:
                return response()->json([
                    'status'        => 'failed',
                    'message' =>   'Unable to send password reset link.'
                ], 401);
        }

        // return response()->json([
        //     'status'        => 'failed',
        //     'message' =>   'user detail not found!'
        // ], 401);
    }

    public function resetEmail(Request $request)
    {
        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ?response()->json([
                'status'        => __($status),
            ], 401)
            : response()->json([
                'status'        => __($status),
            ], 401);

            
    }
}
