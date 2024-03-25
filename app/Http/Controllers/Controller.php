<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /*
     *
    public function index(Request $request){
        if ($request->has('search')) {
            $experts1 = Expert::where('full_name', 'like', $request->search)->get();
            $experiences = Experience::whereHas('experts')->with('experts')->where('name', 'like', $request->search)->get();
            $experts2 =$experiences->map(function ($experience){
                return $experience->experts;
            });
            $experts = array_merge($experts1->toArray(),$experts2->toArray());
            if (!empty($experts))
                return $this->successResponse($experts);
            $categories = Category::where('name', 'like', $request->search)->get();
            if (!empty($categories))
            return $this->successResponse($categories);
            $sub_categories = SubCategory::where('name', 'like', $request->search)->get();
            if (!empty($sub_categories))
            return $this->successResponse($sub_categories);
        }
        return $this->successResponse();
    }
     * */
}
