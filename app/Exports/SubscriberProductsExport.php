<?php

namespace App\Exports;

use App\Models\SubscriberProduct;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SubscriberProductsExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        return SubscriberProduct::query()
            ->where('user_id', auth()->id())
            ->with(['images'])
            ->orderBy('id');
    }

    public function headings(): array
    {
        return [
            'Product Name',
            'SKU',
            'Slug',
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
            'Meta Title',
            'Meta Description',
        ];
    }

    /**
     * @param SubscriberProduct $product
     */
    public function map($product): array
    {
        $gallery = $product->images->pluck('image_path')->filter()->values()->toArray();
        $tags = is_array($product->tags) ? implode(', ', $product->tags) : ($product->tags ?? '');
        $stockStatus = $product->stock_status ?? ($product->stock > 0 ? 'in_stock' : 'out_of_stock');

        return [
            $product->name,
            $product->sku,
            $product->slug,
            $product->brands->pluck('name')->implode(', '),
            $product->categories->pluck('name')->implode(', '),
            $product->subcategories->pluck('name')->implode(', '),
            $product->mrp ?? '',
            $product->offer_price ?? '',
            $product->moq ?? 1,
            $product->stock ?? 0,
            $stockStatus,
            $product->short_description ?? '',
            $product->full_description ?? '',
            $product->status ?? 'draft',
            $product->featured ? 'yes' : 'no',
            $product->thumbnail ?? '',
            $gallery[0] ?? '',
            $gallery[1] ?? '',
            $gallery[2] ?? '',
            $tags,
            $product->meta_title ?? '',
            $product->meta_description ?? '',
        ];
    }
}
