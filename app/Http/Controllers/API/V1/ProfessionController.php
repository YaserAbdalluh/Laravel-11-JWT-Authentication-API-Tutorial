<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Profession;
use Illuminate\Support\Facades\Log;

class ProfessionController extends Controller
{

    // Check if the authenticated user is admin
    private function isAdmin()
    {
        $user = Auth::guard('api')->user();
        return $user && $user->is_admin;
    }

    // Create a new profession
    public function store(Request $request)
    {
        try {
            if (!$this->isAdmin()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'category_id' => 'required|exists:categroys,id',
                'description' => 'nullable|string',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'يجب تعبئة جميع الحقول',
                    'error' => $validator->errors()
                ], 422);

            }
            // if ($validator->fails()) {
            //     return response()->json(['errors' => $validator->errors()], 422);
            // }

            $profession = Profession::create($request->all());

            return response()->json(['message' => 'Profession created successfully', 'profession' => $profession], 201);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Error in stor profeeiosn: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ داخلي في الخادم. يرجى المحاولة لاحقًا.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Get all professions
    public function index(Request $request)
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $query = Profession::query();

        // Apply filtering
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('category_id', 'like', '%' . $request->search . '%');
            });
        }


        // Apply pagination
        $perPage = $request->get('per_page', 10);
        $professions = $query->paginate($perPage);

        return response()->json([
            'status' => true,
            'data' => $professions
        ], 200);
    }


    // Get a single profession
    public function show($id)
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $profession = Profession::find($id);
        if (!$profession) {
            return response()->json(['message' => 'Profession not found'], 404);
        }

        return response()->json($profession, 200);
    }

    // Update a profession
    public function update(Request $request, $id)
    {
        try {
            if (!$this->isAdmin()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $profession = Profession::findOrFail($id);
            if (!$profession) {
                return response()->json(['message' => 'Profession not found'], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255',
                'category_id' => 'sometimes|exists:categroys,id',
                'description' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }


            $profession->update($request->all());

            return response()->json(['message' => 'Profession updated successfully', 'profession' => $profession], 200);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Error in upadet profeeiosn: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ داخلي في الخادم. يرجى المحاولة لاحقًا.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Delete a profession
    public function destroy(Request $request, $id)
    {
        try {
            if (!$this->isAdmin()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $profession = Profession::find($id);
            if (!$profession) {
                return response()->json(['message' => 'Profession not found'], 404);
            }

            $profession->delete();

            return response()->json(['message' => 'Profession deleted successfully'], 200);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Error in delete profeeiosn: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ داخلي في الخادم. يرجى المحاولة لاحقًا.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
