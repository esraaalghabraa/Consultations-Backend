<?php

namespace App\Http\Controllers\Api\V1\Guest;

use App\Mail\EmailVerification;
use App\Models\Role;
use App\Models\User;
use App\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    use ResponseTrait;

    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'abilities:user,access'])
            ->except(['register', 'sendCode', 'verifiedEmail', 'login', 'refreshToken', 'getUser']);
        $this->middleware(['auth:sanctum', 'ability:user,refresh'])->only('refreshToken');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => ['required', 'string', 'min:6', 'max:20'],
            'last_name' => ['required', 'string', 'min:6', 'max:20'],
            'email' => ['required', 'string', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'max:20'],
            'is_expert' => ['required', 'boolean']
        ]);
        if ($validator->fails())
            return $this->failedResponse($validator->errors()->first());
        $otp = mt_rand(100000, 999999);
        $details = ['full_name' => $request->first_name, 'otp' => $otp];
        try {
            Mail::to($request->email)->send(new EmailVerification($details));
        } catch (\Exception $exception) {
            return $this->failedResponse();
        }
        $user = User::create([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'otp' => $otp
        ]);
        if ($request->is_expert)
            $role = Role::where('name', 'expert')->first();
        else
            $role = Role::where('name', 'customer')->first();
        $user->addRole($role);
        return $this->successResponse();
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['string', 'exists:users,email,deleted_at,NULL'],
            'password' => ['required', 'string', 'min:6', 'max:20']
        ]);
        if ($validator->fails())
            return $this->failedResponse($validator->errors()->first());
        if (!auth()->validate($request->only('password', 'email'))) {
            return $this->failedResponse('The provided credentials are incorrect.');
        }
        $user = User::where('email', $request->email)->first();
        return $this->getUser($user);
    }

    public function sendCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email', 'exists:users,email,deleted_at,NULL'],
        ]);
        if ($validator->fails())
            return $this->failedResponse($validator->errors()->first());
        $user = User::where('email', $request->email)->first();
        $currentTime = now();
        // Check if the user is allowed to request a new OTP
        if ($user->otp_last_sent_at && $currentTime->lessThan($this->calculateNextAllowedTime($user))) {
            return $this->failedResponse('You must wait before requesting a new OTP');
        }
        // Generate OTP
        $otp = mt_rand(100000, 999999);
        $details = ['full_name' => $user->first_name, 'otp' => $otp];
        try {
            Mail::to($request->email)->send(new EmailVerification($details));
        } catch (\Exception $exception) {
            return $this->failedResponse();
        }
        $user->update([
            'otp' => $otp,
            'otp_last_sent_at' => $currentTime,
            'otp_resend_count' => $user->otp_resend_count + 1,
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
        $user = User::where('email', $request->email)->where('otp', $request->otp)->first();
        if ($user->otp_last_sent_at < now()->subMinutes(15)) {
            $user->otp = null;
            return $this->failedResponse('Invalid code');
        }
        if (!$user->markEmailAsVerified()) {
            $user->markEmailAsVerified();
            $user->otp = null;
            $user->save();
        }
        return $this->getUser($user);
    }

    public function resetPassword(Request $request)
    {
        $user = Auth::user();
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

    private function calculateNextAllowedTime(User $user)
    {
        $resendCount = $user->otp_resend_count;

        switch ($resendCount) {
            case 1:
                return now()->addMinute();
            case 2:
                return now()->addMinutes(5);
            case 3:
                return now()->addMinutes(15);
            case 4:
                return now()->addMinutes(30);
            case 5:
                return now()->addHour();
            default:
                return now()->addDay();
        }
    }

    /**
     * @param $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUser($user): \Illuminate\Http\JsonResponse
    {
        $user->role = $user->roles[0]['name'];
        Arr::forget($user, 'roles');
        $user->token = $user->createToken('accessToken', ['user', 'access'], now()->addDays(6))->plainTextToken;
        $user->refresh_token = $user->createToken('refreshToken', ['user', 'refresh'], now()->addDays(12))->plainTextToken;
        return $this->successResponse($user);
    }
}
