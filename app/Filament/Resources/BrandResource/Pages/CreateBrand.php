<?php

namespace App\Filament\Resources\BrandResource\Pages;

use App\Filament\Resources\BrandResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBrand extends CreateRecord
{
    protected static string $resource = BrandResource::class;

    protected function afterCreate(): void
    {
        // Clear any cached data if needed
        cache()->forget('brands');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ensure the image is properly handled
        if (isset($data['image']) && $data['image'] === '') {
            $data['image'] = null;
        }

        return $data;
    }
}
