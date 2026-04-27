<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::table('customers', function (Blueprint $table) {
    if (!Schema::hasColumn('customers', 'total_spent')) {
        $table->decimal('total_spent', 16, 2)->default(0);
    }
    if (!Schema::hasColumn('customers', 'total_paid')) {
        $table->decimal('total_paid', 16, 2)->default(0);
    }
    if (!Schema::hasColumn('customers', 'due')) {
        $table->decimal('due', 16, 2)->default(0);
    }
});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
         Schema::table('customers', function (Blueprint $table) {
        $table->dropColumn(['total_spent', 'total_paid', 'due']);
    });
    }
};
