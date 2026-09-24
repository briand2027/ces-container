<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach([route('shop'), route('home'), route('services'), route('projects'), route('gallery'), route('about'), route('contact')] as $url)
        <url><loc>{{ $url }}</loc></url>
    @endforeach
    @foreach($products as $product)
        <url>
            <loc>{{ route('shop.show', $product) }}</loc>
            @if($product->updated_at)<lastmod>{{ $product->updated_at->toAtomString() }}</lastmod>@endif
        </url>
    @endforeach
</urlset>
