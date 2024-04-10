<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Expert;
use App\Models\Favorite;
use App\Models\Recommendation;
use App\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ExpertController extends Controller
{
    use ResponseTrait;

    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'abilities:user,access']);
    }

    public function changeFavorite(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'expert_id' => ['required', 'exists:experts,id,deleted_at,NULL']
        ]);
        if ($validator->fails())
            return $this->failedResponse();
        $favorite = Favorite::where('user_id', Auth::user()->id)->where('expert_id', $request->expert_id)->first();
        if (!$favorite) {
            Favorite::create([
                'user_id' => Auth::user()->id,
                'expert_id' => $request->expert_id,
            ]);
            return $this->successResponse(['isFavorite' => 1]);

        } else {
            $favorite->delete();
            return $this->successResponse(['isFavorite' => 0]);
        }
    }

    public function addReview(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'appointment_id' => ['required', 'exists:appointments,id,deleted_at,NULL'],
            'rating' => ['required', 'numeric', 'min:0', 'max:5'],
            'isRecommended' => ['required', 'boolean'],
        ]);
        if ($validator->fails())
            return $this->failedResponse();
        $appointment = Appointment::find($request->appointment_id);
        $appointment->rating = $request->rating;
        $appointment->is_recommended = $request->isRecommended;
        $appointment->save();

        $count_users_recommended = Appointment::select('user_id')->where('expert_id', $appointment->expert->id)
            ->where('is_recommended', true)
            ->groupBy('user_id')
            ->get()
            ->count();
        $count_users_rating = Appointment::select('user_id')->where('expert_id', $appointment->expert->id)
            ->where('rating', '>', 0)
            ->groupBy('user_id')
            ->get()
            ->count();
        $expert = $appointment->expert;
        $appointments = $expert->appointments;
        if ($appointments->isNotEmpty()) {
            $sum = 0;
            foreach ($appointments as $appointment) {
                $sum += $appointment->rating;
            }
        }
        $res1 = $sum / count($appointments);
        $expert->rating = $res1 / $count_users_rating;
        $expert->rating_number = $res1 / $count_users_rating;
        $expert->recommended_number = $count_users_recommended;
        $expert->save();
        return $this->successResponse();
    }

    public function addBooking(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'expert_id' => ['required', 'exists:experts,id,deleted_at,NULL'],
            'date_id' => ['required', 'exists:expert_dates,id,deleted_at,NULL'],
            'communication_type_id' => ['required', 'exists:communication_types,id'],
            'problem' => ['string'],
        ]);
        if ($validator->fails())
            return $this->failedResponse();
        $expert = Expert::find($request->expert_id);
        Appointment::create([
            'user_id' => Auth::user()->id,
            'expert_id' => $request->expert_id,
            'date_id' => $request->date_id,
            'communication_type_id' => $request->communication_type_id,
            'problem' => $request->problem,
        ]);
        $expert->consultancies_number += 1;
        $expert->save();
        return $this->successResponse();
    }

}
