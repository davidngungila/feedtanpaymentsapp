<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    /**
     * Display the user's profile.
     */
    public function index()
    {
        $user = auth()->user();
        return view('auth.profile', compact('user'));
    }
    
    /**
     * Update the user's profile.
     */
    public function update(Request $request)
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:500',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB max
        ]);
        
        // Handle avatar upload
        if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            $avatar = $request->file('avatar');
            $avatarName = time() . '_' . Str::random(10) . '.' . $avatar->getClientOriginalExtension();
            
            // Store avatar in public/uploads/avatars
            $avatarPath = $avatar->storeAs('avatars', $avatarName, 'public');
            
            // Update user avatar
            $user->avatar = $avatarName;
        }
        
        // Update user profile
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'bio' => $validated['bio'],
            'avatar' => $user->avatar ?? null,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully!',
            'user' => $user->fresh()
        ]);
    }
    
    /**
     * Upload avatar image.
     */
    public function uploadAvatar(Request $request)
    {
        $user = auth()->user();
        
        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            $avatar = $request->file('avatar');
            $avatarName = time() . '_' . Str::random(10) . '.' . $avatar->getClientOriginalExtension();
            
            // Delete old avatar if exists
            if ($user->avatar) {
                $oldAvatarPath = public_path('uploads/avatars/' . $user->avatar);
                if (file_exists($oldAvatarPath)) {
                    unlink($oldAvatarPath);
                }
            }
            
            // Store new avatar
            $avatarPath = $avatar->storeAs('avatars', $avatarName, 'public');
            
            // Update user avatar
            $user->update(['avatar' => $avatarName]);
            
            return response()->json([
                'success' => true,
                'message' => 'Avatar uploaded successfully!',
                'avatar_url' => asset('uploads/avatars/' . $avatarName),
                'user' => $user->fresh()
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Invalid file or file upload failed'
        ], 400);
    }
    
    /**
     * Get user profile data for API.
     */
    public function getProfile()
    {
        $user = auth()->user();
        
        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'bio' => $user->bio,
                'avatar' => $user->avatar ? asset('uploads/avatars/' . $user->avatar) : null,
                'role' => $user->role,
                'created_at' => $user->created_at->toISOString(),
                'updated_at' => $user->updated_at->toISOString(),
            ]
        ]);
    }
    
    /**
     * Delete user avatar.
     */
    public function deleteAvatar()
    {
        $user = auth()->user();
        
        if ($user->avatar) {
            $avatarPath = public_path('uploads/avatars/' . $user->avatar);
            if (file_exists($avatarPath)) {
                unlink($avatarPath);
            }
            
            $user->update(['avatar' => null]);
            
            return response()->json([
                'success' => true,
                'message' => 'Avatar deleted successfully!'
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'No avatar to delete'
        ], 404);
    }
    
    /**
     * Change user password.
     */
    public function changePassword(Request $request)
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);
        
        // Verify current password
        if (!\Illuminate\Support\Facades\Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect'
            ], 422);
        }
        
        // Update password
        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($validated['new_password'])
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully!'
        ]);
    }
    
    /**
     * Download user data.
     */
    public function downloadUserData()
    {
        $user = auth()->user();
        
        $userData = [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'bio' => $user->bio,
            'avatar' => $user->avatar ? asset('uploads/avatars/' . $user->avatar) : null,
            'role' => $user->role,
            'created_at' => $user->created_at->toISOString(),
            'updated_at' => $user->updated_at->toISOString(),
            'emails_sent' => \App\Models\EmailMessage::where('user_id', $user->id)->count(),
            'sms_sent' => \App\Models\SmsMessage::where('user_id', $user->id)->count(),
        ];
        
        $filename = 'feedtan_user_data_' . date('Y-m-d_H-i-s') . '.json';
        
        return response()->json($userData)
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
}
