<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class AdminSettingsController extends Controller
{
    public function edit()
    {
        return view('admin.settings', ['user' => auth()->user()]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'current_password' => ['required_with:password', 'current_password'],
            'password' => ['nullable', 'string', 'min:12', 'confirmed'],
        ]);

        $before = $user->only(['name', 'email']);
        $user->fill(['name' => $data['name'], 'email' => $data['email']]);
        if (!empty($data['password'])) {
            $user->password = $data['password'];
        }
        $user->save();

        DB::table('activity_logs')->insert([
            'user_id' => $user->id,
            'action' => 'admin.account_settings_updated',
            'subject_type' => $user::class,
            'subject_id' => $user->id,
            'details' => json_encode(['before' => $before, 'after' => $user->only(['name', 'email']), 'password_changed' => !empty($data['password'])]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Administrator account settings updated.');
    }
}
