<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\StoreProjectRequest;
use App\Http\Requests\api\UpdateProjectRequest;
use App\Http\Resources\Project\ProjectResource;
use App\Models\Project;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    /**
     * Store the uploaded image safely using Laravel storage.
     * Tries GD compression first; falls back to direct store if GD unavailable.
     */
    private function storeImage($file, string $folder = 'projects'): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $filename = Str::uuid() . '.' . $extension;

        // --- Try GD compression (if extension is loaded) ---
        if (extension_loaded('gd') && in_array($extension, ['jpg', 'jpeg', 'png', 'webp'])) {
            $tmpPath = $file->getRealPath();
            $destPath = storage_path("app/public/{$folder}/{$filename}");

            // Ensure directory exists
            $dir = dirname($destPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $compressed = false;

            if (in_array($extension, ['jpg', 'jpeg'])) {
                $img = @imagecreatefromjpeg($tmpPath);
                if ($img) {
                    imagejpeg($img, $destPath, 80);
                    imagedestroy($img);
                    $compressed = true;
                }
            } elseif ($extension === 'png') {
                $img = @imagecreatefrompng($tmpPath);
                if ($img) {
                    imagesavealpha($img, true);
                    imagepng($img, $destPath, 7);
                    imagedestroy($img);
                    $compressed = true;
                }
            } elseif ($extension === 'webp') {
                $img = @imagecreatefromwebp($tmpPath);
                if ($img) {
                    imagewebp($img, $destPath, 80);
                    imagedestroy($img);
                    $compressed = true;
                }
            }

            if ($compressed) {
                return "{$folder}/{$filename}";
            }
        }

        // --- Fallback: direct Laravel storage (always works) ---
        return $file->storeAs($folder, $filename, 'public');
    }

    // -------------------------------------------------------

    public function index()
    {
        $projects = Project::with('Category')->latest()->get();
        return $this->SuccessfullyResponse(
            ProjectResource::collection($projects),
            __('general.loadSuccess')
        );
    }

    public function store(StoreProjectRequest $request)
    {
        $validated = $request->validated();
        $imagePath = $this->storeImage($request->file('image'), 'projects');

        $project = Project::create([
            'image_path' => $imagePath,
            'description' => $validated['description'] ?? null,
            'category_id' => $validated['category_id'],
        ]);

        return $this->SuccessfullyResponse(
            new ProjectResource($project->load('Category')),
            __('general.createSuccess')
        );
    }

    public function show($id)
    {
        $project = Project::with('Category')->find($id);
        if (!$project) {
            return $this->FailedResponse(__('general.notFound'), 404);
        }
        return $this->SuccessfullyResponse(
            new ProjectResource($project),
            __('general.loadSuccess')
        );
    }

    public function update(UpdateProjectRequest $request, $id)
    {
        $project = Project::find($id);
        if (!$project) {
            return $this->FailedResponse(__('general.notFound'), 404);
        }

        $validated = $request->validated();
        $data = [];

        if (array_key_exists('description', $validated)) {
            $data['description'] = $validated['description'];
        }
        if (!empty($validated['category_id'])) {
            $data['category_id'] = $validated['category_id'];
        }

        if ($request->hasFile('image')) {
            // Delete old image if stored locally
            if ($project->image_path && !str_starts_with($project->image_path, 'http')) {
                Storage::disk('public')->delete($project->image_path);
            }
            $data['image_path'] = $this->storeImage($request->file('image'), 'projects');
        }

        $project->update($data);

        return $this->SuccessfullyResponse(
            new ProjectResource($project->fresh()->load('Category')),
            __('general.updateSuccess')
        );
    }

    public function destroy($id)
    {
        $project = Project::find($id);
        if (!$project) {
            return $this->FailedResponse(__('general.notFound'), 404);
        }

        // Delete local image only (not external URLs)
        if ($project->image_path && !str_starts_with($project->image_path, 'http')) {
            Storage::disk('public')->delete($project->image_path);
        }

        $project->delete();

        return $this->SuccessfullyResponse(null, __('general.deleteSuccess'));
    }
}
