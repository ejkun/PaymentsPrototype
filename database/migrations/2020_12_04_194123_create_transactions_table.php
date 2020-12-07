<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payer_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->foreignId('payee_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->float('value');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payer_id');
            $table->dropConstrainedForeignId('payee_id');
        });
        Schema::dropIfExists('transactions');
    }
}
