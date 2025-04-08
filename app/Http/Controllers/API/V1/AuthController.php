<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Client;
use App\Models\User;
use App\Models\Worker;
// use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class AuthController extends BaseController
{
    protected WhatsAppService $whatsappService;

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user = User::where('first_name', $request->first_name)->first();

        if (!$user) {
            return $this->sendError('البيانات غير متطابقة.', ['error' => 'Invalid credentials.']);
        }


        if (!$user->code_verified) {
            return $this->sendError('البيانات غير متطابقة.', ['error' => '.لم يتم التحقق من حسابك، يرجى التحقق من رقم هاتفك قبل تسجيل الدخول']);
        }

        $credentials = request(['first_name', 'password']);
        // $credentials = $request->only(['first_name', 'password']);
        if (!$user->is_verified) {
            return $this->sendError('البيانات غير متطابقة.', ['error' => '.لم يتم ترخيص حسابك بعد، يجب ترخيص الجهاز من قبل الإدارة']);
        }
        if (!$token = Auth::attempt($credentials)) {
            return $this->sendError('البيانات غير متطابقة.', ['error' => 'البيانات غير متطابقة']);
        }

        $user = User::find(Auth::id());
        $user->update(['last_login_at' => now()]);

        $success = $this->respondWithToken($token);
        return $this->sendResponse([
            'token' => $success,
            'user' => $user,
        ], 'User login successfully.');
        // return $this->sendResponse($success, 'User login successfully.');
    }
    public function __construct(WhatsappService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }
    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'password' => 'required|min:6',
                'c_password' => 'required|same:password',
                'age' => 'required|integer|between:18,60',
                'phone_number' => 'required|string|unique:users,phone_number',
                'user_type' => 'required|in:worker,Client',
                'city_id' => 'required',
                'gendar' => 'required',
                'region_id' => 'required',
                'birth_date' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }

            // Check if the user already exists
            if (User::where('phone_number', $request->phone_number)->exists()) {
                return $this->sendError('المستخدم مسجل بالفعل.', ['phone_number' => 'رقم الهاتف هذا قيد الاستخدام بالفعل.']);
            }

            // Prepare user data
            $input = $request->all();
            $verificationCode = random_int(100000, 999999);
            $input['code'] = $verificationCode;
            $input['password'] = bcrypt($input['password']);

            if ($request->user_type === 'Client') {
                // $validator = Validator::make($request->all(), [
                //     'company_name' => 'required_if:user_type,Client|string|max:255',
                // ]);
                $input['is_client'] = true;
                $input['is_verified'] = 1; // Auto-verification for clients
            } else {
                $input['is_client'] = false;
            }

            // Create user record
            $user = User::create($input);

            // If worker, store additional worker details
            if ($request->user_type === 'worker') {
                $validator = Validator::make($request->all(), [
                    'experience_years' => 'required_if:user_type,worker|integer|min:0',
                    'availability_status' => 'required',
                    'skills' => 'required_if:user_type,worker|array',
                    'skills.*' => 'string|max:255',
                ]);
                if ($validator->fails()) {
                    return $this->sendError('Validation Error.', $validator->errors());
                }
                Worker::create([
                    'user_id' => $user->id,
                    'experience_years' => $request->experience_years,
                    'certifications' => $request->certifications,
                    'skills' => json_encode($request->skills),
                    'hourly_rate' => $request->hourly_rate,
                    'availability_status' => $request->availability_status,
                ]);
            }

            // If client, store additional client details
            if ($request->user_type === 'Client') {
                Client::create([
                    'user_id' => $user->id,
                    'company_name' => $request->company_name,
                    'description' => $request->description,
                ]);
            }

            // Send WhatsApp verification if service is available
            if (isset($this->whatsappService)) {
                $this->whatsappService->sendVerificationCode($user->phone_number, $verificationCode);
            } else {
                return $this->sendError('خدمة الواتس اب غير متوفرة.');
            }

            return $this->sendResponse(
                ['message' => "تم إرسال رمز التحقق إلى $user->phone_number."],
                'يرجى التحقق من حسابك.'
            );

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function verifyCode(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'phone_number' => 'required|string|exists:users,phone_number',
                'code' => 'required|string|min:6|max:6',
            ]);

            if ($validator->fails()) {
                return $this->sendError('خطأ في التحقق..', $validator->errors(), 422);
            }

            $user = User::where('phone_number', $request->phone_number)
                ->where('code', $request->code)
                ->first();

            if (!$user) {
                return $this->sendError('رمز التحقق غير صحيح.', ['code' => 'رمز التحقق غير صحيح.'], 400);
            }

            // Mark user as verified
            $user->update([
                'code_verified' => true,
                'code' => null,
            ]);

            return $this->sendResponse(
                ['message' => 'تم التحقق من رقم الهاتف. يمكنك الآن تسجيل الدخول.'],
                'تم التحقق بنجاح.'
            );
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Error in verifyCode: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ داخلي في الخادم. يرجى المحاولة لاحقًا.',
                'error' => $e->getMessage(), // Only show this in development
            ], 500);
        }
    }


    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */


    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile(Request $request)
    {
        // Check if the user is authenticated
        $user = Auth::guard('api')->user();
        // $query = User::query()->with(['user', 'workers'])->get;
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return response()->json([
            'status' => true,
            'user' => $user
        ], 200);
        // return $this->sendResponse($user, 'الرمز صالح. تم استرداد ملف تعريف المستخدم بنجاح');
    }


    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        if (Auth::user()) {
            try {
                Auth::logout();
                if (request()->hasSession()) {
                    request()->session()->invalidate();
                    // request()->session()->regenerateToken();
                }

                return $this->sendResponse([], 'Successfully logged out.');
            } catch (\Exception $e) {
                return $this->sendError('Logout failed: ' . $e->getMessage(), 500);
            }
        }
        return $this->sendError('User is not logged in.', 400);
    }


    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        $success = $this->respondWithToken(Auth::refresh());

        return $this->sendResponse($success, 'Refresh token return successfully.');
    }
    public function verifyToken(Request $request)
    {
        try {
            // Retrieve the token from the request header
            $token = $request->bearerToken();

            // Check if the token exists and is valid
            if (!$token) {
                return $this->sendError('Token is missing.', [], 400);
            }

            // Validate the token using Laravel's Auth facade
            $user = Auth::setToken($token)->user();

            if (!$user) {
                return $this->sendError('Invalid token.', [], 401);
            }

            // If everything is fine, return true
            return $this->sendResponse(true, 'Token is valid.');
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Error in verifyToken: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60 * 60,
        ];
    }

    // Update Profile
    public function updateProfile(Request $request)
    {
        // Ensure the user is authenticated before proceeding
        $user = User::find(Auth::id());
        if (!$user) {
            return $this->sendError('المستخدم غير مُصادق عليه.', [], 401);
        }

        // Validate the request
        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'profile_picture' => 'sometimes|required|string',
            'phone_number' => 'sometimes|required|string|unique:users,phone_number,' . $user->id,
            'city_id' => 'sometimes|required',
            'region_id' => 'sometimes|required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('خطأ في التحقق (بعض الحقول مطلوبة).', $validator->errors(), 422);
        }

        // Update the user profile
        $user->update($request->only(['first_name', 'last_name', 'phone_number', 'city_id', 'region_id', 'profile_picture']));

        return $this->sendResponse($user, 'تم تحديث الملف الشخصي بنجاح.');
    }

    // Resend OTP
    public function resendOTP(Request $request)
    {
        // Validate the phone number
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string|exists:users,phone_number',
        ]);

        if ($validator->fails()) {
            return $this->sendError('خطأ التحقق.', $validator->errors(), 422);
        }

        // Resend OTP to the user
        $user = User::where('phone_number', $request->phone_number)->first();
        $otp = random_int(100000, 999999);
        $user->update(['code' => $otp]);

        $this->whatsappService->sendVerificationCode($user->phone_number, $otp);

        return $this->sendResponse(true, 'تم إرسال OTP بنجاح، تحقق من تطبيق WhatsApp الخاص بك للحصول على OTP.');
    }

    // Reset Password

    public function resetPassword(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string|exists:users,phone_number',
            'password' => 'required|min:6',
        ]);



        if ($validator->fails()) {
            return $this->sendError('خطأ التحقق.', [
                'errors' => $validator->errors(),
                'phone_number' => $request->phone_number,
            ], 422);
        }


        $user = User::where('phone_number', $request->phone_number)->first();

        if ($user->code_verified == 0) {
            return $this->sendError('لقد تم التحقق من الكود مسبقاً.', ['error' => 'Code has already been verified.'], 400);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'c_password' => $request->password,
            'code' => null, // Clear OTP code after reset
        ]);

        return $this->sendResponse(['message' => 'تم إعادة تعيين كلمة المرور بنجاح.'], 'You can now log in with your new password.');
    }

    // Forget Password
    public function forgotPassword(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string|exists:users,phone_number',
        ]);

        if ($validator->fails()) {
            return $this->sendError('رقم الهاتف المحدد غير مسجل لدينا قم بإنشاء حساب جديد', $validator->errors(), code: 422);
        }

        // Send OTP for password recovery
        $user = User::where('phone_number', $request->phone_number)->first();

        $otp = random_int(100000, 999999);

        $user->update(['code' => $otp]);

        $this->whatsappService->sendVerificationCode($user->phone_number, $otp);

        return $this->sendResponse(true, 'تم إرسال OTP بنجاح، تحقق من تطبيق WhatsApp الخاص بك للحصول على OTP.');
    }

}