<?php

use App\Models\Organization;
use App\Models\ParsingJob;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('review_snapshots', function(Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Organization::class)
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignIdFor(ParsingJob::class)
                ->constrained()
                ->cascadeOnDelete();

            $table->decimal('rating', 3)->nullable();
            $table->unsignedInteger('ratings_count')->nullable();
            $table->unsignedInteger('reviews_count')->nullable();

            $table->unsignedInteger('reviews_added')->default(0);
            $table->unsignedInteger('reviews_updated')->default(0);
            $table->unsignedInteger('reviews_removed')->default(0);

            $table->timestamp('captured_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_snapshots');
    }
};
