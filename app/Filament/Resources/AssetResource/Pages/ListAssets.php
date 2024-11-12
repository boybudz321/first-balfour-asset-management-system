<?php

namespace App\Filament\Resources\AssetResource\Pages;

use App\Filament\Resources\AssetResource;
use Filament\Actions;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\AssetResource\Actions\ImportAssetsAction;
use Filament\Resources\Components\Tab;

class ListAssets extends ListRecords
{
    protected static string $resource = AssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportAssetsAction::make(),
            Actions\CreateAction::make(),
            // ActionGroup::make([
            //     Action::make('createHardware')
            //         ->label('Hardware')
            //         ->icon('heroicon-m-server-stack')
            //         ->url(route('filament.admin.resources.hardware.create')),

            //     Action::make('createSoftware')
            //         ->label('Software')
            //         ->icon('heroicon-m-cpu-chip')
            //         ->url(route('filament.admin.resources.software.create')),

            //     Action::make('createPeripherals')
            //         ->label('Peripherals')
            //         ->icon('heroicon-m-squares-2x2')
            //         ->url(route('filament.admin.resources.peripherals.create')),
            // ])
            //     ->label('New Asset')
            //     ->icon('heroicon-m-chevron-down')
            //     ->button()
            //     ->color('primary')
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Assets'),
            'hardware' => Tab::make('Hardware')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('asset_type', 'hardware')),
            'software' => Tab::make('Software')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('asset_type', 'software')),
            'peripherals' => Tab::make('Peripherals')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('asset_type', 'peripherals')),
        ];
    }
}
