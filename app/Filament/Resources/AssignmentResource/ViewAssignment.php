<?php

namespace App\Filament\Resources\AssignmentResource\Pages;

use App\Filament\Resources\AssignmentResource;
use App\Models\Asset;
use App\Models\AssetStatus;
use App\Models\Assignment;
use App\Models\Purchase;
use App\Models\Lifecycle;
use App\Models\Vendor;
use App\Models\AssignmentStatus;
use App\Models\Employee;
use App\Models\HardwareType;
use App\Models\SoftwareType;
use App\Models\LicenseType;
use App\Models\PeripheralType;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\DateTimeEntry;
use Illuminate\Support\Carbon;

class ViewAssignment extends ViewRecord
{
    protected static string $resource = AssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        Log::info('View Record View Data: ', $this->record->toArray());
        return $infolist
            ->schema([
                Section::make('Assignment Details')
                    ->schema([
                        TextEntry::make('asset.brand')
                            ->label('ID | Asset')
                            ->getStateUsing(function (Assignment $record): string {
                                $asset = $record->asset;
                                return $asset ? "{$asset->id} {$asset->brand} {$asset->model}" : 'N/A';
                            }),
                        TextEntry::make('employee_id')
                            ->label('ID | Employee')
                            ->getStateUsing(function (Assignment $record): string {
                                $employee = $record->employee;
                                return $employee ? "{$employee->id} {$employee->name}" : 'N/A';
                            }),
                        TextEntry::make('start_date')
                            ->label('Start Date')
                            ->getStateUsing(function (Assignment $record): string {
                                $startDate = Carbon::parse($record->start_date);
                                return $startDate ? $startDate->format('m/d/Y') : 'N/A';
                            }),
                        TextEntry::make('end_date')
                            ->label('End Date')
                            ->getStateUsing(function (Assignment $record): string {
                                $endDate = Carbon::parse($record->end_date);
                                return $endDate ? $endDate->format('m/d/Y') : 'N/A';
                            }),
                        TextEntry::make('assignment_status')
                            ->label('Assignment Status')
                            ->getStateUsing(function (Assignment $record): string {
                                $assignmentStatus = AssignmentStatus::find($record->assignment_status);
                                return $assignmentStatus ? $assignmentStatus->assignment_status : 'N/A';
                            })
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'Active' => 'success',
                                'Inactive' => 'primary',
                                'In Transfer' => 'warning',
                                'Unknown' => 'gray'
                            }),

                    ])
                    ->columns(2),

                Section::make('Asset Details')
                    ->schema([
                        TextEntry::make('asset.asset_type')
                            ->label('Asset Type'),
                        TextEntry::make('asset.asset_status')
                            ->label('Asset Status')
                            ->getStateUsing(function (Assignment $record): string {
                                $assetStatus = AssetStatus::find($record->asset->asset_status);
                                return $assetStatus ? $assetStatus->asset_status : 'N/A';
                            }),
                        TextEntry::make('asset.brand')
                            ->label('Brand'),
                        TextEntry::make('asset.model')
                            ->label('Model'),
                    ])
                    ->columns(2),

                Section::make('Hardware Details')
                    ->schema([
                        TextEntry::make('hardware_type')
                            ->label('Hardware Type')
                            ->getStateUsing(function (Assignment $record): string {
                                if ($record->asset && $record->asset->hardware) {
                                    $hardwareType = HardwareType::find($record->asset->hardware->hardware_type);
                                    return $hardwareType ? $hardwareType->hardware_type : 'N/A';
                                }
                                return 'N/A';
                            }),
                        TextEntry::make('serial_number')
                            ->label('Serial No.')
                            ->getStateUsing(function (Assignment $record): string {
                                if ($record->asset) {
                                    if ($record->asset->hardware) {
                                        return $record->asset->hardware->serial_number ?? 'N/A';
                                    }
                                }
                                return 'N/A';
                            }),
                        TextEntry::make('specifications')
                            ->label('Specifications')
                            ->getStateUsing(function (Assignment $record): string {
                                if ($record->asset) {
                                    if ($record->asset->hardware) {
                                        return $record->asset->hardware->specifications ?? 'N/A';
                                    }
                                }
                                return 'N/A';
                            }),
                        TextEntry::make('manufacturer')
                            ->label('Manufacturer')
                            ->getStateUsing(function (Assignment $record): string {
                                if ($record->asset) {
                                    if ($record->asset->hardware) {
                                        return $record->asset->hardware->manufacturer ?? 'N/A';
                                    }
                                }
                                return 'N/A';
                            }),
                        TextEntry::make('warranty_expiration')
                            ->label('Warranty Expiration Date')
                            ->getStateUsing(function (Assignment $record): string {
                                if ($record->asset) {
                                    if ($record->asset->hardware) {
                                        return $record->asset->hardware->warranty_expiration ?? 'N/A';
                                    }
                                }
                                return 'N/A';
                            }),
                    ])
                    ->columns(2)
                    ->visible(function ($record): bool {
                        return $record->asset && $record->asset->asset_type === 'hardware';
                    }),

