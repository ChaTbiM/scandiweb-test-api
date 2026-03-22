<?php

declare(strict_types=1);

namespace App\Models;

final class Price
{
    private float $amount;
    private string $currencyLabel;
    private string $currencySymbol;

    public function __construct(float $amount, string $currencyLabel, string $currencySymbol)
    {
        $this->amount = $amount;
        $this->currencyLabel = $currencyLabel;
        $this->currencySymbol = $currencySymbol;
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromRow(array $row): self
    {
        if (isset($row['currency']) && is_array($row['currency'])) {
            return new self(
                is_numeric((string) ($row['amount'] ?? null)) ? (float) $row['amount'] : 0.0,
                (string) ($row['currency']['label'] ?? ''),
                (string) ($row['currency']['symbol'] ?? '')
            );
        }

        $amount = $row['amount'] ?? 0;

        if (is_int($amount) || is_float($amount) || is_numeric((string) $amount)) {
            $amount = ((float) $amount) / 100;
        } else {
            $amount = 0.0;
        }

        return new self(
            (float) $amount,
            (string) ($row['currency_label'] ?? $row['currencyLabel'] ?? ''),
            (string) ($row['currency_symbol'] ?? $row['currencySymbol'] ?? '')
        );
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getCurrencyLabel(): string
    {
        return $this->currencyLabel;
    }

    public function getCurrencySymbol(): string
    {
        return $this->currencySymbol;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
            'currency' => [
                'label' => $this->currencyLabel,
                'symbol' => $this->currencySymbol,
            ],
        ];
    }
}
