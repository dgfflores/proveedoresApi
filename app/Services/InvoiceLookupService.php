<?php

namespace App\Services;

use App\Models\Invoice;

class InvoiceLookupService
{
    public function find(string $rfcReceptor, ?string $uuid, ?string $trackId): ?Invoice
    {
        return Invoice::query()
            ->with(['supplier:id,rfc', 'clasification:id,location,air'])
            ->whereHas('supplier', fn ($q) => $q->where('rfc', $rfcReceptor))
            ->when($uuid, fn ($q) => $q->where('uuid', $uuid))
            ->when($trackId, fn ($q) => $q->where('track_id', $trackId))
            ->first();
    }
}