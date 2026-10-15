<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'track_id'     => $this->track_id,
            'uuid'         => $this->uuid,
            'rfc_receptor' => $this->supplier?->rfc,
            'currency'     => $this->currency,
            'total'        => (float) $this->total,
            'division'     => $this->clasification?->location . ' - ' . $this->clasification?->air,
            'status_sat'   => $this->status_sat,
            'date'         => $this->date?->toDateString(),
        ];
    }
}