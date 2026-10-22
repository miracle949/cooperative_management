<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('faqs_tbls', function (Blueprint $table) {
            $table->id();
            $table->string('category', 100);
            $table->string('question', 255);
            $table->text('answer');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed the FAQs that were previously hard-coded in UsersHandle::Faqs()
        // so the member FAQ page keeps working right after migrating.
        $now = now();
        $seed = [
            ['Savings & Share Capital', 'Why does my balance look different from what I deposited?', 'Deposits made via QR (GCash) stay "Pending" until an admin verifies the reference number and screenshot. Your balance only updates once the transaction is marked Completed.'],
            ['Savings & Share Capital', 'Can I withdraw my Share Capital anytime?', 'No. Share capital is your investment (sosyo) in the cooperative and is not withdrawable on demand. It can only be released in full upon approved resignation, after the 60-day holding period.'],
            ['Savings & Share Capital', 'What happens if I miss a quarterly Share Capital installment?', 'Missing a quarter simply shifts your Share Certificate eligibility date — no penalty is applied.'],
            ['Loans', 'How much Share Capital do I need to apply for a loan?', 'You need at least 25 shares in your Share Capital account before you can submit a loan application.'],
            ['Loans', 'Why was my payment not reflected on my loan balance?', 'Repayments are posted once confirmed by the finance officer. If it has been more than 24 hours, please use the Report a Problem button on your dashboard so we can check it directly.'],
            ['Deposits & Verification', 'My deposit was marked "Invalid" — what do I do?', 'Use the Report a Problem button on your dashboard, select "Payment Reflection", and attach your screenshot again along with the reference number. A Finance Officer will review it.'],
        ];

        foreach ($seed as $i => [$category, $question, $answer]) {
            DB::table('faqs_tbls')->insert([
                'category' => $category,
                'question' => $question,
                'answer' => $answer,
                'sort_order' => $i + 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('faqs_tbls');
    }
};