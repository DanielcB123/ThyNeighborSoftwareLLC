<?php

declare(strict_types=1);

return [
    'kpi_metrics' => [
        'revenue',
        'gross_margin',
        'jobs_completed',
        'average_ticket',
        'lead_conversion_rate',
        'quote_to_job_rate',
        'customer_satisfaction_score',
    ],
    'metric_ranges' => [
        'revenue' => ['min' => 3000, 'max' => 150000],
        'gross_margin' => ['min' => 0.18, 'max' => 0.65],
        'jobs_completed' => ['min' => 10, 'max' => 140],
        'average_ticket' => ['min' => 180, 'max' => 2200],
        'lead_conversion_rate' => ['min' => 0.08, 'max' => 0.52],
        'quote_to_job_rate' => ['min' => 0.1, 'max' => 0.58],
        'customer_satisfaction_score' => ['min' => 3.4, 'max' => 4.95],
    ],
];
