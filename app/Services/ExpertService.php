<?php

namespace App\Services;

use App\Models\Expert;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ExpertService
{
    /**
     * Get experts based on the request criteria.
     *
     * @param Request $request
     * @return mixed
     */
    public function getExperts(Request $request)
    {
        // Build the query based on request parameters
        $expertsQuery = $this->buildExpertQuery($request);

        $relations = $this->getDetailsExpert();

        // Execute the query with pagination
        $experts = $this->getWithPaginate($expertsQuery, $request, $relations);

        // Format the expert dates for the response
        return $this->formatExpertDates($experts);
    }

    /**
     * Build the query to fetch experts based on the request criteria.
     *
     * @param Request $request
     * @return Builder
     */
    private function buildExpertQuery(Request $request): Builder
    {
        $experts = Expert::query();

        // Add search query filter if provided
        if ($request->query_search) {
            $searchQuery = $request->input('query_search');
            $experts->where(function ($query) use ($searchQuery) {
                $query->where('full_name', 'like', '%' . $searchQuery . '%')
                    ->orWhereHas('category', function ($query) use ($searchQuery) {
                        $query->where('name', 'like', '%' . $searchQuery . '%');
                    })->orWhereHas('subCategories', function ($query) use ($searchQuery) {
                        $query->where('name', 'like', '%' . $searchQuery . '%');
                    });
            });
        }

        // Filter by main category if provided
        if ($request->main_category_id) {
            $experts->where('category_id', $request->main_category_id);
        }

        // Filter by sub category if provided
        if ($request->sub_category_id) {
            $experts->whereHas('subCategories', function ($query) use ($request) {
                $query->where('sub_categories.id', $request->sub_category_id);
            });
        }

        // Filter by recommended experts if requested
        if ($request->experts_type == 'recommended_experts') {
            $experts->where('recommended_number', '>', 0)
                ->orderByDesc('recommended_number');
        }

        // Filter by top experts if requested
        if ($request->experts_type == 'top_experts') {
            $experts->where('rating', '>', 2)
                ->orderByDesc('rating');
        }

        return $experts;
    }

    /**
     * Execute the query with pagination and eager loading.
     *
     * @param $query
     * @param Request $request
     * @param array $relations
     * @return mixed
     */
    private function getWithPaginate($query, Request $request, array $relations = [])
    {
        // Set the limit and page for pagination
        $limit = $request->limit ?? 10;
        $page = $request->page ?? null;

        // Execute the query with the specified relations
        return $query->with($relations)->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * Get relationships to load for experts
     *
     * @return string[]
     */
    public function getDetailsExpert()
    {
        // Define the relationships to load for experts
        return [
            'category:id,name',
            'workTimes.day',
            'workTimes.startTime',
            'workTimes.endTime',
            'expertDates.day',
            'expertDates.hour',
            'subCategories:id,name',
            'communicationTypes',
            'followers'
        ];
    }

    /**
     * Get expert by expert identifier with details
     *
     * @param $id
     * @return Builder|Builder[]|Collection|Model|null
     */
    public function getExpert($id)
    {
        $relations = $this->getDetailsExpert();

        return Expert::with($relations)->find($id);
    }

    public function updateExpert($expert, $request)
    {
        $expert->update([
            'about' => $request->about,
            'address' => $request->address,
            'category_id' => $request->category_id,
            'min_cost' => $request->min_cost,
            'max_cost' => $request->max_cost,
        ]);
    }
    /**
     * Format the dates for each expert for the response.
     *
     * @param mixed $experts
     * @return mixed
     */
    private function formatExpertDates($experts)
    {
        // Get the start and end of the current month
        $startOfMonth = Carbon::now();
        $endOfMonth = Carbon::now()->addMonth();

        // Format the dates for each expert
        $experts->getCollection()->transform(function ($expert) use ($startOfMonth, $endOfMonth) {
            $dates = [];
            foreach ($startOfMonth->daysUntil($endOfMonth) as $date) {
                foreach ($expert->workTimes as $workTime) {
                    if ($workTime->day->name == $date->format('l')) {
                        $dayAppointments = $expert->expertDates->filter(function ($expertDate) use ($date) {
                            return $expertDate->day->name == $date->format('l');
                        })->map(function ($expertDate) {
                            return [
                                'hour' => $expertDate->hour->label,
                                'available' => $expertDate->available,
                            ];
                        });

                        $dates[] = [
                            'date' => $date->format('Y-m-d'),
                            'day' => $date->format('l'),
                            'appointments' => $dayAppointments,
                        ];
                    }
                }
            }
            $expert->dates = $dates;
            unset($expert->workTimes);
            unset($expert->expertDates);
            return $expert;
        });

        return $experts;
    }

    /**
     * Get the top 10 highest recommended experts.
     *
     * @return Collection
     */
    public function getHighestRecommendedExperts()
    {
        return Expert::where('recommended_number', '>', 0)
            ->orderByDesc('recommended_number')
            ->limit(10)
            ->get();
    }

    /**
     * Get the top 10 highest rated experts.
     *
     * @return Collection
     */
    public function getHighestRatedExperts()
    {
        return Expert::where('rating', '>', 2)
            ->orderByDesc('rating')
            ->limit(10)
            ->get();
    }
}
