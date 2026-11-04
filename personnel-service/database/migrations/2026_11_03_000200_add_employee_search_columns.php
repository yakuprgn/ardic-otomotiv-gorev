<?php

use App\Models\Employee;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('first_name_search', 150)->nullable()->index();
            $table->string('last_name_search', 150)->nullable()->index();
            $table->string('email_search', 255)->nullable()->index();
            $table->string('department_search', 150)->nullable()->index();
        });

        DB::table('employees')->orderBy('id')->chunkById(100, function ($employees): void {
            foreach ($employees as $employee) {
                DB::table('employees')->where('id', $employee->id)->update([
                    'first_name_search' => Employee::normalizeForSearch($employee->first_name),
                    'last_name_search' => Employee::normalizeForSearch($employee->last_name),
                    'email_search' => Employee::normalizeForSearch($employee->email),
                    'department_search' => Employee::normalizeForSearch($employee->department),
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'first_name_search', 'last_name_search', 'email_search', 'department_search',
            ]);
        });
    }
};
