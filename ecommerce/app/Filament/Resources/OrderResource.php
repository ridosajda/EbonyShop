<?php

namespace App\Filament\Resources;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Hidden;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Filament\Resources\OrderResource\RelationManagers\AddressRelationManager;
use App\Models\Order;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\SelectColumn; // Add this line
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Set;
use Filament\Forms\Get;







class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                
                Group::make()->schema([
                    Section::make('Order Information')->schema([
                    Select::make('user_id')
                    ->label('Customer')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(), 

            
                Select::make('payment_method')
                    ->options([
                    'stripe' => 'Stripe',
                    'cod' => 'Cash On Delivery'
                    ])
                    ->required(),

                Select::make('payment_status')
                    ->options([
                    'pending' => 'Pending',
                    'paid' => 'Paid',
                    'failed' => 'Failed'
                    ])
                    ->default('pending')
                    ->required(),

                ToggleButtons::make('status')
                    ->inline()
                    ->default('new')
                    ->required()
                    ->options([
                    'new' => 'New',
                    'processing' => 'Processing',
                    'shipped' => 'Shipped',
                    'delivered' => 'Delivered',
                    'cancelled' => 'Cancelled'
                    ])
                    ->colors([
                    'new' => 'info',
                    'processing' => 'warning',
                    'shipped' => 'success',
                    'delivered' => 'success',
                    'cancelled' => 'danger'
                    ])
                    ->icons([
                        'new' => 'heroicon-m-sparkles',
                        'processing' => 'heroicon-m-arrow-path',
                        'shipped' => 'heroicon-m-truck',
                        'delivered' => 'heroicon-m-check-badge',
                        'cancelled' => 'heroicon-m-x-circle'
                        ]),

                    Select::make('currency')
                        ->options([
                        'idr' => 'IDR',
                        'usd' => 'USD',
                        'eur' => 'EUR',
                        'gbp' => 'GBP'
                        ])
                        ->default('idr')
                        ->required(),
                    
                    Select::make('shipping_method')
                        ->options([
                        'fedex' => 'FedEx',
                        'ups' => 'UPS',
                        'dhl' => 'DHL',
                        'usps' => 'USPS'
                        ]),

                    Textarea::make('notes')
                        ->columnSpanFull()
                ])->columns(2),

                Section::make('Order Items')->schema ([
                    Repeater::make('items')
    ->relationship()
    ->schema([
        Select::make('product_id')
            ->relationship('product', 'name')
            ->searchable()
            ->preload()
            ->required()
            ->reactive()
            ->columnSpan(4)
            ->afterStateUpdated(function ($state, Set $set) {
                $selectedProduct = Product::find($state);
                if ($selectedProduct) {
                    $set('unit_amount', $selectedProduct->price);
                    $set('total_amount', $selectedProduct->price);
                } else {
                    $set('unit_amount', 0);
                    $set('total_amount', 0);
                }
            }),
        TextInput::make('quantity')
            ->numeric()
            ->required()
            ->default(1)
            ->minValue(1)
            ->columnSpan(2)
            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                $set('total_amount', $state * $get('unit_amount'));
            }),
        TextInput::make('unit_amount')
            ->numeric()
            ->required()
            ->disabled()
            ->columnSpan(3),
        TextInput::make('total_amount')
            ->numeric()
            ->required()
            ->disabled()
            ->columnSpan(3),
    ])
    ->columns(12),

                

    Placeholder::make('grand_total_placeholder')
->label('Grand Total')
->content(function (Get $get) {
    $total = 0;
    $items = $get('items') ?? [];

    logger()->info('Items in grand total:', ['items' => $items]);

    foreach ($items as $item) {
        $total += $item['total_amount'] ?? 0;
    }
    
    return 'IDR ' . number_format($total, 0, ',', '.');
}),
                
                    Hidden::make('grand_total')
                    ->default(0) 
                ])
           ])->columnSpanFull()
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                ->label('Customer')
                ->sortable()
                ->searchable(),
                TextColumn::make('grand_total')
                ->numeric()
                ->sortable()
                ->money('IDR'),
                TextColumn::make('payment_method')
                ->searchable()
                ->sortable(),
                TextColumn:: make('payment_status')
                ->searchable()
                ->sortable(),
                TextColumn:: make('shipping_method')
                ->sortable()
                ->searchable(),
                SelectColumn::make('status')
                ->options([
                'new' => 'New',
                'processing' => 'Processing',
                'shipped' => 'Shipped',
                'delivered' => 'Delivered',
                'cancelled' => 'Cancelled'
                ])
                ->searchable()
                ->sortable(), 
                TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true)
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(), 
                    EditAction::make(), 
                    DeleteAction::make()
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getNavigationBadge(): ?string {
        return static::getModel()::count();
        
        }
    public static function getNavigationBadgeColor(): string|array|null { 
            return static::getModel()::count() > 10 ? 'success': 'danger';
        }
    public static function getRelations(): array
    {
        return [
            AddressRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
