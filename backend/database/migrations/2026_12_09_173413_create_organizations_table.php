<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(User::class)
                ->constrained()
                ->cascadeOnDelete();

            $table->string('source_url', 1024);
            $table->string('normalized_url', 255)->unique();

            $table->string('yandex_permalink')->nullable()->index();

            $table->string('name')->nullable();
            $table->decimal('rating', 3)->nullable();
            $table->integer('ratings_count')->nullable();
            $table->integer('reviews_count')->nullable();

            $table->string('parsing_status')->default('pending');
            $table->text('parsing_error')->nullable();
            $table->timestamp('last_parsed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
