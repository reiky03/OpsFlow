<?php

use App\UserStatus;
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
        Schema::table('users', function (Blueprint $table) {
            $table->string('employee_number')->nullable()->unique()->after('email');
            $table->foreignId('department_id')->nullable()->after('employee_number')->constrained()->nullOnDelete();
            $table->string('job_title')->nullable()->after('department_id');
            $table->string('status')->default(UserStatus::Active->value)->index()->after('job_title');
            $table->timestamp('last_login_at')->nullable()->after('status');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('department_id');
            $table->dropUnique(['employee_number']);
            $table->dropIndex(['status']);
            $table->dropColumn([
                'employee_number',
                'job_title',
                'status',
                'last_login_at',
                'deleted_at',
            ]);
        });
    }
};
