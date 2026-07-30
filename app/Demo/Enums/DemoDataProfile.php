<?php

declare(strict_types=1);

namespace App\Demo\Enums;

enum DemoDataProfile: string
{
    case Minimal = 'minimal';
    case Standard = 'standard';
    case Large = 'large';

    /**
     * @return array{
     *     tenants:int,
     *     users:int,
     *     locations:int,
     *     customers:int,
     *     leads:int,
     *     invoices:int,
     *     payments:int,
     *     expenses:int,
     *     kpi_days:int
     * }
     */
    public function targets(): array
    {
        return match ($this) {
            self::Minimal => [
                'tenants' => 3,
                'users' => 25,
                'locations' => 20,
                'customers' => 150,
                'leads' => 100,
                'invoices' => 120,
                'payments' => 0,
                'expenses' => 50,
                'kpi_days' => 90,
            ],
            self::Standard => [
                'tenants' => 3,
                'users' => 120,
                'locations' => 35,
                'customers' => 2000,
                'leads' => 1200,
                'invoices' => 2500,
                'payments' => 900,
                'expenses' => 700,
                'kpi_days' => 365,
            ],
            self::Large => [
                'tenants' => 3,
                'users' => 1000,
                'locations' => 150,
                'customers' => 50000,
                'leads' => 25000,
                'invoices' => 75000,
                'payments' => 45000,
                'expenses' => 30000,
                'kpi_days' => 730,
            ],
        };
    }
}
