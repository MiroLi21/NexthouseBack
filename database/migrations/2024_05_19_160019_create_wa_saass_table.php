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
        Schema::create('wa_saass', function (Blueprint $table) {
            $table->id();
            $table->string('session_key')->nullable()->default("");
            $table->string('path_url')->nullable()->default("");
            $table->string('xkey')->nullable()->default(""); 
            $table->boolean('enable')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wa_saass');
    }
};
