<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductsExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(private ?int $tenantId = null)
    {
    }

    public function query()
    {
        $query = Product::withoutGlobalScope('tenant')
            ->with(['images'])
            ->orderBy('id');

        $user = auth()->user();
        if ($user && $user->isAdmin()) {
            return $query;
        }

        if ($this->tenantId === null) {
            return $query->whereNull('subscriber_id');
        }

        return $query->where('subscriber_id', $this->tenantId);
    }

    public function headings(): array
    {
        return [
            'Product Name',
            'SKU',
            'Slug',
            'Part Code',
            'Part Number',
            'Brand',
            'Category',
            'Subcategory',
            'MRP',
            'Offer Price',
            'MOQ',
            'Stock Quantity',
            'Stock Status',
            'Short Description',
            'Full Description',
            'Status',
            'Featured',
            'Featured Image',
            'Gallery Image 1',
            'Gallery Image 2',
            'Gallery Image 3',
            'Tags',
            'Weight',
            'Colors',
            'Sizes',
            'Meta Title',
            'Meta Description',
            'Meta Keywords',
        ];
    }

    /**
     * @param  Product  $product
     */
    public function map($product): array
    {
        $gallery = $product->images->pluck('image')->filter()->values()->toArray();
        $specifications = json_decode($product->specifications, true) ?: [];
        $stockStatus = $product->stock > 0 ? 'in_stock' : 'out_of_stock';

        return [
            $product->name,
            $product->sku,
            $product->slug,
            $product->part_code ?? '',
            $product->part_number ?? '',
            $product->brands->pluck('name')->implode(', '),
            $product->categories->pluck('name')->implode(', '),
            $product->subcategories->pluck('name')->implode(', '),
            $product->mrp ?? '',
            $product->offer_price ?? '',
            $product->moq ?? 1,
            $product->stock ?? 0,
            $stockStatus,
            $product->short_description ?? '',
            $product->additional_info ?? ($product->description ?? ''),
            $product->status ? 1 : 0,
            $product->featured ? 1 : 0,
            $product->thumbnail ?? '',
            $gallery[0] ?? '',
            $gallery[1] ?? '',
            $gallery[2] ?? '',
            $product->tags ?? '',
            $specifications['Weight'] ?? '',
            $specifications['Colors'] ?? '',
            $specifications['Sizes'] ?? '',
            $product->meta_title ?? '',
            $product->meta_description ?? '',
            $product->meta_keywords ?? '',
        ];
    }
}
