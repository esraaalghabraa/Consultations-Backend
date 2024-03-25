<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Expert;
use App\Models\SubCategory;
use App\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class HomeController extends Controller
{
    use ResponseTrait;

    public function getCategories()
    {
        $categories = Category::get();
        return $this->successResponse($categories);
    }

    public function getRecommendedExperts()
    {
        $highestRecommendedExperts = Expert::
        where('recommended_number', '>', 0)
            ->orderByDesc('recommended_number')
            ->limit(10)
            ->get();
        return $this->successResponse($highestRecommendedExperts);
    }

    public function getTopExperts()
    {
        $highestRatedExperts = Expert::
        where('rating_number', '>', 0)
            ->orderByDesc('rating')
            ->limit(10)
            ->get();
        return $this->successResponse($highestRatedExperts);
    }

    public function getSubCategoriesWithExperts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => ['required', 'exists:categories,id,deleted_at,NULL'],
        ]);
        if ($validator->fails())
            return $this->failedResponse($validator->errors()->first());
        $sub_categories = SubCategory::query()->where('category_id',$request->category_id);

        $sub_categories_list=$sub_categories->get();
        $sub_categories_with_experts=$sub_categories->with('experts')->get();
        $experts_list=$sub_categories_with_experts->flatMap(function ($element){
            return $element->experts;
        });
        return $this->successResponse([
            'sub_categories'=>$sub_categories_list,
            'experts'=>$experts_list,
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

}
