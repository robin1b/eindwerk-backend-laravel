<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('contributions', function (Blueprint $table) {
            // 1) Status-veld: pending, paid of failed
            $table->enum('status', ['pending', 'paid', 'failed'])
                ->default('pending')
                ->after('amount');

            // 2) Stripe IDs: sla later client- en charge-ID op
            $table->string('stripe_payment_intent_id')
                ->nullable()
                ->after('status');

            $table->string('stripe_charge_id')
                ->nullable()
                ->after('stripe_payment_intent_id');
        });
    }

    public function down()
    {
        Schema::table('contributions', function (Blueprint $table) {
            $table->dropColumn([
                'stripe_charge_id',
                'stripe_payment_intent_id',
                'status',
            ]);
        });
    }
};
