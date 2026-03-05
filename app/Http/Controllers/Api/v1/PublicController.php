<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Category\CategoryResource;
use App\Http\Resources\Project\ProjectResource;
use App\Http\Resources\Service\ServiceResource;
use App\Models\Category;
use App\Models\Project;
use App\Models\Service;

class PublicController extends Controller
{
    /**
     * GET /api/public/services
     * Returns all services (no auth required)
     */
    public function services()
    {
        $services = Service::latest()->get();
        return $this->SuccessfullyResponse(
            ServiceResource::collection($services),
            __('general.loadSuccess')
        );
    }

    /**
     * GET /api/public/services/{service_id}/categories
     * Returns categories belonging to a specific service
     */
    public function categories($service_id)
    {
        $service = Service::find($service_id);
        if (!$service) {
            return $this->FailedResponse(__('general.notFound'), 404);
        }

        $categories = Category::where('service_id', $service_id)->latest()->get();
        return $this->SuccessfullyResponse(
            CategoryResource::collection($categories),
            __('general.loadSuccess')
        );
    }

    /**
     * GET /api/public/categories/{category_id}/projects
     * Returns projects/images belonging to a specific category
     */
    public function projects($category_id)
    {
        $category = Category::find($category_id);
        if (!$category) {
            return $this->FailedResponse(__('general.notFound'), 404);
        }

        $projects = Project::where('category_id', $category_id)->latest()->get();
        return $this->SuccessfullyResponse(
            ProjectResource::collection($projects),
            __('general.loadSuccess')
        );
    }
}
