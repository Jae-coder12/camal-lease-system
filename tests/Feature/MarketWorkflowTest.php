<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Building;
use App\Models\Lease;
use App\Models\Registrant;
use App\Models\Stall;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Notifications\ResetPassword;
use RuntimeException;
use Tests\TestCase;

class MarketWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_rejects_underage_applicants_and_assigns_control_numbers(): void
    {
        $underage = $this->post('/register', $this->registrationData([
            'birthdate' => today()->subYears(17)->toDateString(),
        ]));
        $underage->assertSessionHasErrors('birthdate');
        $this->assertDatabaseCount('users', 0);

        $this->post('/register', $this->registrationData())->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('registrants', ['control_number' => today()->format('Y') . '-001']);
        $this->assertDatabaseHas('users', ['username' => 'marketvendor']);
    }

    public function test_approval_assigns_a_lease_and_closes_competing_applications(): void
    {
        [$admin, $first, $second, $stall] = $this->workflowFixture();
        $application = Application::create([
            'registrant_id' => $first->registrant->id,
            'stall_id' => $stall->id,
            'purpose' => 'Vegetable sales',
        ]);
        $competitor = Application::create([
            'registrant_id' => $second->registrant->id,
            'stall_id' => $stall->id,
            'purpose' => 'Fruit sales',
        ]);

        $this->actingAs($admin)->post(route('applications.approve', $application), [
            'remarks' => 'Approved after review.',
        ])->assertRedirect();

        $this->assertDatabaseHas('applications', ['id' => $application->id, 'status' => 'approved']);
        $this->assertDatabaseHas('applications', ['id' => $competitor->id, 'status' => 'rejected']);
        $this->assertDatabaseHas('stalls', ['id' => $stall->id, 'registrant_id' => $first->registrant->id, 'status' => 'occupied']);
        $this->assertDatabaseHas('leases', [
            'stall_id' => $stall->id,
            'registrant_id' => $first->registrant->id,
            'approved_by' => $admin->id,
            'rental_amount' => '600.00',
            'payment_frequency' => 'monthly',
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'lease_id' => Lease::where('stall_id', $stall->id)->value('id'),
            'reviewed_by' => $admin->id,
        ]);
        $this->assertDatabaseHas('leases', [
            'application_id' => $application->id,
            'stall_id' => $stall->id,
        ]);
        $this->assertDatabaseHas('application_status_histories', [
            'application_id' => $application->id,
            'from_status' => 'pending',
            'to_status' => 'approved',
            'acted_by' => $admin->id,
        ]);
    }

    public function test_admin_login_is_dedicated_and_rejects_applicant_accounts(): void
    {
        $admin = User::factory()->create([
            'username' => 'marketadmin',
            'role' => 'admin',
            'password' => Hash::make('AdminPass123!'),
        ]);
        $applicant = User::factory()->create([
            'username' => 'ordinaryvendor',
            'role' => 'user',
            'password' => Hash::make('VendorPass123!'),
        ]);

        $this->get(route('admin.login'))->assertOk()->assertSee('Administrator sign in');
        $this->post(route('admin.login.submit'), [
            'login' => $applicant->username,
            'password' => 'VendorPass123!',
        ])->assertSessionHasErrors('login');
        $this->assertGuest();

        $this->post(route('admin.login.submit'), [
            'login' => $admin->username,
            'password' => 'AdminPass123!',
        ])->assertRedirect(route('admin.dashboard'));
        $this->get(route('admin.dashboard'))->assertOk();
        $this->get(route('applications.index'))->assertOk();
        $this->get(route('leases.index'))->assertOk();
        $this->post(route('logout'))->assertRedirect(route('landing'));
        $this->assertGuest();
    }

    public function test_applicant_can_sign_in_and_request_a_password_reset_without_exposing_account_existence(): void
    {
        Notification::fake();
        $applicant = User::factory()->create([
            'username' => 'recoveryvendor',
            'role' => 'user',
            'password' => Hash::make('VendorPass123!'),
        ]);
        $this->makeRegistrantFor($applicant);

        $this->get(route('login'))->assertOk()
            ->assertSee('Email address or username')
            ->assertSee('Forgot password?');

        $this->post(route('password.email'), ['email' => $applicant->email])
            ->assertSessionHas('status');
        Notification::assertSentTo($applicant, ResetPassword::class);

        $this->post(route('password.email'), ['email' => 'not-registered@example.test'])
            ->assertSessionHas('status', 'If an account exists for that email, a password reset link has been sent.');
        $this->assertDatabaseCount('password_reset_tokens', 1);
        $admin = User::factory()->create(['role' => 'admin']);
        $this->post(route('password.email'), ['email' => $admin->email])
            ->assertSessionHas('status', 'If an account exists for that email, a password reset link has been sent.');
        Notification::assertNotSentTo($admin, ResetPassword::class);

        $this->post(route('login'), [
            'login' => $applicant->email,
            'password' => 'VendorPass123!',
        ])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($applicant);
        $this->get(route('registrants.index'))->assertForbidden();
    }

    public function test_password_reset_page_updates_password_using_a_valid_broker_token(): void
    {
        $applicant = User::factory()->create(['role' => 'user']);
        $token = Password::createToken($applicant);
        $this->get(route('password.reset', ['token' => $token, 'email' => $applicant->email]))
            ->assertOk()->assertSee('Choose a new password');

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $applicant->email,
            'password' => 'NewVendorPass123!',
            'password_confirmation' => 'NewVendorPass123!',
        ])->assertRedirect(route('login'))->assertSessionHas('status');

        $this->post(route('login'), [
            'login' => $applicant->email,
            'password' => 'NewVendorPass123!',
        ])->assertRedirect(route('dashboard'));
    }

    public function test_admin_settings_are_role_protected_and_record_account_changes(): void
    {
        [$admin, $applicant] = $this->workflowFixture();
        $this->actingAs($applicant)->get(route('admin.settings'))->assertForbidden();

        $this->actingAs($admin)->get(route('admin.settings'))->assertOk();
        $this->put(route('admin.settings.update'), [
            'name' => 'Market Admin Updated',
            'email' => 'market-admin-updated@example.test',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'name' => 'Market Admin Updated']);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $admin->id,
            'action' => 'admin.account_settings_updated',
            'subject_id' => $admin->id,
        ]);
    }

    public function test_admin_user_seeder_creates_secure_idempotent_account_and_does_not_promote_applicants(): void
    {
        config([
            'admin.name' => 'Seeded Market Admin',
            'admin.email' => 'seeded-admin@example.test',
            'admin.username' => 'seeded-admin@example.test',
            'admin.password' => 'SecureAdminPass123!',
        ]);

        $this->seed(\Database\Seeders\AdminUserSeeder::class);
        $admin = User::where('email', 'seeded-admin@example.test')->firstOrFail();
        $this->assertTrue($admin->isAdmin());
        $this->assertSame('seeded-admin-admin', $admin->username);
        $this->assertTrue(Hash::check('SecureAdminPass123!', $admin->password));

        $originalHash = $admin->password;
        config(['admin.password' => 'DifferentAdminPass456!']);
        $this->seed(\Database\Seeders\AdminUserSeeder::class);
        $this->assertSame($originalHash, $admin->fresh()->password);
        $this->assertSame(1, User::where('email', 'seeded-admin@example.test')->count());

        User::factory()->create([
            'email' => 'applicant-conflict@example.test',
            'username' => 'applicantconflict',
            'role' => 'user',
        ]);
        config([
            'admin.email' => 'applicant-conflict@example.test',
            'admin.username' => 'anotheradmin',
        ]);
        try {
            $this->seed(\Database\Seeders\AdminUserSeeder::class);
            $this->fail('Seeding must not promote an existing applicant.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('non-administrator', $exception->getMessage());
        }
        $this->assertDatabaseHas('users', ['email' => 'applicant-conflict@example.test', 'role' => 'user']);
    }

    public function test_admin_user_seeder_retries_password_confirmation_until_it_matches(): void
    {
        config([
            'admin.name' => 'Prompted Market Admin',
            'admin.email' => 'prompted-admin@example.test',
            'admin.username' => 'prompted-admin',
            'admin.password' => '',
        ]);
        $command = \Mockery::mock(\Illuminate\Console\Command::class);
        $command->shouldReceive('secret')->times(4)->andReturn(
            'FirstSecurePassword123!',
            'DoesNotMatch123!',
            'SecondSecurePassword456!',
            'SecondSecurePassword456!',
        );
        $command->shouldReceive('warn')->once()->with('Passwords did not match. Try again.');
        $command->shouldReceive('info')->once()->with('Administrator account created from the configured environment credentials. Username: prompted-admin');

        app(\Database\Seeders\AdminUserSeeder::class)->setCommand($command)->run();

        $admin = User::where('email', 'prompted-admin@example.test')->firstOrFail();
        $this->assertTrue(Hash::check('SecondSecurePassword456!', $admin->password));
    }

    public function test_admin_dashboard_metrics_and_navigation_use_live_records(): void
    {
        [$admin, $applicant, , $stall] = $this->workflowFixture();
        $lease = Lease::create([
            'stall_id' => $stall->id,
            'registrant_id' => $applicant->registrant->id,
            'start_date' => today(),
            'end_date' => today()->addYear(),
            'monthly_rate' => 600,
            'rental_amount' => 600,
            'payment_frequency' => 'monthly',
        ]);
        $lease->payments()->create([
            'recorded_by' => $admin->id,
            'reference_number' => 'PM-PENDING-001',
            'amount' => 600,
            'payment_date' => today(),
            'status' => 'pending',
        ]);
        \Illuminate\Support\Facades\DB::table('activity_logs')->insert([
            'user_id' => $admin->id,
            'action' => 'payment.recorded',
            'subject_type' => \App\Models\Payment::class,
            'subject_id' => 1,
            'details' => json_encode(['reference_number' => 'PM-PENDING-001']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()
            ->assertSee('Pending Transactions')
            ->assertSee('Building Management')
            ->assertSee('Stall Assignments & Occupants')
            ->assertSee('Lease Contracts')
            ->assertSee('User Management')
            ->assertSee('Activity Log')
            ->assertSee('Settings')
            ->assertSee('reports')
            ->assertSee('No paid rental transactions were recorded in the displayed period.');

        $response->assertSee('href="' . route('admin.settings') . '"', false);
        $this->get(route('admin.activity-logs'))->assertOk()
            ->assertSee('Administrative activity')
            ->assertSee('Payment Recorded')
            ->assertSee('PM-PENDING-001');
        $this->assertSame(1, \App\Models\Payment::where('status', 'pending')->count());
    }

    public function test_applicant_dashboard_is_personalized_and_scopes_applications_leases_and_payments(): void
    {
        [$admin, $applicant, $otherApplicant, $stall] = $this->workflowFixture();
        Application::create([
            'registrant_id' => $applicant->registrant->id,
            'stall_id' => $stall->id,
            'purpose' => 'Applicant private application',
        ]);
        Application::create([
            'registrant_id' => $otherApplicant->registrant->id,
            'stall_id' => $stall->id,
            'purpose' => 'Other applicant private application',
        ]);
        $ownLease = Lease::create([
            'stall_id' => $stall->id,
            'registrant_id' => $applicant->registrant->id,
            'start_date' => today(),
            'end_date' => today()->addYear(),
            'monthly_rate' => 600,
            'rental_amount' => 600,
            'payment_frequency' => 'monthly',
        ]);
        $otherLease = Lease::create([
            'stall_id' => $stall->id,
            'registrant_id' => $otherApplicant->registrant->id,
            'start_date' => today(),
            'end_date' => today()->addYear(),
            'monthly_rate' => 600,
            'rental_amount' => 600,
            'payment_frequency' => 'monthly',
        ]);
        $ownLease->payments()->create([
            'recorded_by' => $admin->id,
            'reference_number' => 'PM-OWNED-001',
            'amount' => 600,
            'payment_date' => today(),
            'status' => 'paid',
        ]);
        $otherLease->payments()->create([
            'recorded_by' => $admin->id,
            'reference_number' => 'PM-PRIVATE-002',
            'amount' => 600,
            'payment_date' => today(),
            'status' => 'paid',
        ]);

        $this->actingAs($applicant)->get(route('dashboard'))->assertOk()
            ->assertSee('Application tracking')
            ->assertSee('PM-OWNED-001')
            ->assertSee('Current stall assignments')
            ->assertDontSee('PM-PRIVATE-002')
            ->assertDontSee('Other applicant private application');
        $this->get(route('admin.activity-logs'))->assertForbidden();
    }

    public function test_application_review_creates_a_linked_historical_contract_and_can_be_completed(): void
    {
        [$admin, $applicant, , $stall] = $this->workflowFixture();
        $application = Application::create([
            'registrant_id' => $applicant->registrant->id,
            'stall_id' => $stall->id,
            'purpose' => 'Vegetable sales',
        ]);

        $this->actingAs($admin)->post(route('applications.review', $application))->assertRedirect();
        $this->assertDatabaseHas('application_status_histories', [
            'application_id' => $application->id,
            'from_status' => 'pending',
            'to_status' => 'under_review',
            'acted_by' => $admin->id,
        ]);

        $this->post(route('applications.approve', $application), ['remarks' => 'Documents verified.'])->assertRedirect();
        $application->refresh();
        $lease = Lease::findOrFail($application->lease_id);
        $this->assertSame('approved', $application->status);
        $this->assertSame($lease->id, $application->lease_id);
        $this->assertSame($applicant->registrant->name, $lease->tenant_details['name']);
        $this->assertNotEmpty($lease->contract_number);
        $this->assertNotEmpty($lease->contract_terms);

        $this->get(route('leases.document', $lease))->assertOk()
            ->assertSee($lease->contract_number)
            ->assertSee('CONTRACT OF LEASE OF MARKET STALL')
            ->assertSee('Control No.')
            ->assertSee('first option to renew')
            ->assertSee('Authorized Municipal Representative')
            ->assertSee('ACKNOWLEDGMENT')
            ->assertSee($applicant->registrant->name);
        $this->get(route('leases.download', $lease))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
        $this->get(route('applications.index'))->assertOk()
            ->assertSee('Applicant details')
            ->assertSee('Generated contract');

        $this->post(route('applications.complete', $application))->assertRedirect();
        $this->assertDatabaseHas('applications', ['id' => $application->id, 'status' => 'completed']);
        $this->assertDatabaseHas('application_status_histories', [
            'application_id' => $application->id,
            'from_status' => 'approved',
            'to_status' => 'completed',
            'acted_by' => $admin->id,
        ]);
    }

    public function test_lease_renewal_creates_a_new_contract_without_overwriting_the_old_one(): void
    {
        [$admin, $applicant, , $stall] = $this->workflowFixture();
        $original = Lease::create([
            'stall_id' => $stall->id,
            'registrant_id' => $applicant->registrant->id,
            'start_date' => today()->subYear(),
            'end_date' => today()->subDay(),
            'monthly_rate' => 600,
            'rental_amount' => 600,
            'payment_frequency' => 'monthly',
            'status' => 'expired',
        ]);

        $this->actingAs($applicant)->post(route('leases.renew', $original), [])->assertForbidden();

        $start = today()->toDateString();
        $end = today()->addYear()->toDateString();
        $this->actingAs($admin)->post(route('leases.renew', $original), [
            'start_date' => $start,
            'end_date' => $end,
            'rental_amount' => 750,
            'payment_frequency' => 'monthly',
            'contract_terms' => 'Renewed contract terms.',
        ])->assertRedirect();

        $renewed = Lease::where('renewed_from_lease_id', $original->id)->firstOrFail();
        $this->assertSame('ended', $original->fresh()->status);
        $this->assertSame($original->end_date->toDateString(), $original->fresh()->end_date->toDateString());
        $this->assertSame($start, $renewed->start_date->toDateString());
        $this->assertSame('750.00', $renewed->rental_amount);
        $this->assertSame('750.00', $stall->fresh()->rental_rate);
        $this->assertSame('Renewed contract terms.', $renewed->contract_terms);
        $this->assertSame('occupied', $stall->fresh()->status);
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'lease.renewed',
            'subject_id' => $renewed->id,
            'user_id' => $admin->id,
        ]);
    }

    public function test_shared_stall_accepts_distinct_occupants_up_to_its_capacity(): void
    {
        [$admin, $first, $second, $stall] = $this->workflowFixture(sharedCapacity: 2);
        $firstApplication = Application::create([
            'registrant_id' => $first->registrant->id, 'stall_id' => $stall->id, 'purpose' => 'Produce',
        ]);
        $secondApplication = Application::create([
            'registrant_id' => $second->registrant->id, 'stall_id' => $stall->id, 'purpose' => 'Dry goods',
        ]);

        $this->actingAs($admin)->post(route('applications.approve', $firstApplication))->assertRedirect();
        $this->assertDatabaseHas('applications', ['id' => $secondApplication->id, 'status' => 'pending']);
        $this->actingAs($admin)->post(route('applications.approve', $secondApplication))->assertRedirect();

        $this->assertSame(2, Lease::where('stall_id', $stall->id)->where('status', 'active')->count());
        $this->assertSame(2, $stall->fresh()->occupants()->count());
        $this->assertSame(0, $stall->fresh()->remainingCapacity());
    }

    public function test_applications_reserve_stalls_and_prevent_duplicate_requests(): void
    {
        [, $first, $second, $stall] = $this->workflowFixture();

        $this->actingAs($first)->post(route('my.apply', $stall), [
            'purpose' => 'Fresh vegetables',
        ])->assertRedirect(route('my.applications'));
        $this->assertDatabaseHas('applications', [
            'registrant_id' => $first->registrant->id,
            'stall_id' => $stall->id,
            'status' => 'pending',
        ]);
        $this->assertSame(0, $stall->fresh()->remainingCapacity());

        $this->actingAs($second)->post(route('my.apply', $stall), [
            'purpose' => 'Fruit vendor',
        ])->assertSessionHasErrors('stall');
        $this->assertSame(1, Application::where('stall_id', $stall->id)->count());
    }

    public function test_authorized_payment_records_update_the_monthly_balance(): void
    {
        [$admin, $applicant, , $stall] = $this->workflowFixture();
        $lease = Lease::create([
            'stall_id' => $stall->id,
            'registrant_id' => $applicant->registrant->id,
            'start_date' => today()->startOfMonth(),
            'end_date' => today()->addYear(),
            'monthly_rate' => 600,
            'rental_amount' => 600,
            'payment_frequency' => 'monthly',
        ]);

        $this->actingAs($admin)->post(route('payments.store'), [
            'lease_id' => $lease->id,
            'amount' => 300,
            'payment_date' => today()->toDateString(),
            'period_covered' => today()->format('F Y'),
            'method' => 'cash',
        ])->assertRedirect();

        $this->assertDatabaseHas('payments', [
            'lease_id' => $lease->id,
            'amount' => '300.00',
            'status' => 'paid',
        ]);
        $this->assertSame(300.0, $lease->fresh()->outstanding_balance);
    }

    public function test_admin_can_review_pending_payments_and_only_paid_transactions_have_receipts(): void
    {
        [$admin, $applicant, , $stall] = $this->workflowFixture();
        $lease = Lease::create([
            'stall_id' => $stall->id,
            'registrant_id' => $applicant->registrant->id,
            'start_date' => today(),
            'end_date' => today()->addYear(),
            'monthly_rate' => 600,
            'rental_amount' => 600,
            'payment_frequency' => 'monthly',
        ]);
        $pending = $lease->payments()->create([
            'recorded_by' => $admin->id,
            'reference_number' => 'PM-REVIEW-001',
            'amount' => 600,
            'payment_date' => today(),
            'status' => 'pending',
        ]);

        $this->actingAs($applicant)->post(route('payments.review', $pending), [
            'decision' => 'approve',
        ])->assertForbidden();
        $this->actingAs($admin)->get(route('payments.index', ['status' => 'pending']))
            ->assertOk()->assertSee('PM-REVIEW-001')->assertSee('Approve')->assertSee('Reject');
        $this->get(route('payments.receipt', $pending))->assertNotFound();

        $this->post(route('payments.review', $pending), ['decision' => 'reject'])
            ->assertSessionHasErrors('remarks');
        $this->post(route('payments.review', $pending), [
            'decision' => 'approve',
            'remarks' => 'Verified against the municipal record.',
        ])->assertRedirect()->assertSessionHas('success', 'Payment approved and marked as paid.');
        $this->assertDatabaseHas('payments', [
            'id' => $pending->id,
            'status' => 'paid',
            'remarks' => 'Verified against the municipal record.',
        ]);
        $this->get(route('payments.receipt', $pending))->assertOk();
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'payment.approved',
            'subject_id' => $pending->id,
            'user_id' => $admin->id,
        ]);

        $rejected = $lease->payments()->create([
            'recorded_by' => $admin->id,
            'reference_number' => 'PM-REVIEW-002',
            'amount' => 300,
            'payment_date' => today(),
            'status' => 'pending',
        ]);
        $this->post(route('payments.review', $rejected), [
            'decision' => 'reject',
            'remarks' => 'The submitted transaction could not be verified.',
        ])->assertRedirect();
        $this->assertDatabaseHas('payments', ['id' => $rejected->id, 'status' => 'rejected']);
        $this->get(route('payments.receipt', $rejected))->assertNotFound();
        $this->assertGreaterThan(0, $applicant->fresh()->notifications()->count());
    }

    public function test_expiration_command_releases_stall_and_notifies_occupant(): void
    {
        [$admin, $applicant, , $stall] = $this->workflowFixture();
        $stall->assignOwner($applicant->registrant->id);
        $lease = $stall->leases()->firstOrFail();
        $lease->update(['end_date' => today()->subDay()]);

        $this->artisan('market:notify-lease-expirations')->assertSuccessful();

        $this->assertSame('expired', $lease->fresh()->status);
        $this->assertSame('available', $stall->fresh()->status);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $applicant->id,
        ]);
        $this->assertGreaterThan(0, $admin->notifications()->count());
    }

    public function test_applicants_cannot_access_administrator_routes_and_can_only_view_their_receipts(): void
    {
        [$admin, $applicant, $otherApplicant, $stall] = $this->workflowFixture();
        $lease = Lease::create([
            'stall_id' => $stall->id,
            'registrant_id' => $applicant->registrant->id,
            'start_date' => today(),
            'end_date' => today()->addYear(),
            'monthly_rate' => 600,
            'rental_amount' => 600,
            'payment_frequency' => 'monthly',
        ]);
        $payment = $lease->payments()->create([
            'recorded_by' => $admin->id,
            'reference_number' => 'PM-TEST-0001',
            'amount' => 600,
            'payment_date' => today(),
            'status' => 'paid',
        ]);

        $this->actingAs($applicant)->get(route('buildings.index'))->assertForbidden();
        $this->get(route('admin.dashboard'))->assertForbidden();
        $this->get(route('payments.receipt', $payment))->assertOk();
        $this->actingAs($otherApplicant)->get(route('payments.receipt', $payment))->assertForbidden();
    }

    public function test_reference_seeder_preserves_five_buildings_and_daily_rates(): void
    {
        $this->seed(\Database\Seeders\MarketReferenceSeeder::class);

        $this->assertSame(5, Building::count());
        $this->assertSame(208, Stall::count());
        $this->assertDatabaseHas('stalls', [
            'stall_number' => '1',
            'rental_rate' => '20.00',
            'payment_frequency' => 'daily',
            'source_occupant_name' => 'Mayflor Ramos',
        ]);
        $this->assertDatabaseHas('stalls', [
            'stall_number' => '233-C',
            'needs_verification' => 1,
            'status' => 'inactive',
        ]);
        $this->assertDatabaseHas('stalls', [
            'stall_number' => '251',
            'rental_rate' => '450.00',
            'status' => 'available',
        ]);
        $this->assertDatabaseHas('stalls', [
            'stall_number' => '246',
            'rental_rate' => '600.00',
            'stall_type' => 'Rice & Gilingan',
        ]);
    }

    private function registrationData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Market Vendor',
            'status' => 'Single',
            'citizenship' => 'Filipino',
            'birthdate' => today()->subYears(25)->toDateString(),
            'address' => 'Camalaniugan, Cagayan',
            'contact_number' => '09123456789',
            'email' => 'vendor@example.test',
            'username' => 'marketvendor',
            'password' => 'VendorPass123!',
            'password_confirmation' => 'VendorPass123!',
        ], $overrides);
    }

    private function workflowFixture(int $sharedCapacity = 1): array
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $first = $this->makeApplicant('first@example.test', 'firstvendor');
        $second = $this->makeApplicant('second@example.test', 'secondvendor');
        $building = Building::create(['code' => 'A', 'name' => 'Building A', 'is_active' => true]);
        $stall = Stall::create([
            'building_id' => $building->id,
            'stall_number' => 'T-1',
            'location' => 'Building A',
            'monthly_rate' => 600,
            'rental_rate' => 600,
            'payment_frequency' => 'monthly',
            'shared_capacity' => $sharedCapacity,
            'status' => 'available',
        ]);

        return [$admin, $first, $second, $stall];
    }

    private function makeApplicant(string $email, string $username): User
    {
        $user = User::factory()->create(['email' => $email, 'username' => $username, 'role' => 'user']);
        Registrant::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'status' => 'Single',
            'citizenship' => 'Filipino',
            'birthdate' => today()->subYears(25),
            'address' => 'Camalaniugan',
            'contact_number' => '09123456789',
        ]);

        return $user->load('registrant');
    }

    private function makeRegistrantFor(User $user): Registrant
    {
        return Registrant::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'status' => 'Single',
            'citizenship' => 'Filipino',
            'birthdate' => today()->subYears(25),
            'address' => 'Camalaniugan',
            'contact_number' => '09123456789',
        ]);
    }
}
