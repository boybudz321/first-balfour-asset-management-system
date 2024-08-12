<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VendorResource\Pages;
use App\Filament\Resources\VendorResource\RelationManagers;
use App\Models\Vendor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VendorResource extends Resource
{
    protected static ?string $model = Vendor::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'Manage Transactions';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('address_1')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('address_2')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('city')
                    ->maxLength(255),
                Forms\Components\TextInput::make('tel_no_1')
                    ->tel()
                    ->maxLength(255),
                Forms\Components\TextInput::make('tel_no_2')
                    ->tel()
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('contact_person')
                    ->maxLength(255),
                Forms\Components\TextInput::make('mobile_number')
                    ->numeric(),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->maxLength(255),
                Forms\Components\TextInput::make('url')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\Textarea::make('remarks')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('contact_person')
                    ->searchable()
                    ->placeholder('N/A'),
                Tables\Columns\TextColumn::make('address_1')
                    ->searchable()
                    ->placeholder('N/A'),
                Tables\Columns\TextColumn::make('address_2')
                    ->searchable()
                    ->placeholder('N/A'),
                Tables\Columns\TextColumn::make('city')
                    ->searchable()
                    ->placeholder('N/A'),
                Tables\Columns\TextColumn::make('tel_no_1')
                    ->searchable()
                    ->placeholder('N/A'),
                Tables\Columns\TextColumn::make('tel_no_2')
                    ->searchable()
                    ->placeholder('N/A'),
                Tables\Columns\TextColumn::make('mobile_number')
                    ->numeric()
                    ->sortable()
                    ->placeholder('N/A'),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->placeholder('N/A'),
                Tables\Columns\TextColumn::make('url')
                    ->searchable()
                    ->placeholder('N/A'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('N/A'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('N/A'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVendors::route('/'),
            'create' => Pages\CreateVendor::route('/create'),
            'edit' => Pages\EditVendor::route('/{record}/edit'),
        ];
    }
}
