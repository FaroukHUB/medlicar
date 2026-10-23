<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Parrainage : code par client + suivi des parrainages + récompenses. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('referral_code')->nullable()->unique()->after('phone');
        });

        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('referred_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('reward_amount', 10, 2)->default(0);
            $table->enum('status', ['pending', 'validated', 'cancelled'])->default('pending');
            $table->timestamps();
        });

        Schema::table('agency', function (Blueprint $table) {
            $table->boolean('referral_enabled')->default(false);
            $table->decimal('referral_reward', 10, 2)->default(0);   // récompense au parrain
            $table->decimal('referred_discount', 10, 2)->default(0); // remise au filleul
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');
        Schema::table('customers', fn (Blueprint $t) => $t->dropColumn('referral_code'));
        Schema::table('agency', fn (Blueprint $t) => $t->dropColumn(['referral_enabled', 'referral_reward', 'referred_discount']));
    }
};
