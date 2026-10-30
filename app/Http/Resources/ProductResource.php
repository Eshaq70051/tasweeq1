<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $imageUrl = null;
        if ($this->image) {
            $imageUrl = str_starts_with($this->image, 'http')
                ? $this->image
                : asset('storage/' . $this->image);
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => (float) $this->price,
            'formatted_price' => number_format((float) $this->price, 2),
            'old_price' => $this->old_price ? (float) $this->old_price : null,
            'currency' => $this->currency ?? 'YER',
            'details' => $this->details,
            'image' => $this->image,
            'image_url' => $imageUrl,
            'stock' => (int) ($this->stock ?? 0),
            'low_stock_threshold' => (int) ($this->low_stock_threshold ?? 5),
            'in_stock' => ((int) ($this->stock ?? 0)) > 0,
            'is_low_stock' => (bool) $this->is_low_stock,
            'category_id' => $this->category_id,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
