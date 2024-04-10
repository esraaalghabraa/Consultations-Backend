<?php

namespace App\Http\Controllers\Api\V1\User;

use Illuminate\Routing\Controller;
use App\Mail\EmailVerification;
use App\Models\Expert;
use App\Models\User;
use App\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthUserController extends Controller
{
    use ResponseTrait;

    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'abilities:user,access'])
            ->except(['register', 'sendCode', 'verifiedEmail', 'login', 'refreshToken']);
        $this->middleware(['auth:sanctum', 'ability:user,refresh'])->only('refreshToken');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => ['required', 'string', 'min:6', 'max:20'],
            'email' => ['required', 'string', 'email', 'unique:users,email'],
            'phone' => ['required', 'string', 'min:10', 'max:15', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:6', 'max:20']
        ]);
        if ($validator->fails())
            return $this->failedResponse($validator->errors()->first());
        $otp = mt_rand(100000, 999999);
        $details = ['full_name' => $request->full_name, 'otp' => $otp];
        try {
            Mail::to($request->email)->send(new EmailVerification($details));
        } catch (\Exception $exception) {
            return $this->failedResponse();
        }
        User::create([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'otp' => $otp
        ]);
        return $this->successResponse();
    }

    public function sendCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email', 'exists:users,email,deleted_at,NULL'],
        ]);
        if ($validator->fails())
            return $this->failedResponse($validator->errors()->first());
        $user = User::where('email', $request->email)->first();
        $otp = mt_rand(100000, 999999);
        $details = ['full_name' => $user->full_name, 'otp' => $otp];
        try {
            Mail::to($request->email)->send(new EmailVerification($details));
        } catch (\Exception $exception) {
            return $this->failedResponse();
        }
        $user->update([
            'otp' => $otp
        ]);
        $user->save();
        return $this->successResponse();
    }

    public function verifiedEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'exists:users,email,deleted_at,NULL'],
            'code' => ['required', 'numeric']
        ]);
        if ($validator->fails())
            return $this->failedResponse($validator->errors()->first());
        $user = User::where('email', $request->email)->first();
        if ($user->updated_at < now()->subMinutes(15)) {
            $user->otp = null;
            return $this->failedResponse('Invalid code');
        }
        if ($user->otp === $request->code) {
            return $this->failedResponse('Invalid code');
        }
        if (!$user->markEmailAsVerified()) {
            $user->markEmailAsVerified();
            $user->otp = null;
            $user->save();
        }
        $user->token = $user->createToken('accessToken', ['user', 'access'], now()->addDay())->plainTextToken;
        $user->refresh_token = $user->createToken('refreshToken', ['user', 'refresh'], now()->addDays(6))->plainTextToken;
        return $this->successResponse($user);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['string', 'exists:users,email,deleted_at,NULL'],
            'phone' => ['string', 'exists:users,phone,deleted_at,NULL'],
            'password' => ['required', 'string', 'min:6', 'max:20']
        ]);
        if ($validator->fails())
            return $this->failedResponse($validator->errors()->first());
        $user = Expert::where('email', $request->email)->orWhere('phone', $request->phone)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->failedResponse('The provided credentials are incorrect.');
        }

        $otp = mt_rand(100000, 999999);
        $details = ['full_name' => $user->full_name, 'otp' => $otp];
        try {
            Mail::to($user->email)->send(new EmailVerification($details));
        } catch (\Exception $exception) {
            return $this->failedResponse();
        }
        $user->update([
            'otp' => $otp
        ]);
        $user->save();

        return $this->successResponse();
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'exists:users,email,deleted_at,NULL'],
            'password' => ['required', 'string', 'min:6', 'max:20']
        ]);
        if ($validator->fails())
            return $this->failedResponse($validator->errors()->first());
        $user = User::where('email', $request->email)->first();
        $user->update([
            'password' => Hash::make($request->password)
        ]);
        $user->save();
        return $this->successResponse();
    }

    public function refreshToken(Request $request)
    {
        try {
            $request->user()->tokens()->delete();
            $token = $request->user()->createToken('accessToken', ['user', 'access'], now()->addDay())->plainTextToken;
            $r_token = $request->user()->createToken('refreshToken', ['user', 'refresh'], now()->addDays(6))->plainTextToken;
            return $this->successResponse(['token' => $token, 'refresh_token' => $r_token]);
        } catch (\Exception $e) {
            return $this->failedResponse('Server failure : ' . $e, 500);
        }
    }

    public function logout()
    {
        Auth::user()->currentAccessToken()->delete();
        return $this->successResponse();
    }
}
