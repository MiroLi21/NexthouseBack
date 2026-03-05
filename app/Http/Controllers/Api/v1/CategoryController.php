<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\StoreCategoryRequest;
use App\Http\Requests\api\UpdateCategoryRequest;
use App\Http\Resources\Category\CategoryResource;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('Service')->latest()->get();
        return $this->SuccessfullyResponse(
            CategoryResource::collection($categories),
            __('general.loadSuccess')
        );
    }

    public function store(StoreCategoryRequest $request)
    {
        $category = Category::create($request->validated());
        return $this->SuccessfullyResponse(
            new CategoryResource($category->load('Service')),
            __('general.createSuccess')
        );
    }

    public function show($id)
    {
        $category = Category::with('Service')->find($id);
        if (!$category) {
            return $this->FailedResponse(__('general.notFound'), 404);
        }
        return $this->SuccessfullyResponse(
            new CategoryResource($category),
            __('general.loadSuccess')
        );
    }

    public function update(UpdateCategoryRequest $request, $id)
    {
        $category = Category::find($id);
        if (!$category) {
            return $this->FailedResponse(__('general.notFound'), 404);
        }

        $category->update($request->validated());

        return $this->SuccessfullyResponse(
            new CategoryResource($category->load('Service')),
            __('general.updateSuccess')
        );
    }

    public function destroy($id)
    {
        $category = Category::find($id);
        if (!$category) {
            return $this->FailedResponse(__('general.notFound'), 404);
        }

        $category->delete();

        return $this->SuccessfullyResponse(null, __('general.deleteSuccess'));
    }
}
