<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('email');
        });

        Schema::create('buildings', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('stalls', function (Blueprint $table) {
            $table->foreignId('building_id')->nullable()->after('id')->constrained()->restrictOnDelete();
            $table->decimal('rental_rate', 10, 2)->nullable()->after('monthly_rate');
            $table->string('payment_frequency', 20)->default('monthly')->after('rental_rate');
            $table->string('stall_type', 80)->nullable()->after('payment_frequency');
            $table->string('shared_group', 30)->nullable()->after('stall_type');
            $table->unsignedSmallInteger('shared_capacity')->default(1)->after('stall_type');
            $table->string('source_occupant_name')->nullable()->after('shared_capacity');
            $table->boolean('needs_verification')->default(false)->after('shared_capacity');
            $table->text('source_notes')->nullable()->after('needs_verification');
            $table->unsignedInteger('display_order')->default(0)->after('source_notes');
            $table->string('status')->default('available')->change();
            $table->index(['building_id', 'status']);
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();
            $table->foreignId('reviewed_by')->nullable()->after('reviewed_at')->constrained('users')->nullOnDelete();
            $table->string('supporting_document')->nullable()->after('purpose');
        });

        Schema::table('leases', function (Blueprint $table) {
            $table->decimal('rental_amount', 10, 2)->nullable()->after('monthly_rate');
            $table->string('payment_frequency', 20)->default('monthly')->after('rental_amount');
            $table->foreignId('approved_by')->nullable()->after('payment_frequency')->constrained('users')->nullOnDelete();
            $table->string('status')->default('active')->change();
        });

        Schema::create('control_number_sequences', function (Blueprint $table) {
            $table->unsignedSmallInteger('year')->primary();
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();
        });
        $maxByYear = [];
        foreach (DB::table('registrants')->pluck('control_number') as $controlNumber) {
            if (preg_match('/^(\d{4})-(\d+)$/', $controlNumber, $matches)) {
                $year = (int) $matches[1];
                $maxByYear[$year] = max($maxByYear[$year] ?? 0, (int) $matches[2]);
            }
        }
        foreach ($maxByYear as $year => $lastNumber) {
            DB::table('control_number_sequences')->insert([
                'year' => $year,
                'last_number' => $lastNumber,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lease_id')->constrained()->restrictOnDelete();
            $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete();
            $table->string('reference_number')->unique();
            $table->decimal('amount', 10, 2);
            $table->date('payment_date');
            $table->string('period_covered', 80)->nullable();
            $table->string('method', 40)->nullable();
            $table->string('status', 20)->default('paid');
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->index(['lease_id', 'payment_date']);
        });

        Schema::create('stall_occupants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stall_id')->constrained()->restrictOnDelete();
            $table->foreignId('registrant_id')->constrained()->restrictOnDelete();
            $table->foreignId('lease_id')->constrained()->restrictOnDelete();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->timestamps();
            $table->index(['stall_id', 'end_date']);
            $table->unique(['lease_id', 'registrant_id']);
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 100);
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('details')->nullable();
            $table->timestamps();
            $table->index(['subject_type', 'subject_id']);
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('stall_occupants');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('control_number_sequences');

        Schema::table('leases', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn(['rental_amount', 'payment_frequency']);
            $table->enum('status', ['active', 'ended'])->default('active')->change();
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn('supporting_document');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->change();
        });

        Schema::table('stalls', function (Blueprint $table) {
            $table->dropIndex(['building_id', 'status']);
            $table->dropConstrainedForeignId('building_id');
            $table->dropColumn([
                'rental_rate', 'payment_frequency', 'stall_type', 'shared_group', 'shared_capacity', 'source_occupant_name',
                'needs_verification', 'source_notes', 'display_order',
            ]);
            $table->enum('status', ['available', 'occupied'])->default('available')->change();
        });

        Schema::dropIfExists('buildings');
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn('username');
        });
    }
};
