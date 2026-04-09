<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reading_levels', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('level');
            $table->string('label');
            $table->unsignedTinyInteger('accuracy_threshold');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reading_levels');
    }
};