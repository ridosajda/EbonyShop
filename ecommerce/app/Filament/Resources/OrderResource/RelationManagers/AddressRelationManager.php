<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use App\Filament\Resources\OrderResource\RelationManagers\Textinput;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AddressRelationManager extends RelationManager
{
    protected static string $relationship = 'address';

    public function form(Form $form): Form
    {
        return $form
            ->schema([

                Textinput::make('first_name')
                ->requied()
                ->maxLength(255),

                Textinput::make('last_name')
                ->required()
                ->maxLength(255),

                Textinput::make('phone')
                ->required()
                ->tel()
                ->maxLength(255),

                Textinput::make('city')
                ->required()
                ->maxLength(255),

                Textinput::make('state')
                ->required()
                ->maxLength(255),

                Textinput::make('zip_code')
                ->required()
                ->numeric()
                ->maxLength(10),

                Textinput::make('street_address')
                ->required()
                ->maxLength(255),

                Forms\Components\TextInput::make('street_address')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('street_address')
            ->columns([
                Tables\Columns\TextColumn::make('street_address'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
