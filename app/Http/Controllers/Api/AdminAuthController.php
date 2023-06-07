<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class AdminAuthController extends Controller
{



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
       //php artisan passport:client --password
            $response = Http::asForm()->post('http://127.0.0.1:81/oauth/token', [
                'grant_type' => 'password',
                'client_id' => '3',
                'client_secret' => 'bvxzoNaFm0gfJjXrVBx7i8bsIkw905H6HbDYbSwb',
                'username' => $request->input('email'),
                'password' => $request->input('password'),
                'scope' => '*',
            ]);
            // return $response;

        //    $admin = Admin::where('email', $request->input('email'))->first();
        //     if ($admin) {
        //         if (Hash::check($request->input('password'), $admin->password)) {
                    $admin = Admin::where('email', '=', $request->input('email'))->first();
        //             // $this->revokePreviousTokens($user->id, 2);

                    $admin->setAttribute('token', $response->json()['access_token']);
                    return response()->json([
                        'message' => 'Logged in successfully',
                        'data' => $admin,
                    ], Response::HTTP_OK);
        //         } else {
        //             return response()->json(['message' => 'Login failed, wrong credentials'], Response::HTTP_BAD_REQUEST);
        //         }
        //     } else {
        //         return response()->json(['message' => 'Login failed, wrong credentials'], Response::HTTP_BAD_REQUEST);
        //     }
        } catch (Exception $ex) {
            // return $response;
            return response()->json(
                ['message' =>$ex->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        }
    }



 public function logout()
    {
        $revoked = auth('admin-api')->user()->token()->revoke();
        return response()->json(
            [
                'message' => $revoked ? 'Logged out successfully' : 'Logout failed!',
            ],
            $revoked ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST
        );
    }
}
