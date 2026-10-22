
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
        Schema::create('share_capital_transaction_tbls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('share_capital_account_id')
                ->constrained('share_capital_account_tbls')
                ->onDelete('cascade');
            $table->enum('type', ['Subscription', 'Deposit', 'Withdrawal'])->nullable();
            $table->decimal('shares', 10, 2)->nullable();
            $table->decimal('amount_per_share', 10, 2)->nullable();
            $table->decimal('total_amount', 12, 2)->nullable();
            $table->string('payment_method')->nullable();
            $table->string('reference_no')->nullable();
            $table->text('note')->nullable();
            $table->enum('status', ['Pending', 'Completed', 'Approved', 'Rejected'])->default('Pending');
            $table->date('transaction_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('share_capital_transaction_tbls');
    }
};
