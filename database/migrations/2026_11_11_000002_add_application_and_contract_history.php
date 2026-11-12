<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->foreignId('lease_id')->nullable()->unique()->constrained()->restrictOnDelete();
        });

        Schema::table('leases', function (Blueprint $table) {
            $table->foreignId('application_id')->nullable()->after('registrant_id')
                ->constrained()->restrictOnDelete();
            $table->index('application_id');
            $table->json('tenant_details')->nullable();
            $table->text('contract_terms')->nullable();
            $table->foreignId('renewed_from_lease_id')->nullable()->unique()
                ->constrained('leases')->restrictOnDelete();
            $table->timestamp('terminated_at')->nullable();
            $table->foreignId('terminated_by')->nullable()->constrained('users')->nullOnDelete();
        });

        Schema::create('application_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->restrictOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->text('remarks')->nullable();
            $table->foreignId('acted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['application_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_status_histories');

        Schema::table('leases', function (Blueprint $table) {
            $table->dropConstrainedForeignId('terminated_by');
            $table->dropConstrainedForeignId('renewed_from_lease_id');
            $table->dropConstrainedForeignId('application_id');
            $table->dropColumn(['tenant_details', 'contract_terms', 'terminated_at']);
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lease_id');
        });
    }
};
