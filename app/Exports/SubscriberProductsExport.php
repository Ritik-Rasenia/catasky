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
            'Category',
            'Subcategory',
            'Product Name',
            'Slug',
            'SKU',
            'MRP',
            'Offer Price',
            'Short Description',
            'Full Description',
            'Status',
            'Featured',
            'Featured Image',
            'Gallery Images',
            'Tags',
        ];
    }
 
    /**
     * @param SubscriberProduct $product
     */
    public function map($product): array
    {
        $gallery = $product->images->pluck('image_path')->filter()->implode(', ');
        $tags = is_array($product->tags) ? implode(', ', $product->tags) : ($product->tags ?? '');
 
        return [
            $product->category->name ?? '',
            $product->subcategory->name ?? '',
            $product->name,
            $product->slug,
            $product->sku ?? '',
            $product->mrp ?? '',
            $product->offer_price ?? '',
            $product->short_description ?? '',
            $product->full_description ?? '',
            $product->status ?? 'draft',
            $product->featured ? 'yes' : 'no',
            $product->thumbnail ?? '',
            $gallery,
            $tags,
        ];
    }
}
