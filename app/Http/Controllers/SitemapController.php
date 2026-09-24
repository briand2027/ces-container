<?php

namespace App\Http\Controllers;

use App\Models\Container;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        return response()
            ->view('seo.sitemap', [
                'products' => Container::query()
                    ->where('statut', 'disponible')
                    ->whereNotNull('image_principale')
                    ->orderByDesc('updated_at')
                    ->get(['id', 'updated_at']),
            ])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
