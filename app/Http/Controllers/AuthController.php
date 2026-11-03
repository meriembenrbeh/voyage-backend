<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $input = $request->only('email', 'password');
        $jwt_token = null;
        if (!$jwt_token = JWTAuth::attempt($input)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Email or Password',
            ], Response::HTTP_UNAUTHORIZED);
        }
        return response()->json([
            'success' => true,
            'token' => $jwt_token,
            'user' => Auth::user(),
        ]);
    }
    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|confirmed|min:6',
            'role' => 'required|string|in:admin,user', // Role doit êtreadmin ou user

            'avatar' => 'nullable|string', // Avatar optionnel et doit êtreune chaîne de caractères
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        $user = User::create(array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password)],

            ['isActive' => false] // Initialiser isActive à false
        ));
        // Envoyer l'email de vérification
        $verificationUrl = route('verify.email', ['email' => $user->email]);
        Mail::send([], [], function ($message) use ($user, $verificationUrl) {
            $message->to($user->email)
                ->subject('Verification de votre email')
                ->html("<h2>{$user->name}! Merci de vous être inscrit surnotre site</h2>

<h4>Veuillez vérifier votre email pourcontinuer...</h4>

<a href='{$verificationUrl}'>Cliquez ici</a>");
        });
        return response()->json([
            'message' => 'User successfully registered. Please verify your
email.',
            'user' => $user
        ], 201);
        return response()->json([
            'message' => 'User successfully registered. Please verify your

email.',

            'user' => $user
        ], 201);
    }
    //Method verify Email
    public function verifyEmail(Request $request)
    {
        $user = User::where('email', $request->query('email'))->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }
        if ($user->isActive) {
            return response()->json([
                'success' => true,
                'message' => 'Account already activated'
            ]);
        }

        $user->isActive = true;
        $user->save();
        return response()->json([
            'success' => true,
            'message' => 'Account activated successfully'
        ]);
    }
    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        try {
            // Check if the token is valid and associated with a user
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'msg' => 'User not found or token invalid.',
                ], 401);
            }

            // Invalidate the token
            JWTAuth::invalidate(JWTAuth::parseToken());

            return response()->json([
                'status' => 'success',
                'msg' => 'Logged out successfully.',
            ], 200);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'msg' => 'Failed to logout, token invalid.',
            ], 401); // Use 401 for invalid token
        }
    }


    /**
     * Return auth guard
     */
    private function guard()
    {
        return Auth::guard();
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        try {
            $newToken = JWTAuth::refresh(JWTAuth::getToken());
            return $this->createNewToken($newToken);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'msg' => 'Failed to refresh token, token invalid.',
            ], 401);
        }
    }
    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile()
    {
        return response()->json(auth('api')->user());
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
            'user' => auth('api')->user()
        ]);
    }
}
