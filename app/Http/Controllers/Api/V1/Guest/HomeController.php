<?php

namespace App\Http\Controllers\Api\V1\Guest;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Expert;
use App\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{
    use ResponseTrait;

    public function getHomeDate()
    {
        $categories = Category::whereHas('subCategories')->with('subCategories')->whereHas('experts')->get();
        $highestRecommendedExperts = Expert::
        where('recommended_number', '>', 0)
            ->orderByDesc('recommended_number')
            ->limit(10)
            ->get();
        $highestRatedExperts = Expert::
        where('rating', '>', 2)
            ->orderByDesc('rating')
            ->limit(10)
            ->get();
        return $this->successResponse([
            'categories' => $categories??[],
            'highestRecommendedExperts' => $highestRecommendedExperts??[],
            'highestRatedExperts' => $highestRatedExperts??[],
        ]);
    }

    public function getMainCategoryDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => ['required', 'exists:categories,id,deleted_at,NULL'],
        ]);
        if ($validator->fails())
            return $this->failedResponse($validator->errors()->first());
        $category = Category::with('subCategories')->with('experts')->find($request->category_id);
        return $this->successResponse([
            'sub_categories' => $category->sub_categories??[],
            'experts' => $category->experts??[],
        ]);
    }


    function getExperts(Request $request)
    {
        $limit = $request->limit ? $request->limit : 10;
        $page = $request->page ? $request->page : null;
        $validator = Validator::make($request->all(), [
            'experts_type' => ['required', 'string', 'max:255'],
            'main_category_id' => ['exists:categories,id'],
            'sub_category_id' => ['exists:sub_categories,id'],
        ]);
        if ($validator->fails())
            return $this->failedResponse($validator->errors()->first());
        $experts = Expert::query();
        if ($request->main_category_id) {
            $experts = $experts->where('category_id', $request->main_category_id);
        }
        if ($request->sub_category_id) {
            $experts = $experts->whereHas('subCategories', function ($query) use ($request) {
                $query->where('sub_categories.id', $request->sub_category_id);
            });
        }
        if ($request->experts_type == 'recommended_experts') {
            $experts = $experts->where('recommended_number', '>', 0)
                ->orderByDesc('recommended_number');
        }
        if ($request->experts_type == 'top_experts') {
            $experts = $experts->where('rating', '>', 2)
                ->orderByDesc('rating');
        }
        return $this->successResponse($experts->paginate($limit, ['*'], 'page', $page));
    }

    public function search(Request $request)
    {
        $limit = $request->limit ? $request->limit : 10;
        $page = $request->page ? $request->page : null;
        $validator = Validator::make($request->all(), [
            'query' => ['required', 'string', 'max:255'],
        ]);
        if ($validator->fails())
            return $this->failedResponse($validator->errors()->first());

        $searchQuery = $request->input('query');

        // Search for experts based on department name or expertise name
        $experts = Expert::query();
        $experts = $experts->where('full_name', 'like', '%' . $searchQuery . '%')
            ->orWhereHas('category', function ($query) use ($searchQuery) {
                $query->where('name', 'like', '%' . $searchQuery . '%');
            })->orWhereHas('subCategories', function ($query) use ($searchQuery) {
                $query->where('name', 'like', '%' . $searchQuery . '%');
            })->orWhereHas('experiences', function ($query) use ($searchQuery) {
                $query->where('name', 'like', '%' . $searchQuery . '%');
            });

        return $this->successResponse($experts->paginate($limit, ['*'], 'page', $page));
    }


    public function getExpertDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'expert_id' => ['required', 'exists:experts,id,deleted_at,NULL']
        ]);
        if ($validator->fails())
            return $this->failedResponse();
        $expert = Expert::with(['experiences' => function ($q) {
            return $q->select('experiences.id', 'name')->with('experienceYears');
        }])
            ->with(['subCategories' => function ($qu) {
                return $qu->select('sub_categories.id', 'name');
            }])
            ->find($request->expert_id);
        return $this->successResponse($expert);
    }
}
