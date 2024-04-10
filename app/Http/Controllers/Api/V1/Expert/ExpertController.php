<?php

namespace App\Http\Controllers\Api\V1\Expert;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CommunicationType;
use App\Models\Day;
use App\Models\Expert;
use App\Models\ExpertCommunications;
use App\Models\ExpertDate;
use App\Models\ExpertExperience;
use App\Models\Hour;
use App\Models\SubCategory;
use App\Models\SubCategoryExpert;
use App\Models\WorkTime;
use App\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ExpertController extends Controller
{
    use ResponseTrait;

    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'abilities:expert,access']);
    }

    public function getSubCategoriesWithExperiences(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => ['required', 'exists:categories,id,deleted_at,NULL'],
        ]);
        if ($validator->fails())
            return $this->failedResponse($validator->errors()->first());
        $sub_categories = SubCategory::query()->where('category_id', $request->category_id);

        $sub_categories_list = $sub_categories->get();
        $sub_categories_with_experiences = $sub_categories->with('experiences')->get();
        $experiences_list = $sub_categories_with_experiences->flatMap(function ($element) {
            return $element->experiences;
        });
        return $this->successResponse([
            'sub_categories' => $sub_categories_list,
            'experiences' => $experiences_list,
        ]);
    }

    public function getDaysAndHours()
    {
        $hours = Hour::get();
        $days = Day::get();
        $categories = Category::get();
        $communication_types = CommunicationType::get();
        return $this->successResponse([
            'hours' => $hours,
            'days' => $days,
            'categories' => $categories,
            'communication_types' => $communication_types,
        ]);
    }


    public function getProfile()
    {
        $expert = Expert::with('subCategories')
            ->with('expertDates')->with('expertDates.hour')->with('expertDates.day')
            ->with('experiences')
            ->with('communicationTypes')
            ->with('users')
            ->with('followers')
            ->find(Auth::user()->id);

        return $this->successResponse($expert);
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
            'work_times.*.date' => ['required', 'date'],
            'experiences' => ['required', 'array', 'min:1'],
            'experiences.*.id' => ['required', 'exists:experiences,id,deleted_at,NULL'],
            'experiences.*.experience_years' => ['required', 'numeric'],
            'communication_types' => ['required', 'array', 'min:1'],
            'communication_types.*.id' => ['required', 'exists:communication_types,id,deleted_at,NULL'],
            'communication_types.*.cost_appointment' => ['required', 'numeric'],
            'sub_category_ids' => ['required', 'array', 'min:1'],
            'sub_category_ids.*' => ['required', 'exists:sub_categories,id,deleted_at,NULL'],
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
                'date' => $item['date'],
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
                'experience_id' => $item['id'],
            ]);
        }
        foreach ($request->communication_types as $item) {
            $communication = CommunicationType::find($item['id']);
            ExpertCommunications::create([
                'expert_id' => $expert->id,
                'cost_appointment' => $communication->cost + $item['cost_appointment'],
                'communication_type_id' => $item['id'],
            ]);
        }
        foreach ($request->sub_category_ids as $item) {

            SubCategoryExpert::create([
                'expert_id' => $expert->id,
                'sub_category_id' => $item,
            ]);
        }

        return $this->successResponse($expert);
    }


}
