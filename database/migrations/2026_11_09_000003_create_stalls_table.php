<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('stalls', function (Blueprint $t) {
            $t->id();
            $t->string('stall_number')->unique();
            $t->string('location');
            $t->decimal('size_sqm', 8, 2)->default(0);
            $t->decimal('monthly_rate', 10, 2);
            $t->text('description')->nullable();
            $t->enum('status', ['available', 'occupied'])->default('available');
            $t->foreignId('registrant_id')->nullable()->constrained()->nullOnDelete(); // current owner
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('stalls'); }
};
