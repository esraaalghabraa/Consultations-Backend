<?php

namespace App\Http\Controllers\Api\V1\Expert;

use App\Exceptions\ExistObjectException;
use App\Http\Controllers\Controller;
use App\ImageTrait;
use App\ResponseTrait;
use App\Services\CategoryService;
use App\Services\CommunicationService;
use App\Services\ExpertService;
use App\Services\SubCategoryService;
use App\Services\UserService;
use App\Services\WorkTimeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ExpertController extends Controller
{
    use ResponseTrait, ImageTrait;

    protected ExpertService $expertService;
    protected CategoryService $categoryService;
    protected UserService $userService;
    protected WorkTimeService $workTimeService;

    protected CommunicationService $communicationService;
    protected SubCategoryService $subCategoryService;

    /**
     * Constructor for injecting dependencies
     */
    public function __construct(WorkTimeService      $workTimeService, UserService $userService,
                                CategoryService      $categoryService, ExpertService $expertService,
                                CommunicationService $communicationService, SubCategoryService $subCategoryService)
    {
        // Middleware for authentication and role checks
        $this->middleware(['auth:sanctum', 'abilities:user,access', 'role_expert']);

        // Assigning services to properties
        $this->userService = $userService;
        $this->categoryService = $categoryService;
        $this->expertService = $expertService;
        $this->workTimeService = $workTimeService;
        $this->communicationService = $communicationService;
        $this->subCategoryService = $subCategoryService;
    }

    /**
     * Method to get categories with their subcategories
     */
    public function getCategories()
    {
        return $this->categoryService->getCategoriesWithSubcategories();
    }

    /**
     * Method to get the profile of the authenticated expert
     */
    public function getProfile()
    {
        return $this->successResponse($this->expertService->getExpert(Auth::user()->id));
    }

    /**
     * Method to complete the expert's information
     */
    public function completeInfo(Request $request)
    {
        // Validate the request data
        $validator = $this->validateRequest($request);

        if ($validator->fails())
            return $this->failedResponse($validator->errors()->first());

        $image = null;
        if ($request->has('image')) {
            $image = $this->setImage($request->file('image'));
        }

        try {
            // Get authenticated user and expert
            $user = $this->userService->getAuthenticatedUser();
            $expert = $this->userService->getAuthenticatedExpert();

            // Update user and expert information
            $this->userService->updateUser($user, $request, $image);
            $this->expertService->updateExpert($expert, $request);

            // Create work times, communication types, and subcategories for the expert
            $this->workTimeService->createWorkTimesToExpert($expert, $request->work_times);
            $this->communicationService->addCommunicationTypesToExpert($expert, $request->communication_types_ids);
            $this->subCategoryService->addSubCategoriesToExpert($expert, $request->sub_category_ids);
        } catch (ExistObjectException $e) {
            // Handle known exceptions
            return $this->failedResponse($e->getMessage());
        } catch (\Exception $e) {
            // Handle unexpected exceptions
            return $this->failedResponse('An unexpected error occurred');
        }
        return $this->successResponse($expert);
    }

    /**
     * Method to validate the request data
     */
    private function validateRequest(Request $request)
    {
        return Validator::make($request->all(), [
            'image' => ['image', 'mimes:jpeg,jpg,png,svg|max:255'],
            'about' => ['required', 'string'],
            'address' => ['required', 'string', 'max:255'],
            'birthdate' => ['required', 'date', 'before:today', 'date_format:Y-m-d'],
            'gender' => ['required', 'string'],
            'min_cost' => ['required', 'numeric'],
            'max_cost' => ['required', 'numeric', 'gt:min_cost'],
            'category_id' => ['required', 'exists:categories,id,deleted_at,NULL'],
            'communication_types_ids' => ['required', 'array', 'min:1'],
            'communication_types.*' => ['required', 'exists:communication_types,id,deleted_at,NULL'],
            'sub_category_ids' => ['required', 'array', 'min:1'],
            'sub_category_ids.*' => ['required', 'exists:sub_categories,id,deleted_at,NULL'],
            'work_times' => ['required', 'array', 'min:1'],
            'work_times.*.day' => ['required', 'string', 'exists:days,name,deleted_at,NULL'],
            'work_times.*.start_time' => ['required', 'date_format:g,h:i A'],
            'work_times.*.end_time' => ['required', 'date_format:g,h:i A', 'after:start'],
        ]);
    }
}
