<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssetResource\Pages;
use App\Models\Asset;
use App\Models\AssetStatus;
use App\Models\HardwareType;
use App\Models\SoftwareType;
use App\Models\LicenseType;
use App\Models\PeripheralType;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Repeater;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use App\Filament\Resources\AssetResource\RelationManagers\AssignmentsRelationManager;
use Illuminate\Support\Facades\Log;

class AssetResource extends Resource
{
    protected static ?string $model = Asset::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Manage Assets';

    protected static ?int $navigationSort = 1;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Repeater::make('Asset Information')
                    ->schema([
                        Fieldset::make('Asset Details')
                            ->schema([
                                Select::make('asset_type')
                                    ->options([
                                        'hardware' => 'Hardware',
                                        'software' => 'Software',
                                        'peripherals' => 'Peripherals',
                                    ])
                                    ->required()
                                    ->label('Asset Type')
                                    ->reactive()
                                    ->autofocus()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $set('show_hardware', $state === 'hardware');
                                        $set('show_software', $state === 'software');
                                        $set('show_peripherals', $state === 'peripherals');
                                    }),
                                Select::make('asset_status')
                                    ->label('Asset Status')
                                    ->options(function () {
                                        return AssetStatus::all()->pluck('asset_status', 'id');
                                    })
                                    ->default(1)
                                    ->required()
                                    ->createOptionForm([
                                        TextInput::make('asset_status')
                                            ->label('Asset Status')
                                            ->required(),
                                    ])
                                    ->createOptionUsing(function ($data) {
                                        $status = AssetStatus::create([
                                            'asset_status' => $data['asset_status'],
                                        ]);

                                        return $status->id;
                                    }),
                                TextInput::make('brand')->label('Brand')->required(),
                                TextInput::make('model')->label('Model')->required(),
                            ]),
                        Fieldset::make('Hardware Details')
                            ->hidden(fn (callable $get) => $get('show_hardware') !== true)
                            ->schema([
                                Select::make('hardware_type')->label('Hardware Type')
                                    ->options(HardwareType::all()->pluck('hardware_type', 'id')->toArray())
                                    ->required(),
                                TextInput::make('serial_number')->label('Serial No.')->required(),
                                TextArea::make('specifications')->label('Specifications')->required(),
                                TextInput::make('manufacturer')->label('Manufacturer')->required(),
                                DatePicker::make('warranty_expiration')
                                    ->label('Warranty Expiration Date')
                                    ->displayFormat('m/d/Y')
                                    ->format('Y-m-d')
                                    ->seconds(false)
                                    ->nullable(),
                            ]),
                        Fieldset::make('Software Details')
                            ->hidden(fn (callable $get) => $get('show_software') !== true)
                            ->schema([
                                TextInput::make('version')->label('Version')->nullable(),
                                TextInput::make('license_key')->label('License Key')->nullable(),
                                Select::make('software_type')->label('Software Type')
                                    ->options(SoftwareType::all()->pluck('software_type', 'id')->toArray())
                                    ->required(),
                                Select::make('license_type')->label('License Type')
                                    ->options(LicenseType::all()->pluck('license_type', 'id')->toArray())
                                    ->required(),
                            ]),
                        Fieldset::make('Peripherals Details')
                            ->hidden(fn (callable $get) => $get('show_peripherals') !== true)
                            ->schema([
                                Select::make('peripherals_type')->label('Peripheral Type')
                                    ->options(PeripheralType::all()->pluck('peripherals_type', 'id')->toArray())
                                    ->required(),
                                TextInput::make('serial_number')->label('Serial No.')->required(),
                                TextArea::make('specifications')->label('Specifications')->required(),
                                TextInput::make('manufacturer')->label('Manufacturer')->required(),
                                DatePicker::make('warranty_expiration')
                                    ->label('Warranty Expiration Date')
                                    ->displayFormat('m/d/Y')
                                    ->format('Y-m-d')
                                    ->seconds(false),
                            ]),
                        Fieldset::make('Lifecycle Information')
                            ->schema([
                                DatePicker::make('acquisition_date')
                                    ->label('Acquisition Date')
                                    ->required()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $set('retirement_date', null);
                                    }),
                                DatePicker::make('retirement_date')
                                    ->label('Retirement Date')
                                    ->nullable()
                                    ->minDate(fn ($get) => $get('acquisition_date'))
                                ])->reactive(),
                        Radio::make('add_purchase_information')
                            ->label('Add Purchase Order Information?')
                            ->options([
                                'yes' => 'Yes',
                                'no' => 'No',
                            ])
                            ->default('no')
                            ->reactive(),
                            Fieldset::make('Purchase Information')
                                ->hidden(fn (callable $get) => $get('add_purchase_information') !== 'yes')
                                ->schema([
                                    TextInput::make('purchase_order_no')
                                        ->label('Purchase Order No.')
                                        ->required()
                                        ->numeric()
                                        ->columnSpan(1),
                                    TextInput::make('sales_invoice_no')
                                        ->label('Sales Invoice No.')
                                        ->required()
                                        ->numeric()
                                        ->columnSpan(1),
                                    DatePicker::make('purchase_order_date')
                                        ->label('Purchase Order Date')
                                        ->required(),
                                    TextInput::make('purchase_order_amount')
                                        ->label('Purchase Order Amount')
                                        ->required()
                                        ->numeric()
                                        ->columnSpan(1),
                                ])->label('Purchase Order Information'),
                            Fieldset::make('Vendor Information')
                            ->hidden(fn (callable $get) => $get('add_purchase_information') !== 'yes')
                            ->schema([
                                Radio::make('vendor_option')
                                    ->label('Vendor Option')
                                    ->options([
                                        'existing' => 'Choose from Existing Vendors',
                                        'new' => 'Create New Vendor',
                                    ])
                                    ->reactive()
                                    ->default('existing'),
                                Select::make('vendor_id')
                                    ->label('Existing Vendor')
                                    ->options(function () {
                                        return \App\Models\Vendor::pluck('name', 'id');
                                    })
                                    ->preload()
                                    ->searchable()
                                    ->hidden(fn (callable $get) => $get('vendor_option') !== 'existing')
                                    ->required(),
                                Fieldset::make('New Vendor Details')
                                    ->hidden(fn (callable $get) => $get('vendor_option') !== 'new')
                                    ->schema([
                                        TextInput::make('vendor.name')
                                            ->label('Vendor Name')
                                            ->required()
                                            ->maxLength(255),
                                        TextInput::make('vendor.address_1')
                                            ->label('Address 1')
                                            ->required()
                                            ->maxLength(255),
                                        TextInput::make('vendor.address_2')
                                            ->label('Address 2')
                                            ->maxLength(255),
                                        TextInput::make('vendor.city')
                                            ->label('City')
                                            ->nullable()
                                            ->maxLength(255),
                                        TextInput::make('vendor.tel_no_1')
                                            ->label('Telephone No. 1')
                                            ->tel()
                                            ->nullable()
                                            ->maxLength(255),
                                        TextInput::make('vendor.tel_no_2')
                                            ->label('Telephone No. 2')
                                            ->nullable()
                                            ->tel()
                                            ->maxLength(255),
                                        TextInput::make('vendor.contact_person')
                                            ->label('Contact Person')
                                            ->nullable()
                                            ->maxLength(255),
                                        TextInput::make('vendor.mobile_number')
                                            ->label('Mobile Number')
                                            ->nullable()
                                            ->numeric(),
                                        TextInput::make('vendor.email')
                                            ->label('Email')
                                            ->email()
                                            ->nullable()
                                            ->maxLength(255),
                                        TextInput::make('vendor.url')
                                            ->label('URL')
                                            ->maxLength(255),
                                        Textarea::make('vendor.remarks')
                                            ->label('Remarks'),
                                    ]),
                            ])
                            ->columnSpanFull(),
                        ])
                    ->createItemButtonLabel('Add Asset')
                    ->label('Asset Information')
                    ->columnSpanFull()
                    ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Asset ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->orWhere('assets.id', 'like', "%{$search}%");
                    }),
                TextColumn::make('asset_type')
                    ->label('Asset Type')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('asset_status')
                    ->label('Asset Status')
                    ->getStateUsing(function (Asset $record): string {
                        $assetStatus = AssetStatus::find($record->asset_status);
                        return $assetStatus ? $assetStatus->asset_status : 'N/A';
                    })
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Active' => 'success',
                        'Inactive' => 'gray',
                        'In Transfer' => 'primary',
                        'Maintenance' => 'warning',
                        'Lost' => 'gray',
                        'Disposed' => 'gray',
                        'Stolen' => 'danger',
                        'Unknown' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('brand')
                    ->label('Brand')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('model')
                    ->label('Model')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('details')
                    ->label('Details')
                    ->sortable()
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->orWhere('hardware.specifications', 'like', "%{$search}%")
                            ->orWhere('software.version', 'like', "%{$search}%")
                            ->orWhere('peripherals.specifications', 'like', "%{$search}%");
                    })
                    ->getStateUsing(fn($record) => $record->details),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->orWhere('assets.created_at', 'like', "%{$search}%");
                    }),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->orWhere('assets.updated_at', 'like', "%{$search}%");
                    }),
            ])
            ->filters([
                // Define any filters here
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('assets.id', 'desc')
            ->modifyQueryUsing(function (Builder $query) {
                $query->leftJoin('hardware', 'assets.id', '=', 'hardware.asset_id')
                    ->leftJoin('software', 'assets.id', '=', 'software.asset_id')
                    ->leftJoin('peripherals', 'assets.id', '=', 'peripherals.asset_id')
                    ->select('assets.*',
                        'hardware.specifications as hardware_specifications',
                        'software.version as software_version',
                        'peripherals.specifications as peripherals_specifications',
                    );
            });
    }

    public static function getRelations(): array
    {
        return [
            AssignmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssets::route('/'),
            'create' => Pages\CreateAsset::route('/create'),
            'view' => Pages\ViewAsset::route('/{record}'),
            'edit' => Pages\EditAsset::route('/{record}/edit'),
        ];
    }

}
