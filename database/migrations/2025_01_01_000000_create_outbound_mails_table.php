<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('outbound_mails', function (Blueprint $table) {
            $table->id();
            $table->string('sender_upn');
            $table->string('subject')->nullable();
            $table->string('template_key')->nullable();
            $table->json('template_data')->nullable();
            $table->enum('status', ['queued', 'sent', 'failed'])->default('queued');
            $table->json('to_recipients');
            $table->json('cc_recipients')->nullable();
            $table->json('bcc_recipients')->nullable();
            $table->longText('html_body')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outbound_mails');
    }
};
