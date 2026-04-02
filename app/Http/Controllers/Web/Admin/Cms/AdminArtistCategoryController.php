<?php

namespace App\Http\Controllers\Web\Admin\Cms;

use App\Http\Controllers\Controller;
use App\Models\ArtistCategory;
use App\Traits\AdminApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AdminArtistCategoryController extends Controller
{
    use AdminApiResponse;

    /**
     * Display a listing of artist categories.
     */
    public function index()
    {
        $categories = ArtistCategory::orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('web.admin.spotlight.artist_categories', compact('categories'));
    }

    /**
     * Store a new artist category.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:artist_categories,slug',
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $slug = $request->filled('slug') 
            ? Str::slug($request->slug) 
            : Str::slug($request->name);

        $category = ArtistCategory::create([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $request->input('sort_order', 0),
        ]);

        return $this->success('Artist category created successfully.', [
            'category' => $category,
        ]);
    }

    /**
     * Update an existing artist category.
     */
    public function update(Request $request, ArtistCategory $category)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:artist_categories,slug,' . $category->id,
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $slug = $request->filled('slug') 
            ? Str::slug($request->slug) 
            : Str::slug($request->name);

        $category->update([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $request->input('sort_order', 0),
        ]);

        return $this->success('Artist category updated successfully.', [
            'category' => $category->fresh(),
        ]);
    }

    /**
     * Delete an artist category.
     */
    public function destroy(ArtistCategory $category)
    {
        if ($category->spotlights()->exists()) {
            return $this->error('Cannot delete category that has spotlights associated with it.', [], 422);
        }

        $category->delete();

        return $this->success('Artist category deleted successfully.');
    }
}
