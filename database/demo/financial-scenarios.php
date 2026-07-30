<?php

declare(strict_types=1);

return [
    'expense_categories' => [
        'payroll',
        'fuel',
        'equipment',
        'software',
        'marketing',
        'insurance',
        'rent',
    ],
    'invoice_statuses' => [
        'draft',
        'sent',
        'paid',
        'overdue',
    ],
    'payment_methods' => [
        'ach',
        'credit_card',
        'check',
        'wire',
    ],
    'ticket_amount_range' => [
        'min' => 125.00,
        'max' => 24500.00,
    ],
    'expense_amount_range' => [
        'min' => 55.00,
        'max' => 9800.00,
    ],
];
