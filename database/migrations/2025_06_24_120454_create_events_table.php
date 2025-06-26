<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizer_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->dateTime('deadline');
            $table->enum('privacy', ['public', 'private'])
                ->default('public');
            $table->boolean('password_protected')->default(false);
            $table->string('password_hash')->nullable();
            $table->boolean('anonymous_contributions')->default(false);
            $table->boolean('show_contribution_breakdown')->default(true);
            $table->timestamps();
        });
    }
};
