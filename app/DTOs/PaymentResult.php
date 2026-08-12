<?php

namespace App\DTOs;

class PaymentResult
{
    public function __construct(
        public bool $success,
        public ?string $paymentId = null,
        public ?string $status = null, // e.g., 'paid', 'failed'
        public ?string $error = null
    ) {}
}
