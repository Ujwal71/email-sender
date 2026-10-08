<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_campaign_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('status', 20)->default('pending'); // pending | sent | failed
            $table->text('error')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->unique(['email_campaign_id', 'email']);
            $table->index(['email_campaign_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_recipients');
    }
};
