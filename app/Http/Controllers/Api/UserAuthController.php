<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class UserAuthController extends Controller
{


    public function loginPersonal(Request $request)
    {
        $validator = Validator($request->all(), [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:3',
        ]);

        if (!$validator->fails()) {
            $user = User::where('email', $request->input('email'))->first();
            if (Hash::check($request->input('password'), $user->password)) {

                $token = $user->createToken('User');
                $user->setAttribute('token', $token->accessToken);
                return response()->json(
                    [
                        'message' => 'Logged in successfully',
                        'data' => $user
                    ],
                    Response::HTTP_OK,
                );
            } else {
                return response()->json(['message' => 'Login failed, wrong credentials'], Response::HTTP_BAD_REQUEST);
            }
        } else {
            return response()->json(['message' => $validator->getMessageBag()->first()], Response::HTTP_BAD_REQUEST);
        }
    }






    public function loginPGCT(Request $request)
    {
        $validator = Validator($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:3',
        ]);

        if (!$validator->fails()) {
            return $this->generatePgctToken($request);
        } else {
            return response()->json(['message' => $validator->getMessageBag()->first()], Response::HTTP_BAD_REQUEST);
        }
    }

    private function generatePgctToken(Request $request)
    {
        try {

            $response = Http::asForm()->post('http://127.0.0.1:81/oauth/token', [
                'grant_type' => 'password',
                'client_id' => '2',
                'client_secret' => 'lAp5MEentTOHqKaSes4xRo8Y1LGGuXrtiMzx0Od2',
                'username' => $request->input('email'),
                'password' => $request->input('password'),
                'scope' => '*',
            ]);

//            if (!$response->failed()){
//                return response()->json([
//                    'message' => 'Logged in successfully',
//                ]);
//            }else{
//                return response()->json([
//                    'message' => 'failed in successfully',
//                ]);
//            }
            $user = User::where('email', $request->input('email'))->first();
            if ($user) {
                if (Hash::check($request->input('password'), $user->password)) {
                    $user = User::where('email', '=', $request->input('email'))->first();
                    // $this->revokePreviousTokens($user->id, 2);

                    $user->setAttribute('token', $response->json()['access_token']);
                    return response()->json([
                        'message' => 'Logged in successfully',
                        'data' => $user,
                    ], Response::HTTP_OK);
                } else {
                    return response()->json(['message' => 'Login failed, wrong credentials'], Response::HTTP_BAD_REQUEST);
                }
            } else {
                return response()->json(['message' => 'Login failed, wrong credentials'], Response::HTTP_BAD_REQUEST);
            }
        } catch (Exception $ex) {
            // return $response;
            return response()->json(
                ['message' => $response->json()['message']],
                Response::HTTP_BAD_REQUEST
            );
        }
    }



    public function register(Request $request)
    {
        $validator = Validator($request->all(), [
            'name' => 'required|string|min:3|max:100',
            'email' => 'required|email|unique:users,email',
        ]);

        if (!$validator->fails()) {
            $user = new User();
            $user->name = $request->input('name');
            $user->email = $request->input('email');
            $user->password = Hash::make(12345);
            $isSaved = $user->save();

            return response()->json(
                [
                    'message' => $isSaved ? 'User created successfully' : 'Create failed!'
                ],
                $isSaved ? Response::HTTP_CREATED : Response::HTTP_BAD_REQUEST,
            );
        } else {
            return response()->json(['message' => $validator->getMessageBag()->first()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function logout()
    {
        $revoked = auth('user-api')->user()->token()->revoke();
        return response()->json(
            [
                'message' => $revoked ? 'Logged out successfully' : 'Logout failed!',
            ],
            $revoked ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST
        );
    }
}
