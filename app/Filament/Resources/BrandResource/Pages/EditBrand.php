<?php

namespace App\Filament\Resources\BrandResource\Pages;

use App\Filament\Resources\BrandResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditBrand extends EditRecord
{
    protected static string $resource = BrandResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        // Clear any cached data if needed
        cache()->forget('brands');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Ensure the image is properly handled
        if (isset($data['image']) && $data['image'] === '') {
            $data['image'] = null;
        }

        return $data;
    }
}
