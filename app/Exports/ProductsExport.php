<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductsExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        return Product::query()
            ->with(['images'])
            ->orderBy('id');
    }

    public function headings(): array
    {
        return [
            'brand',
            'category',
            'sub_category',
            'name',
            'slug',
            'part_code',
            'part_number',
            'thumbnail',
            'gallery_images',
            'tags',
            'short_description',
            'price',
            'additional_info',
            'featured',
            'is_future',
            'meta_title',
            'meta_description',
            'meta_keywords',
            'status',
        ];
    }

    /**
     * @param  Product  $product
     */
    public function map($product): array
    {
        $gallery = $product->images->pluck('image')->filter()->implode(', ');

        return [
            $product->brand->name ?? '',
            $product->category->name ?? '',
            $product->subcategory->name ?? '',
            $product->name,
            $product->slug,
            $product->part_code ?? '',
            $product->part_number ?? '',
            $product->thumbnail ?? '',
            $gallery,
            $product->tags ?? '',
            $product->short_description ?? '',
            $product->price ?? '',
            $product->additional_info ?? '',
            $product->featured ? 1 : 0,
            $product->is_future ? 1 : 0,
            $product->meta_title ?? '',
            $product->meta_description ?? '',
            $product->meta_keywords ?? '',
            $product->status ? 1 : 0,
        ];
    }
}
