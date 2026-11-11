<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('applications', function (Blueprint $t) {
            $t->id();
            $t->foreignId('registrant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('stall_id')->constrained()->cascadeOnDelete();
            $t->string('purpose');                     // type of business
            $t->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $t->text('remarks')->nullable();
            $t->timestamp('reviewed_at')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('applications'); }
};
