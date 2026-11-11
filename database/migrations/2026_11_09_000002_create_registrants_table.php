<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('registrants', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('control_number')->unique();   // e.g. 2026-001
            $t->string('name');
            $t->string('status');                      // civil status
            $t->string('citizenship');
            $t->date('birthdate');
            $t->string('address');
            $t->string('contact_number');
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('registrants'); }
};
