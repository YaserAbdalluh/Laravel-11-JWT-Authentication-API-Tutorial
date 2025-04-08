<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ClientCroller extends Controller
{
    // Check if the authenticated user is admin
    private function isClient()
    {
        $user = Auth::guard('api')->user();
        return $user && $user->is_client;
    }
    private function isAdmin()
    {
        $user = Auth::guard('api')->user();
        return $user && $user->is_admin;
    }

    // Create a new clients
    public function store(Request $request)
    {
        try {
            if (!$this->isClient()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $validator = Validator::make($request->all(), [
                'company_name' => 'required|string|max:255',
                'user_id' => 'required|exists:users,id',
                'description' => 'nullable|string',
                'total_orders' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Check if a client already exists for this user
            if (Client::where('user_id', $request->user_id)->exists()) {
                return response()->json(['message' => 'User already has a client record'], 409);
            }

            $client = Client::create($request->all());

            return response()->json(['message' => 'Client created successfully', 'client' => $client], 201);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Error in store client: ' . $e->getMessage(), [
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


    // Get all clientss
    public function index(Request $request)
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $query = Client::query();

        // Apply filtering
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('company_name', 'like', '%' . $request->search . '%')
                    ->orWhere('user_id', 'like', '%' . $request->search . '%');
            });
        }

        // Filter clients based on authenticated user_id
        // $query->where('user_id', $user->id);

        // Eager load the user details along with the client
        $query->with('user');  // This will load the user associated with each client

        // Apply pagination
        $perPage = $request->get('per_page', 10);
        $clients = $query->paginate($perPage);

        return response()->json([
            'status' => true,
            'data' => $clients
        ], 200);
    }



    // Get a single clients
    public function show($id)
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $clients = Client::find($id);
        if (!$clients) {
            return response()->json(['message' => 'clients not found'], 404);
        }

        return response()->json($clients, 200);
    }

    // Update a clients
    public function update(Request $request, $id)
    {
        try {
            if (!$this->isAdmin()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $clients = Client::findOrFail($id);
            if (!$clients) {
                return response()->json(['message' => 'clients not found'], 404);
            }

            $validator = Validator::make($request->all(), [
                'company_name' => 'required|string|max:255',
                'user_id' => 'required|exists:users,id',
                'description' => 'nullable|string',
                'total_orders' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }


            $clients->update($request->all());

            return response()->json(['message' => 'clients updated successfully', 'clients' => $clients], 200);
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

    // Delete a clients
    public function destroy(Request $request, $id)
    {
        try {
            if (!$this->isAdmin()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $clients = Client::find($id);
            if (!$clients) {
                return response()->json(['message' => 'clients not found'], 404);
            }

            $clients->delete();

            return response()->json(['message' => 'clients deleted successfully'], 200);
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
