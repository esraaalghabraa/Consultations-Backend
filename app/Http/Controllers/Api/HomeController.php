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

    public function getAllRecommendedExperts()
    {
        $highestRecommendedExperts = Expert::
        where('recommended_number', '>', 0)
            ->orderByDesc('recommended_number')
            ->get();
        return $this->successResponse($highestRecommendedExperts);
    }

    public function getTopExperts()
    {
        $highestRatedExperts = Expert::
        where('rating_number', '>', 2)
            ->orderByDesc('rating')
            ->limit(10)
            ->get();
        return $this->successResponse($highestRatedExperts);
    }

    public function getAllTopExperts()
    {
        $highestRatedExperts = Expert::
        where('rating_number', '>', 2)
            ->orderByDesc('rating')
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

    public function search(Request $request){
        $validator = Validator::make($request->all(), [
            'query' => ['required', 'string','max:255'],
        ]);
        if ($validator->fails())
            return $this->failedResponse($validator->errors()->first());

        $searchQuery = $request->input('query');

        // Search for experts based on department name or expertise name
        $experts = Expert::where('full_name', 'like', '%' . $searchQuery . '%')
            ->orWhereHas('category', function ($query) use ($searchQuery) {
            $query->where('name', 'like', '%' . $searchQuery . '%');
        })->orWhereHas('subCategories', function ($query) use ($searchQuery) {
            $query->where('name', 'like', '%' . $searchQuery . '%');
        })->orWhereHas('experiences', function ($query) use ($searchQuery) {
            $query->where('name', 'like', '%' . $searchQuery . '%');
        })
            ->get();

        return $this->successResponse($experts);
    }
}
















//        $new_array = [
//[
//            "id"
//=>
//1,
//"full_name"
//=>
//"Alaina Breanna",
//"phone"
//=>
//"+9633477323",
//"email"
//=>
//"sabshire@example.net",
//"password"
//=>
//"$2y$12$5DBcl18t.fWHHMnYUnZ1fe9t5DD7CYOP7saKDBnapULwPp8BuqIHW",
//"address"
//=>
//"Sierra Leone",
//"about"
//=>
//"Sit et quam inventore qui.",
//"rating"
//=>
//2,
//"rating_number"
//=>
//0,
//"recommended_number"
//=>
//0,
//"consultancies_number"
//=>
//0,
//"min_range"
//=>
//6,
//"max_range"
//=>
//50,
//"category_id"
//=>
//1,
//"deleted_at"
//=>
//null,
//"created_at"
//=>
//"2024-03-25T10:25:46.000000Z",
//"updated_at"
//=>
//"2024-03-25T10:25:46.000000Z",],
//[
//            "id"
//=>
//1,
//"full_name"
//=>
//"Alaina Breanna",
//"phone"
//=>
//"+9633477323",
//"email"
//=>
//"sabshire@example.net",
//"password"
//=>
//"$2y$12$5DBcl18t.fWHHMnYUnZ1fe9t5DD7CYOP7saKDBnapULwPp8BuqIHW",
//"address"
//=>
//"Sierra Leone",
//"about"
//=>
//"Sit et quam inventore qui.",
//"rating"
//=>
//2,
//"rating_number"
//=>
//0,
//"recommended_number"
//=>
//0,
//"consultancies_number"
//=>
//0,
//"min_range"
//=>
//6,
//"max_range"
//=>
//50,
//"category_id"
//=>
//1,
//"deleted_at"
//=>
//null,
//"created_at"
//=>
//"2024-03-25T10:25:46.000000Z",
//"updated_at"
//=>
//"2024-03-25T10:25:46.000000Z",],
//[
//            "id"
//=>
//1,
//"full_name"
//=>
//"Alaina Breanna",
//"phone"
//=>
//"+9633477323",
//"email"
//=>
//"sabshire@example.net",
//"password"
//=>
//"$2y$12$5DBcl18t.fWHHMnYUnZ1fe9t5DD7CYOP7saKDBnapULwPp8BuqIHW",
//"address"
//=>
//"Sierra Leone",
//"about"
//=>
//"Sit et quam inventore qui.",
//"rating"
//=>
//2,
//"rating_number"
//=>
//0,
//"recommended_number"
//=>
//0,
//"consultancies_number"
//=>
//0,
//"min_range"
//=>
//6,
//"max_range"
//=>
//50,
//"category_id"
//=>
//1,
//"deleted_at"
//=>
//null,
//"created_at"
//=>
//"2024-03-25T10:25:46.000000Z",
//"updated_at"
//=>
//"2024-03-25T10:25:46.000000Z",],
//        ];

