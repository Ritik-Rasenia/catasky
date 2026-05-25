<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <!-- Main Home Page -->
    <url>
        <loc>{{ route('home') }}</loc>
        <lastmod>{{ now()->toDateString() }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>

    <!-- Catalogue Overview -->
    <url>
        <loc>{{ route('catalogue') }}</loc>
        <lastmod>{{ now()->toDateString() }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>

    <!-- Dynamic Categories -->
    @foreach ($categories as $category)
        <url>
            <loc>{{ route('category.products', $category->slug) }}</loc>
            <lastmod>{{ $category->updated_at->toDateString() }}</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.8</priority>
        </url>
    @endforeach

    <!-- Dynamic Subcategories -->
    @foreach ($subcategories as $subcategory)
        <url>
            <loc>{{ route('subcategory.products', $subcategory->slug) }}</loc>
            <lastmod>{{ $subcategory->updated_at->toDateString() }}</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.7</priority>
        </url>
    @endforeach

    <!-- Dynamic Products -->
    @foreach ($products as $product)
        <url>
            <loc>{{ route('product.details', $product->slug) }}</loc>
            <lastmod>{{ $product->updated_at->toDateString() }}</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.8</priority>
        </url>
    @endforeach
</urlset>
