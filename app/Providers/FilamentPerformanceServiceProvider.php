<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Support\Assets\Js;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\Cache;

class FilamentPerformanceServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // Enable query caching for Filament resources
        $this->enableQueryCaching();

        // Optimize asset loading
        $this->optimizeAssets();

        // Configure global settings
        $this->configureGlobalSettings();
    }

    protected function enableQueryCaching()
    {
        // Cache common queries
        Cache::remember('filament_common_data', now()->addHour(), function () {
            return [
                'categories' => \App\Models\Category::all(),
                'brands' => \App\Models\Brand::all(),
            ];
        });
    }

    protected function optimizeAssets()
    {
        // Register and optimize assets
        FilamentAsset::register([
            // Add your custom assets here
        ]);

        // Enable asset versioning
        config(['filament.assets.should_version_assets' => true]);

        // Enable asset minification
        config(['filament.assets.should_minify_assets' => true]);
    }

    protected function configureGlobalSettings()
    {
        // Set global performance configurations
        config([
            'filament.layout.tables.pagination.default_records_per_page' => 10,
            'filament.layout.tables.is_striped' => true,
            'filament.layout.sidebar.is_collapsible_on_desktop' => true,
            'filament.layout.tables.reorder_column.is_enabled' => false,
        ]);
    }
}
