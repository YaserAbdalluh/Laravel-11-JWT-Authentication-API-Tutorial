<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class FavoriteController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    /**
     * Store a newly created resource in storage.
     */


    public function index()
    {
        try {
            $user = Auth::guard('api')->user();

            if (!$user) {
                return response()->json([
                    'message' => 'Unauthorized',
                ], 401);
            }

            // Fetch the user's favorites with related client, worker, and the worker's associated user
            $favorites = Favorite::where('client_id', $user->id)
                ->with([
                    'worker.user' // Load worker and the associated user of the worker
                ])
                ->get();
            return response()->json([
                'status' => true,
                'data' => $favorites
            ], 200);
            // return response()->json($favorites, 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function store(Request $request)
    {
        try {
            $user = Auth::guard('api')->user();

            if (!$user) {
                return response()->json([
                    'message' => 'Unauthorized',
                ], 401);
            }

            $request->validate([
                'worker_id' => 'required',
                'client_id' => 'required',
            ]);

            // Check if the favorite already exists for the given client, worker, and profession
            $existingFavorite = DB::table('favorites')
                ->where('client_id', $request->client_id)
                ->where('worker_id', $request->worker_id)
                ->first();

            $worker_id = $request->worker_id;
            $foundingWorker = DB::table('workers')
                ->where('id', $worker_id)  // Use the correct variable here
                ->first();


            if (!$foundingWorker) {
                return response()->json([
                    'message' => 'worker not found.',
                ], 404);
            }

            if ($existingFavorite) {
                return response()->json([
                    'message' => 'تمت إضافة المهني للمفضلة من قبل.',
                ], 400);
            }

            DB::table('favorites')->insert([
                'client_id' => $request->client_id,
                'worker_id' => $request->worker_id,
            ]);

            return response()->json(['message' => 'تمت الاضافة الى المفظلة'], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to add favorite',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $user = Auth::guard('api')->user();

            if (!$user) {
                return response()->json([
                    'message' => 'Unauthorized',
                ], 401);
            }
            $deleted = DB::table('favorites')->where('worker_id', $id)->delete();

            if ($deleted) {
                return response()->json(['message' => 'تم حذف المفضلة بنجاح'], 200);
            } else {
                return response()->json(['message' => 'لم يتم العثور على المفضلة'], 404);
            }
        } catch (Exception $e) {
            return response()->json([
                'message' => 'فشل في حذف المفضلة',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
}