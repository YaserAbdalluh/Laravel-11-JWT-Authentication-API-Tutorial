<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class WorkerCroller extends Controller
{
    private function isVerify()
    {
        $user = Auth::guard('api')->user();
        return $user && $user->is_verified;
    }
    // private function isfreelancing()
    // {
    //     $user = Auth::guard('api')->user();
    //     return $user && $user->is_client;
    // }
    // Create a new workers
    // public function store(Request $request)
    // {
    //     try {
    //         if (!$this->isVerify()) {
    //             return response()->json(['message' => 'Unauthorized'], 403);
    //         }

    //         $validator = Validator::make($request->all(), [
    //             'user_id' => 'required|exists:users,id',
    //             'profession_id' => 'required|exists:professions,id',
    //             'certifications' => 'nullable|string|max:255',
    //             'phone_number' => 'required|string|max:15',
    //             'experience_years' => 'required|integer|min:0|max:5',
    //             'total_reviews' => 'nullable|integer|min:0',
    //             'hourly_rate' => 'nullable|numeric|min:0',
    //             'rating_avg' => 'nullable|numeric|min:0|max:5',
    //             'availability_status' => 'nullable|string',
    //             'skills' => 'nullable|string',
    //         ]);

    //         if ($validator->fails()) {
    //             return response()->json(['errors' => $validator->errors()], 422);
    //         }
    //         // Check if a client already exists for this user

    //         if ($this->isfreelancing() == true) {
    //             return response()->json(['message' => 'User is not Freelancing'], 409);
    //         }

    //         $workers = Worker::create($request->all());

    //         return response()->json(['message' => 'workers created successfully', 'workers' => $workers], 201);
    //     } catch (\Exception $e) {
    //         // Log the error for debugging
    //         Log::error('Error in stor profeeiosn: ' . $e->getMessage(), [
    //             'request' => $request->all(),
    //             'trace' => $e->getTraceAsString()
    //         ]);

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'حدث خطأ داخلي في الخادم. يرجى المحاولة لاحقًا.',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    // Get all workerss
    public function index(Request $request)
    {
        try {
            $user = Auth::guard('api')->user();

            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Define the query to get workers with their related 'user' and 'profession'
            // $query = Worker::query()->with(['user', 'profession']);

            $query = Worker::query()->with(['user', 'profession']);


            // Apply filtering based on search input
            if ($request->has('search')) {
                $search = '%' . $request->search . '%';

                // Apply filtering on the 'user' relation
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('first_name', 'like', $search)
                        ->orWhere('last_name', 'like', $search)
                        ->orWhere('id', 'like', $search);
                });

                // Apply filtering on the 'profession' relation
                $query->orWhereHas('profession', function ($q) use ($search) {
                    $q->where('name', 'like', $search);
                });
            }

            // Apply pagination
            $perPage = $request->get('per_page', 10);
            $workers = $query->paginate($perPage);

            // Return the response with data
            return response()->json([
                'status' => true,
                'data' => $workers
            ], 200);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Error in fetching workers: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            // Return error response
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ داخلي في الخادم. يرجى المحاولة لاحقًا.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    // Get a single workers
    public function show($id)
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        // $workers = Worker::find($id);
        $workers = Worker::with(['user', 'profession'])->find($id);
        if (!$workers) {
            return response()->json(['message' => 'لم يتم العثور على مهنيين'], 404);
        }
        return response()->json([
            'status' => true,
            'data' => $workers
        ], 200);
        // return response()->json($workers, 200);
    }

    // Update a workers
    public function update(Request $request, $id)
    {
        try {
            if (!$this->isVerify()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $workers = Worker::findOrFail($id);
            if (!$workers) {
                return response()->json(['message' => 'workers not found'], 404);
            }

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
                'profession_id' => 'required|exists:professions,id',
                'certifications' => 'nullable|string|max:255',
                'phone_number' => 'required|string|max:15',
                'experience_years' => 'required|integer|min:0|max:5',
                'total_reviews' => 'nullable|integer|min:0',
                'hourly_rate' => 'nullable|numeric|min:0',
                'rating_avg' => 'nullable|numeric|min:0|max:5',
                'availability_status' => 'nullable|string',
                'skills' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }


            $workers->update($request->all());

            return response()->json(['message' => 'workers updated successfully', 'workers' => $workers], 200);
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

    // Delete a workers
    public function destroy(Request $request, $id)
    {
        try {
            if (!$this->isVerify()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $workers = Worker::find($id);
            if (!$workers) {
                return response()->json(['message' => 'workers not found'], 404);
            }

            $workers->delete();

            return response()->json(['message' => 'workers deleted successfully'], 200);
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