<?php

namespace App\Http\Controllers\Api\V1\Guest;

use App\Http\Controllers\Controller;
use App\ResponseTrait;
use App\Services\CategoryService;
use App\Services\ExpertService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{
    use ResponseTrait;

    protected ExpertService $expertService;

    protected CategoryService $categoryService;

    // Constructor for injecting dependencies
    public function __construct(CategoryService $categoryService, ExpertService $expertService)
    {
        $this->categoryService = $categoryService;
        $this->expertService = $expertService;
    }

    // Method to get home page data including categories and experts
    public function getHomeData(): JsonResponse
    {
        // Fetch categories with their subcategories and experts
        $categories = $this->categoryService->getCategoriesWithSubcategoriesAndExperts();

        // Fetch the highest recommended experts
        $highestRecommendedExperts = $this->expertService->getHighestRecommendedExperts();

        // Fetch the highest rated experts
        $highestRatedExperts = $this->expertService->getHighestRatedExperts();

        // Return the fetched data in a success response
        return $this->successResponse([
            'categories' => $categories,
            'highestRecommendedExperts' => $highestRecommendedExperts,
            'highestRatedExperts' => $highestRatedExperts,
        ]);
    }

    // Method to get experts based on search criteria
    public function getExperts(Request $request): JsonResponse
    {
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'query_search' => ['string', 'max:255'],
            'experts_type' => ['string', 'max:255'],
            'main_category_id' => ['exists:categories,id'],
            'sub_category_id' => ['exists:sub_categories,id'],
        ]);

        if ($validator->fails()) {
            return $this->failedResponse($validator->errors()->first());
        }

        // Fetch experts based on the validated request data
        $experts = $this->expertService->getExperts($request);

        // Return the fetched experts in a success response
        return $this->successResponse($experts);
    }

}
