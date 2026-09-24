<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">
    <channel>
        <title>C.E.S. Container</title>
        <link>{{ route('shop') }}</link>
        <description>Container marittimi, moduli e attrezzature disponibili da C.E.S. Container.</description>
        @foreach($products as $product)
            @php
                $description = trim(strip_tags((string) $product->localizedDescription()));
                $condition = in_array(mb_strtolower((string) $product->etat), ['neuf', 'nuovo', 'new'], true) ? 'new' : 'used';
            @endphp
            @if($description !== '')
                <item>
                    <g:id>{{ $product->id }}</g:id>
                    <g:title>{{ $product->reference }}{{ $product->category ? ' - '.$product->category->nom : '' }}{{ $product->dimensions ? ' - '.$product->dimensions : '' }}</g:title>
                    <g:description>{{ $description }}</g:description>
                    <g:link>{{ route('shop.show', $product) }}</g:link>
                    <g:image_link>{{ asset($product->image_principale) }}</g:image_link>
                    @foreach(array_slice($product->images_secondaires ?? [], 0, 10) as $image)
                        <g:additional_image_link>{{ asset($image) }}</g:additional_image_link>
                    @endforeach
                    <g:availability>in_stock</g:availability>
                    <g:price>{{ number_format((float) $product->prix_vente * (1 + $vatRate / 100), 2, '.', '') }} EUR</g:price>
                    <g:condition>{{ $condition }}</g:condition>
                    <g:brand>C.E.S. Container</g:brand>
                    <g:identifier_exists>no</g:identifier_exists>
                </item>
            @endif
        @endforeach
    </channel>
</rss>
