<?php
namespace App\Http\Controllers;

use App\Models\{Application, Building, Registrant, Stall, User};
use App\Notifications\MarketSystemNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};

class LandingController extends Controller
{
    public function index()
    {
        return view('landing', [
            'buildings' => Building::where('is_active', true)
                ->with(['stalls' => fn ($query) => $query->with(['owner', 'activeLease'])->orderBy('display_order')->orderBy('stall_number')])
                ->orderBy('code')->get(),
            'selectedBuilding' => request('building'),
            'stats' => [
                'buildings' => Building::where('is_active', true)->count(),
                'stalls' => Stall::count(),
                'available' => Stall::where('status', 'available')->where('needs_verification', false)
                    ->whereDoesntHave('applications', fn ($query) => $query->where('status', 'pending'))->count(),
                'occupied' => Stall::where('status', 'occupied')->count(),
                'pending' => Application::where('status', 'pending')->count(),
            ],
        ]);
    }

    public function register(Request $request)
    {
        $data = $request->validate(array_merge(RegistrantController::rules(), [
            'birthdate' => 'required|date|before_or_equal:' . now()->subYears(18)->toDateString(),
            'email' => 'required|email|unique:users,email',
            'username' => 'required|string|min:3|max:40|alpha_dash|unique:users,username',
            'password' => 'required|min:8|confirmed',
        ]), ['birthdate.before_or_equal' => 'You must be at least 18 years old to register.']);

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'], 'email' => $data['email'], 'username' => $data['username'],
                'password' => $data['password'], 'role' => 'user',
            ]);
            Registrant::create([
                'user_id' => $user->id, 'name' => $data['name'], 'status' => $data['status'],
                'citizenship' => $data['citizenship'], 'birthdate' => $data['birthdate'],
                'address' => $data['address'], 'contact_number' => $data['contact_number'],
            ]);
            DB::table('activity_logs')->insert([
                'user_id' => $user->id,
                'action' => 'registrant.registered',
                'subject_type' => Registrant::class,
                'subject_id' => $user->registrant->id,
                'details' => json_encode(['control_number' => $user->registrant->control_number]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return $user;
        });

        $user->notify(new MarketSystemNotification(
            'Registration successful',
            'Your applicant control number is ' . $user->registrant->control_number . '.',
            route('dashboard'),
        ));
        Auth::login($user);
        return redirect()->route('dashboard')
            ->with('success', 'Registration complete! Your control number is ' . $user->registrant->control_number);
    }
}
