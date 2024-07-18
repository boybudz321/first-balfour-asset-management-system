<?php

namespace App\Filament\Resources\HardwareResource\Pages;

use App\Filament\Resources\HardwareResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use App\Models\Asset;
use App\Models\Hardware;

class CreateHardware extends CreateRecord
{
    protected static string $resource = HardwareResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model

    {
        if (isset($data['assets'])) {
            return $this->handleMultipleAssetCreation($data['assets']);
        }

        return $this->handleSingleAssetCreation($data);
    }

    protected function handleMultipleAssetCreation(array $assetsData)
    {
        $createdAssets = [];

        DB::transaction(function () use ($assetsData, &$createdAssets) {
            foreach ($assetsData as $assetData) {
                $createdAssets[] = $this->createSingleAsset($assetData);
            }
        });

        return end($createdAssets);
    }

    protected function handleSingleAssetCreation(array $data)
    {
        return DB::transaction(function () use ($data) {
            return $this->createSingleAsset($data);
        });
    }

    protected function createSingleAsset(array $data)
    {
        $asset = Asset::create([
            'asset_type' => 'hardware',
            'asset_status' => $data['asset_status'],
            'brand' => $data['brand'],
            'model' => $data['model'],
        ]);

        Hardware::create([
            'asset_id' => $asset->id,
            'specifications' => $data['specifications'],
            'serial_number' => $data['serial_number'],
            'manufacturer' => $data['manufacturer'],
            'warranty_expiration' => $data['warranty_expiration'],
        ]);

        return $asset;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
