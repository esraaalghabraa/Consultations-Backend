<?php

namespace App\Http\Controllers\Api\V1\Expert;

use App\Mail\EmailVerification;
use App\Models\Expert;
use App\Models\ExpertDate;
use App\Models\ExpertExperience;
use App\Models\WorkTime;
use App\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controller;

class AuthExpertController extends Controller
{
    use ResponseTrait;

    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'abilities:expert,access'])
            ->except(['register', 'sendCode', 'verifiedEmail', 'login', 'refreshToken']);
        $this->middleware(['auth:sanctum', 'ability:expert,refresh'])->only('refreshToken');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => ['required', 'string', 'min:6', 'max:20'],
            'email' => ['required', 'string', 'email', 'unique:experts,email'],
            'phone' => ['required', 'string', 'min:10', 'max:15', 'unique:experts,phone'],
            'password' => ['required', 'string', 'min:6', 'max:20']
        ]);
        if ($validator->fails())
            return $this->failedResponse($validator->errors()->first());
        $otp = mt_rand(100000, 999999);
        $details = ['full_name' => $request->full_name, 'otp' => $otp];
        try {
            Mail::to($request->email)->send(new EmailVerification($details));
        } catch (\Exception $exception) {
            return $this->failedResponse('ex: ' . $exception);
        }
        Expert::create([
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
            'email' => ['required', 'string', 'email', 'exists:experts,email,deleted_at,NULL'],
        ]);
        if ($validator->fails())
            return $this->failedResponse($validator->errors()->first());
        $expert = Expert::where('email', $request->email)->first();
        $otp = mt_rand(100000, 999999);
        $details = ['full_name' => $expert->full_name, 'otp' => $otp];
        try {
            Mail::to($request->email)->send(new EmailVerification($details));
        } catch (\Exception $exception) {
            return $this->failedResponse();
        }
        $expert->update([
            'otp' => $otp
        ]);
        $expert->save();
        return $this->successResponse();
    }

    public function verifiedEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'exists:experts,email,deleted_at,NULL'],
            'code' => ['required', 'numeric']
        ]);
        if ($validator->fails())
            return $this->failedResponse($validator->errors()->first());
        $expert = Expert::where('email', $request->email)->first();
        if ($expert->updated_at < now()->subMinutes(15)) {
            $expert->otp = null;
            $expert->save();
            return $this->failedResponse('Invalid code');
        }

        if ($expert->otp != $request->code) {
            return $this->failedResponse('Invalid code2');
        }
        if (!$expert->markEmailAsVerified()) {
            $expert->markEmailAsVerified();
        }
        $expert->otp = null;
        $expert->save();
        $expert->token = $expert->createToken('accessToken', ['expert', 'access'], now()->addDays(6))->plainTextToken;
        $expert->refresh_token = $expert->createToken('refreshToken', ['expert', 'refresh'], now()->addDays(12))->plainTextToken;

        return $this->successResponse($expert);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['string', 'exists:experts,email,deleted_at,NULL'],
            'phone' => ['string', 'exists:experts,phone,deleted_at,NULL'],
            'password' => ['required', 'string', 'min:6', 'max:20']
        ]);
        if ($validator->fails())
            return $this->failedResponse($validator->errors()->first());
        $expert = Expert::query()->where('email', $request->email)->orWhere('phone', $request->phone)->first();
        if (!$expert || !Hash::check($request->password, $expert->password)) {
            return $this->failedResponse('The provided credentials are incorrect.');
        }

        $otp = mt_rand(100000, 999999);
        $details = ['full_name' => $expert->full_name, 'otp' => $otp];
        try {
            Mail::to($expert->email)->send(new EmailVerification($details));
        } catch (\Exception $exception) {
            return $this->failedResponse();
        }
        $expert->update([
            'otp' => $otp
        ]);
        $expert->save();

        return $this->successResponse();
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'exists:experts,email,deleted_at,NULL'],
            'password' => ['required', 'string', 'min:6', 'max:20']
        ]);
        if ($validator->fails())
            return $this->failedResponse($validator->errors()->first());
        $expert = Expert::where('email', $request->email)->first();
        $expert->update([
            'password' => Hash::make($request->password)
        ]);
        $expert->save();
        return $this->successResponse();
    }

    public function refreshToken(Request $request)
    {
        try {
            $request->user()->tokens()->delete();
            $token = $request->user()->createToken('accessToken', ['expert', 'access'], now()->addDay())->plainTextToken;
            $r_token = $request->user()->createToken('refreshToken', ['expert', 'refresh'], now()->addDays(6))->plainTextToken;
            return $this->successResponse(['token' => $token, 'refresh_token' => $r_token]);
        } catch (\Exception $e) {
            return $this->failedResponse('Server failure : ' . $e, 500);
        }
    }


    public function completeInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'string'],
            'birthdate' => ['required', 'date'],
            'about' => ['required', 'string'],
            'category_id' => ['required', 'exists:categories,id,deleted_at,NULL'],
            'work_times' => ['required', 'array', 'min:1'],
            'work_times.*.day_id' => ['required', 'exists:days,id,deleted_at,NULL'],
            'work_times.*.start_time_id' => ['required', 'exists:hours,id,deleted_at,NULL'],
            'work_times.*.end_time_id' => ['required', 'exists:hours,id,deleted_at,NULL'],
            'experiences' => ['required', 'array', 'min:1'],
            'experiences.*.id' => ['required', 'exists:experiences,id,deleted_at,NULL'],
            'experiences.*.experience_years' => ['required', 'numeric'],
            'communication_types' => ['required', 'array', 'min:1'],
            'communication_types.*.id' => ['required', 'exists:communication_types,id,deleted_at,NULL'],
            'communication_types.*.cost_appointment' => ['required', 'numeric'],
        ]);
        if ($validator->fails())
            return $this->failedResponse($validator->errors()->first());
        $expert = Expert::find(Auth::user()->id);
        $expert->update([
            'address' => $request->address,
            'gender' => $request->gender,
            'birthdate' => $request->birthdate,
            'about' => $request->about,
            'category_id' => $request->category_id,
            'is_complete_data' => 1,
        ]);
        $expert->save();
        foreach ($request->work_times as $item) {
            WorkTime::create([
                'expert_id' => $expert->id,
                'day_id' => $item['day_id'],
                'start_time_id' => $item['start_time_id'],
                'end_time_id' => $item['end_time_id'],
            ]);
            for ($i = $item['start_time_id']; $i <= $item['end_time_id']; $i++) {
                ExpertDate::create([
                    'expert_id' => $expert->id,
                    'hour_id' => $i,
                    'day_id' => $item['day_id'],
                ]);
            }
        }
        foreach ($request->experiences as $item) {
            ExpertExperience::create([
                'expert_id' => $expert->id,
                'experience_years' => $item['experience_years'],
                'experience_id' => $item['experience_id'],
            ]);
        }
        foreach ($request->communication_types as $item) {
            ExpertExperience::create([
                'expert_id' => $expert->id,
                'cost_appointment' => $item['cost_appointment'],
                'communication_type_id' => $item['communication_type_id'],
            ]);
        }

        return $this->successResponse($expert);
    }

    public function logout()
    {
        Auth::user()->currentAccessToken()->delete();
        return $this->successResponse();
    }
}
