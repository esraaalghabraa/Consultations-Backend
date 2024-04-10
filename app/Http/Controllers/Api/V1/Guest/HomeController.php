<?php

namespace App\Http\Controllers\Api\V1\Guest;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Expert;
use App\Models\SubCategory;
use App\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use function Laravel\Prompts\select;

class HomeController extends Controller
{
    use ResponseTrait;

    public function getHomeDate()
    {
        $categories = Category::get();
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
            'categories' => $categories,
            'highestRecommendedExperts' => $highestRecommendedExperts,
            'highestRatedExperts' => $highestRatedExperts,
        ]);
    }

    public function getRecommendedExperts(Request $request)
    {
        $records = $request->records_number ?? 10;
        $highestRecommendedExperts = Expert::
        where('recommended_number', '>', 0)
            ->orderByDesc('recommended_number')
            ->latest()->paginate($records);
        return $this->successResponse($highestRecommendedExperts);
    }

    public function getTopExperts()
    {
        $records = $request->records_number ?? 10;
        $highestRatedExperts = Expert::
        where('rating', '>', 2)
            ->orderByDesc('rating')
            ->latest()->paginate($records);
        return $this->successResponse($highestRatedExperts);
    }

    public function getSubCategoriesWithExperts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => ['required', 'exists:categories,id,deleted_at,NULL'],
        ]);
        if ($validator->fails())
            return $this->failedResponse($validator->errors()->first());
        $sub_categories = SubCategory::query()->where('category_id', $request->category_id);

        $sub_categories_list = $sub_categories->get();
        $sub_categories_with_experts = $sub_categories->with('experts')->get();
        $experts_list = $sub_categories_with_experts->flatMap(function ($element) {
            return $element->experts;
        });
        return $this->successResponse([
            'sub_categories' => $sub_categories_list,
            'experts' => $experts_list,
        ]);
    }

    public function getSubCategoryExperts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sub_category_id' => ['required', 'exists:sub_categories,id,deleted_at,NULL'],
        ]);
        if ($validator->fails())
            return $this->failedResponse($validator->errors()->first());

        $sub_categories = SubCategory::with('experts')
            ->find($request->sub_category_id);
        return $this->successResponse($sub_categories->experts);

    }

    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'query' => ['required', 'string', 'max:255'],
        ]);
        if ($validator->fails())
            return $this->failedResponse($validator->errors()->first());

        $searchQuery = $request->input('query');

        // Search for experts based on department name or expertise name
        $experts = Expert::where('full_name', 'like',  $searchQuery )
            ->orWhereHas('category', function ($query) use ($searchQuery) {
                $query->where('name', 'like',  $searchQuery );
            })->orWhereHas('subCategories', function ($query) use ($searchQuery) {
                $query->where('name', 'like',  $searchQuery );
            })->orWhereHas('experiences', function ($query) use ($searchQuery) {
                $query->where('name', 'like',  $searchQuery );
            })
            ->get();

        return $this->successResponse($experts);
    }


    public function getExpertDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'expert_id' => ['required', 'exists:experts,id,deleted_at,NULL']
        ]);
        if ($validator->fails())
            return $this->failedResponse();
        $expert = Expert::with(['experiences'=>function($q){
            return $q->select('experiences.id','name')->with('experienceYears');
        }])
            ->with(['subCategories' => function ($qu) {
                return $qu->select('sub_categories.id', 'name');
            }])
            ->find($request->expert_id);
        return $this->successResponse($expert);
    }
}
