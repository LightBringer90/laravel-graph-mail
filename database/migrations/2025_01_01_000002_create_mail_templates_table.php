<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mail_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('module')->nullable();
            $table->string('mailable_class')->nullable();
            $table->string('view')->nullable();
            $table->string('default_subject')->nullable();
            $table->json('default_data')->nullable();
            $table->json('to')->nullable();
            $table->json('cc')->nullable();
            $table->json('bcc')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->index(['module', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_templates');
    }
};
