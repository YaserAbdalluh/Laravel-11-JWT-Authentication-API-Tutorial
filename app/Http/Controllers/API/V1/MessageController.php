<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Message;
use Exception;
use Pusher\Pusher;

class MessageController extends Controller
{
    public function sendMessage(Request $request)
    {
        try {
            $user = Auth::guard('api')->user();

            if (!$user) {
                return response()->json([
                    'message' => 'Unauthorized',
                ], 401);
            }


            // Broadcast the message
            $pusher = new Pusher(
                env('PUSHER_APP_KEY'),
                env('PUSHER_APP_SECRET'),
                env('PUSHER_APP_ID'),
                ['cluster' => env('PUSHER_APP_CLUSTER'), 'useTLS' => true]
            );
            $message = Message::create([
                'message' => $request->message,
                'sender_id' => $request->sender_id,
                'receiver_id' => $request->receiver_id,
            ]);

            $pusher->trigger('chat-channel', 'new-message', [
                'message' => $message
            ]);

            return response()->json(['message' => 'تمت عملية الارسال', 'data' => $message], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }

    }

    public function getMessages($sender_id, $receiver_id)
    {
        try {
            $user = Auth::guard('api')->user();

            if (!$user) {
                return response()->json([
                    'message' => 'Unauthorized',
                ], 401);
            }
            $messages = Message::where(function ($query) use ($sender_id, $receiver_id) {
                $query->where('sender_id', $sender_id)->where('receiver_id', $receiver_id);
            })->orWhere(function ($query) use ($sender_id, $receiver_id) {
                $query->where('sender_id', $receiver_id)->where('receiver_id', $sender_id);
            })->orderBy('sent_at', 'desc')->limit($request->limit ?? 20)->get();

            return response()->json([
                'status' => true,
                'data' => $messages
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}