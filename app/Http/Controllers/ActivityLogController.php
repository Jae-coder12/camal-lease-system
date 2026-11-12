<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $activities = DB::table('activity_logs')
            ->leftJoin('users', 'activity_logs.user_id', '=', 'users.id')
            ->select('activity_logs.*', 'users.name as actor_name')
            ->when($filters['q'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('activity_logs.action', 'like', '%' . $search . '%')
                        ->orWhere('users.name', 'like', '%' . $search . '%')
                        ->orWhere('activity_logs.subject_type', 'like', '%' . $search . '%');
                });
            })
            ->orderByDesc('activity_logs.created_at')
            ->orderByDesc('activity_logs.id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.activity-logs', compact('activities'));
    }
}
