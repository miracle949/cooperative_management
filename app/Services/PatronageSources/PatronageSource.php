<?php

namespace App\Services\PatronageSources;

interface PatronageSource
{
    /**
     * Human-readable name of this patronage source.
     */
    public function getName(): string;

    /**
     * Calculate patronage for a specific member in a given year.
     */
    public function getPatronageForYear(int $userId, int $year): float;

    /**
     * Calculate total patronage across all members for a given year.
     */
    public function getTotalPatronageForYear(int $year): float;

    /**
     * Return an associative array of [user_id => patronage_amount]
     * for all members in the given year.
     */
    public function getAllPatronageForYear(int $year): array;
}
