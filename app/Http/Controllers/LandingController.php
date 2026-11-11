<?php
namespace App\Http\Controllers;

use App\Models\{Registrant, Stall, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};

class LandingController extends Controller
{
    public function index()
    {
        return view('landing', [
            'stalls' => Stall::where('status', 'available')->orderBy('stall_number')->get(),
            'control' => Registrant::nextControlNumber(),
        ]);
    }

    public function register(Request $request)
    {
        $data = $request->validate(array_merge(RegistrantController::rules(), [
            'birthdate' => 'required|date|before_or_equal:' . now()->subYears(18)->toDateString(),
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ]), ['birthdate.before_or_equal' => 'You must be at least 18 years old to register.']);

        $user = DB::transaction(function () use ($data) {
            $user = User::create(['name' => $data['name'], 'email' => $data['email'], 'password' => $data['password'], 'role' => 'user']);
            Registrant::create([
                'user_id' => $user->id, 'name' => $data['name'], 'status' => $data['status'],
                'citizenship' => $data['citizenship'], 'birthdate' => $data['birthdate'],
                'address' => $data['address'], 'contact_number' => $data['contact_number'],
            ]);
            return $user;
        });

        Auth::login($user);
        return redirect()->route('dashboard')
            ->with('success', 'Registration complete! Your control number is ' . $user->registrant->control_number);
    }
}
