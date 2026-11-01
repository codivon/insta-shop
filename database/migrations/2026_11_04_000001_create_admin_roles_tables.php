<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Roles: named bundles of admin-panel permissions ──
        Schema::create('admin_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);                       // Persian display name
            $table->string('slug', 80)->unique();             // stable identifier
            $table->string('description', 255)->nullable();
            // Permission slugs (= admin route names) granted by this role.
            $table->json('permissions')->nullable();
            $table->boolean('is_system')->default(false);     // built-in roles can't be deleted
            $table->timestamps();
        });

        // ── Pivot: admins ⇄ roles ──
        Schema::create('admin_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('admins')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('admin_roles')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['admin_id', 'role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_role');
        Schema::dropIfExists('admin_roles');
    }
};
