<?php

use App\Models\Organization;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reviews', function(Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Organization::class)
                ->index('reviews-organization_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('external_id')->unique();
            $table->string('author')->nullable();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->text('text')->nullable();

            $table->timestamp('published_at')
                ->index('reviews-published_at')
                ->nullable();

            $table->string('content_hash', 64);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
