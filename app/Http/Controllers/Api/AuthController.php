<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponser;
class AuthController extends Controller
{
    use ApiResponser;
    /**
     * Login The User
     * @param Request $request
     * @return User
     */
    public function loginUser(Request $request)
    {
        try {
            $validateUser = Validator::make($request->all(), 
            [
                'email' => 'required|email',
                'password' => 'required'
            ]);
            
            if($validateUser->fails()){
                return $this->errorResponse('validation error',$validateUser->errors(),401);
            }
            
            if(!Auth::attempt($request->only(['email', 'password']))){
                return $this->errorResponse(__('auth.password'),[],403);
            }

            $user = User::where('email', $request->email)->first();
            return $this->successResponse([['token'=>$user->createToken("API TOKEN")->plainTextToken]],'User Logged In Successfully',200);
            /*return response()->json([
                'status' => true,
                'message' => 'User Logged In Successfully',
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 200);*/

        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage(),[],500);
        }
    }
}