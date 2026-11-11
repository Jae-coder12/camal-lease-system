<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('leases', function (Blueprint $t) {
            $t->id();
            $t->string('contract_number')->nullable()->unique();
            $t->foreignId('stall_id')->constrained()->cascadeOnDelete();
            $t->foreignId('registrant_id')->constrained()->cascadeOnDelete();
            $t->date('start_date');
            $t->date('end_date')->nullable();
            $t->decimal('monthly_rate', 10, 2);
            $t->enum('status', ['active', 'ended'])->default('active');
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('leases'); }
};
