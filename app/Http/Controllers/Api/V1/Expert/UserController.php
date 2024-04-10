<?php

namespace App\Http\Controllers\Api\V1\Expert;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Expert;
use App\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    use ResponseTrait;
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'abilities:expert,access']);
    }
    public function getAppointments(){
        $appointments = Appointment::query()->where('expert_id', Auth::user()->id);
        $UpComing = $appointments->where('status', 0)->orWhere('status', 1)->get();
        $Past = $appointments->where('status', 2)->get();
        return $this->successResponse([
            'UpComing' => $UpComing,
            'Past' => $Past,
        ]);
    }
    public function acceptAppointment(Request  $request)
    {
        $validator = Validator::make($request->all(), [
            'appointment_id' => ['required', 'exists:appointments,id,deleted_at,NULL'],
        ]);
        if ($validator->fails()) {
            return $this->failedResponse($validator->errors()->first());
        }
        $appointment = Appointment::find($request->appointment_id);
        $appointment->status = 1;
        $appointment->save();
        return $this->successResponse();
    }
    public function responseAppointment(Request  $request)
    {
        $validator = Validator::make($request->all(), [
            'appointment_id' => ['required', 'exists:appointments,id,deleted_at,NULL'],
            'response' => ['required', 'string'],
        ]);
        if ($validator->fails()) {
            return $this->failedResponse($validator->errors()->first());
        }
        $appointment = Appointment::find($request->appointment_id);
        $appointment->response = $request->response;
        $appointment->save();
        return $this->successResponse();
    }
}
