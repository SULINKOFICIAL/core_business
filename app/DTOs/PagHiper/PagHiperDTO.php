<?php

namespace App\DTOs\PagHiper;

use DateTime;

final class PagHiperDTO
{
    public function __construct(
        public readonly string    $transactionId,
        public readonly string    $orderId,
        public readonly int       $value,
        public readonly int       $valueFee,
        public readonly int       $discount,
        public readonly int       $paidValue,
        public readonly DateTime  $paidAt,
        public readonly string    $status,
        public readonly string    $type,
    ) {
    }
}
