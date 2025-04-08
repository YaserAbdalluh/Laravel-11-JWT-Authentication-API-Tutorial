<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Support\Facades\Cache; // Ensure correct model is used
use App\Models\Categroy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    private function isAdmin()
    {
        $user = Auth::guard('api')->user();
        return $user && $user->is_admin;
    }

    // Fetch categories with optional filtering
    public function index(Request $request)
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $cacheKey = 'categories_all';
        // Use cache or fetch fresh data
        $categories = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($request) {
            $query = Categroy::query();

            if ($request->has('search')) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('user_id', 'like', '%' . $request->search . '%');
                });
            }

            return $query->get();
        });
        return response()->json([
            'status' => true,
            'data' => $categories
        ], 200);
    }

    // Show a single category by ID
    public function show($id)
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $cacheKey = "category_{$id}";

        $category = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($id) {
            return Categroy::find($id);
        });
        // $category = Categroy::find($id);

        // if (!$category) {
        //     return response()->json(['error' => 'Category not found'], 404);
        // }

        return response()->json([
            'status' => true,
            'data' => $category
        ], 200);
    }

    // Store a new category with image upload
    public function store(Request $request)
    {
        if (!$this->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Validate input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'image' => 'required',  // Validating image (optional)
        ]);
        // Clear cache

        if ($validator->fails()) {
            return response()->json([
                'message' => 'يجب تعبئة جميع الحقول',
                'error' => $validator->errors()
            ], 422);
        }

        // Create category
        try {
            $category = Categroy::create([
                'name' => $request->name,
                'image' => $request->image,
            ]);
            Cache::forget('categories_all');
            return response()->json([
                'status' => true,
                'message' => 'تم انشاء الصنف بنجاح',
                'data' => $category
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create category',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Update an existing category with optional image update
    public function update(Request $request, $id)
    {
        // Find the category
        $category = Categroy::find($id);
        if (!$this->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!$category) {
            return response()->json(['error' => 'Category not found'], 404);
        }

        // Validate input
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'image' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'يجب تعبئة جميع الحقول',
                'error' => $validator->errors()
            ], 422);

        }

        // Update category
        try {
            $category->update([
                'name' => $request->name ?? $category->name,
                'image' => $request->image,
            ]);
            // Clear cache
            Cache::forget("category_{$id}");
            Cache::forget('categories_all');
            return response()->json([
                'status' => true,
                'message' => 'تم تحديث الفئة بنجاح',
                'data' => $category
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update category',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Delete a category and its image
    public function destroy($id)
    {
        // Find the category
        if (!$this->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $category = Categroy::find($id);

        if (!$category) {
            return response()->json(['error' => 'Category not found'], 404);
        }

        // Delete category
        try {
            $category->delete();

            return response()->json([
                'status' => true,
                'message' => 'تم حذف الفئة بنجاح'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete category',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}