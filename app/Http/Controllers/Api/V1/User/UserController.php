<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Day;
use App\Models\Expert;
use App\Models\ExpertDate;
use App\Models\Favorite;
use App\Models\Hour;
use App\Models\User;
use App\ResponseTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    use ResponseTrait;

    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'abilities:user,access']);
    }

    public function getProfile()
    {
        return $this->successResponse(Auth::user());
    }

    public function editProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address' => ['string', 'max:255'],
            'gender' => ['string'],
            'birthdate' => ['date'],
        ]);
        if ($validator->fails()) {
            return $this->failedResponse($validator->errors()->first());
        }
        $user = User::find(Auth::user()->id);
        $user->update([
            'address' => $request->address,
            'gender' => $request->gender,
            'birthdate' => $request->birthdate,
        ]);
        $user->save();
        return $this->successResponse(Auth::user());
    }

    public function getFavorite()
    {
        $favorite = User::with('favoriteExperts')->find(Auth::user()->id);
        return $this->successResponse($favorite);
    }

    public function getAppointments()
    {
        $appointments = Appointment::query()->where('user_id', Auth::user()->id);
        $UpComing = $appointments->where('status', 0)->orWhere('status', 1)->get();
        $Past = $appointments->where('status', 2)->get();
        return $this->successResponse([
            $UpComing, $Past
        ]);
    }

    public function getHistory()
    {

    }

}
