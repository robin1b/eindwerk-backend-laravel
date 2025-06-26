<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContributionsTable extends Migration
{
    public function up()
    {
        Schema::create('contributions', function (Blueprint $table) {
            $table->id();
            // Verwijst naar je events-tabel
            $table->foreignId('event_id')
                ->constrained()
                ->onDelete('cascade');
            // Optioneel voor later; voor nu mag je dit nullable maken:
            $table->unsignedBigInteger('user_id')->nullable();
            $table->decimal('amount', 10, 2);
            // Voor guest-flow kun je anonymous weglaten of default zetten:
            $table->boolean('anonymous')->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->string('status')->default('pending');        // pending, paid, failed
            $table->string('stripe_payment_intent_id')->nullable();
            $table->string('stripe_charge_id')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('contributions');
    }
}
