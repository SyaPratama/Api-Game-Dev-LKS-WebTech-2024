<?php

use App\Models\Game;
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
        Schema::create('game__versions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Game::class,'game_id');
            $table->string('version');
            $table->string('storage_path');
            $table->timestamp('created_at')->default(now("Asia/Jakarta"));
            $table->timestamp('updated_at')->default(now('Asia/Jakarta'));
            $table->timestamp('deleted_at')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game__versions');
    }
};