                Section::make('Software Details')
                    ->schema([
                        TextEntry::make('software.version')
                            ->label('Version')
                            ->getStateUsing(function (Assignment $record): string {
                                if ($record->asset) {
                                    if ($record->asset->software) {
                                        return $record->asset->software->version ?? 'N/A';
                                    }
                                }
                                return 'N/A';
                            }),
                        TextEntry::make('software.license_key')
                            ->label('License Key')
                            ->getStateUsing(function (Assignment $record): string {
                                if ($record->asset) {
                                    if ($record->asset->software) {
                                        return $record->asset->software->license_key ?? 'N/A';
                                    }
                                }
                                return 'N/A';
                            }),
                        TextEntry::make('software_type')
                            ->label('Software Type')
                            ->getStateUsing(function (Assignment $record): string {
                                if ($record->asset && $record->asset->software) {
                                    $softwareType = SoftwareType::find($record->asset->software->software_type);
                                    return $softwareType ? $softwareType->software_type : 'N/A';
                                }
                                return 'N/A';
                            }),
                        TextEntry::make('license_type')
                            ->label('License Type')
                            ->getStateUsing(function (Assignment $record): string {
                                if ($record->asset && $record->asset->software) {
                                    $licenseType = LicenseType::find($record->asset->software->license_type);
                                    return $licenseType ? $licenseType->license_type : 'N/A';
                                }
                                return 'N/A';
                            }),
                    ])
                    ->columns(2)
                    ->visible(function ($record): bool {
                        return $record->asset && $record->asset->asset_type === 'software';
                    }),

                Section::make('Peripherals Details')
                    ->schema([
                        TextEntry::make('peripherals_type')
                            ->label('Peripherals Type')
                            ->getStateUsing(function (Assignment $record): string {
                                if ($record->asset && $record->asset->peripherals) {
                                    $peripheralsType = PeripheralType::find($record->asset->peripherals->peripherals_type);
                                    return $peripheralsType ? $peripheralsType->peripherals_type : 'N/A';
                                }
                                return 'N/A';
                            }),
                        TextEntry::make('serial_number')
                            ->label('Serial No.')
                            ->getStateUsing(function (Assignment $record): string {
                                if ($record->asset) {
                                    if ($record->asset->peripherals) {
                                        return $record->asset->peripherals->serial_number ?? 'N/A';
                                    }
                                }
                                return 'N/A';
                            }),
                        TextEntry::make('specifications')
                            ->label('Specifications')
                            ->getStateUsing(function (Assignment $record): string {
                                if ($record->asset) {
                                    if ($record->asset->peripherals) {
                                        return $record->asset->peripherals->specifications ?? 'N/A';
                                    }
                                }
                                return 'N/A';
                            }),
                        TextEntry::make('manufacturer')
                            ->label('Manufacturer')
                            ->getStateUsing(function (Assignment $record): string {
                                if ($record->asset) {
                                    if ($record->asset->peripherals) {
                                        return $record->asset->peripherals->manufacturer ?? 'N/A';
                                    }
                                }
                                return 'N/A';
                            }),
                        TextEntry::make('warranty_expiration')
                            ->label('Warranty Expiration Date')
                            ->getStateUsing(function (Assignment $record): string {
                                if ($record->asset) {
                                    if ($record->asset->peripherals) {
                                        return $record->asset->peripherals->warranty_expiration ?? 'N/A';
                                    }
                                }
                                return 'N/A';
                            }),
                    ])
                    ->columns(2)
                    ->visible(function ($record): bool {
                        return $record->asset && $record->asset->asset_type === 'peripherals';
                    }),

                Section::make('Asset Lifecycle Information')
                    ->schema([
                        TextEntry::make('acquisition_date')
                            ->label('Acquisition Date')
                            ->getStateUsing(function (Assignment $record): string {
                                $lifecycle = Lifecycle::where('asset_id', $record->asset->id)->first();
                                return $lifecycle && $lifecycle->acquisition_date
                                    ? Carbon::parse($lifecycle->acquisition_date)->format('m/d/Y')
                                    : "N/A";
                            }),
                        TextEntry::make('retirement_date')
                            ->label('Retirement Date')
                            ->getStateUsing(function (Assignment $record): string {
                                $lifecycle = Lifecycle::where('asset_id', $record->asset->id)->first();
                                return $lifecycle && $lifecycle->retirement_date
                                    ? Carbon::parse($lifecycle->retirement_date)->format('m/d/Y')
                                    : "N/A";
                            }),
                    ])
                    ->columns(2),
            ]);
    }
}