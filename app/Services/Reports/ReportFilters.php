<?php

namespace App\Services\Reports;

use Carbon\CarbonImmutable;
use InvalidArgumentException;

final readonly class ReportFilters
{
    public CarbonImmutable $desde;

    public CarbonImmutable $hasta;

    public function __construct(CarbonImmutable $desde, CarbonImmutable $hasta)
    {
        $normalizedStart = $desde->startOfDay();
        $normalizedEnd = $hasta->endOfDay()->microsecond(0);

        if ($normalizedStart->gt($normalizedEnd)) {
            throw new InvalidArgumentException('La fecha inicial no puede ser posterior a la final.');
        }

        $this->desde = $normalizedStart;
        $this->hasta = $normalizedEnd;
    }

    public static function fromDates(?string $desde = null, ?string $hasta = null): self
    {
        $today = CarbonImmutable::today();

        $parsedDesde = ($desde !== null && $desde !== '')
            ? CarbonImmutable::createFromFormat('!Y-m-d', $desde)
            : null;

        $parsedHasta = ($hasta !== null && $hasta !== '')
            ? CarbonImmutable::createFromFormat('!Y-m-d', $hasta)
            : null;

        if ($parsedDesde === null && $parsedHasta === null) {
            return new self($today->startOfMonth(), $today);
        }

        if ($parsedDesde === null) {
            return new self($parsedHasta->startOfMonth(), $parsedHasta);
        }

        if ($parsedHasta === null) {
            $effectiveEnd = $parsedDesde->gt($today) ? $parsedDesde : $today;

            return new self($parsedDesde, $effectiveEnd);
        }

        return new self($parsedDesde, $parsedHasta);
    }

    public function daysInRange(): int
    {
        return (int) round($this->desde->startOfDay()->diffInDays($this->hasta->startOfDay())) + 1;
    }

    public function groupsByDay(): bool
    {
        return $this->daysInRange() <= 62;
    }

    /**
     * @return array{desde: string, hasta: string}
     */
    public function toQuery(): array
    {
        return [
            'desde' => $this->desde->toDateString(),
            'hasta' => $this->hasta->toDateString(),
        ];
    }
}
