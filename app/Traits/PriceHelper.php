<?php

namespace App\Traits;

trait PriceHelper
{
    /**
     * Format price with currency symbol
     */
    public function getFormattedPriceWithCurrency(): string
    {
        $symbols = [
            'KES' => 'KSh ',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
        ];

        $symbol = $symbols[$this->currency] ?? $this->currency . ' ';

        return $symbol . number_format($this->price, 0, '.', ',');
    }

    /**
     * Get price in millions format (e.g., "2.5M")
     */
    public function getPriceInMillions(): string
    {
        $millions = $this->price / 1000000;

        if ($millions >= 1) {
            return number_format($millions, 1) . 'M';
        }

        $thousands = $this->price / 1000;
        return number_format($thousands, 0) . 'K';
    }

    /**
     * Get price per square meter/foot
     */
    public function getPricePerUnit(): ?float
    {
        if (!$this->area || $this->area == 0) {
            return null;
        }

        return round($this->price / $this->area, 2);
    }

    /**
     * Format price per unit
     */
    public function getFormattedPricePerUnit(): ?string
    {
        $pricePerUnit = $this->getPricePerUnit();

        if (!$pricePerUnit) {
            return null;
        }

        return number_format($pricePerUnit, 0) . ' per sq m';
    }

    /**
     * Check if property is affordable based on budget
     */
    public function isAffordable(float $budget): bool
    {
        return $this->price <= $budget;
    }

    /**
     * Get price range category
     */
    public function getPriceCategory(): string
    {
        $price = $this->price;

        if ($price < 5000000) return 'Budget';
        if ($price < 20000000) return 'Mid-Range';
        if ($price < 50000000) return 'Premium';
        return 'Luxury';
    }

    /**
     * Calculate monthly mortgage estimate (simple calculation)
     *
     * @param float $downPaymentPercent Down payment as percentage (e.g., 20)
     * @param float $interestRate Annual interest rate (e.g., 12.5)
     * @param int $years Loan term in years (e.g., 25)
     */
    public function estimateMonthlyMortgage(
        float $downPaymentPercent = 20,
        float $interestRate = 12.5,
        int $years = 25
    ): float {
        $downPayment = $this->price * ($downPaymentPercent / 100);
        $loanAmount = $this->price - $downPayment;

        $monthlyRate = ($interestRate / 100) / 12;
        $numberOfPayments = $years * 12;

        // Monthly payment formula: P * [r(1+r)^n] / [(1+r)^n – 1]
        $monthlyPayment = $loanAmount *
            ($monthlyRate * pow(1 + $monthlyRate, $numberOfPayments)) /
            (pow(1 + $monthlyRate, $numberOfPayments) - 1);

        return round($monthlyPayment, 2);
    }

    /**
     * Get formatted monthly mortgage
     */
    public function getFormattedMonthlyMortgage(
        float $downPaymentPercent = 20,
        float $interestRate = 12.5,
        int $years = 25
    ): string {
        $monthly = $this->estimateMonthlyMortgage($downPaymentPercent, $interestRate, $years);
        return 'KSh ' . number_format($monthly, 0, '.', ',') . '/month';
    }
}
