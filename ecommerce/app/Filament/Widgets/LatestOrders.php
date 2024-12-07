<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\Order;  // Import model Order
use Filament\Tables\Columns\TextColumn;  // Pastikan kamu sudah mengimpor kolom yang dibutuhkan
use Filament\Tables\Actions\Action;  // Pastikan Action di-import dengan benar
use App\Filament\Resources\OrderResource; // Pastikan kamu sudah mengimpor kolom yang dibutuhkan

class LatestOrders extends BaseWidget
{
    protected int | string | array $columnpan = 'full';
    protected static ?int $sort = 2;
    public function table(Table $table): Table
    {
        return $table
            // Menggunakan query langsung dari model Order
            ->query(Order::query()) 
            // Menghapus atau memperbaiki pagination jika perlu
            ->defaultPaginationPageOption(10)
            ->defaultSort('created_at', 'desc')
            ->columns([  // Memperbaiki 'colummns' menjadi 'columns'
                TextColumn::make('id')
                    ->label('Order ID')
                    ->searchable(),

                TextColumn::make('user.name') 
                ->searchable(),   

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'new' => 'info',
                        'processing' => 'warning',
                        'shipped' => 'success',
                        'delivered' => 'success',  // Perbaiki penulisan 'succes' menjadi 'success'
                        'cancelled' => 'danger',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'new' => 'heroicon-m-sparkles',
                        'processing' => 'heroicon-m-arrow-path',
                        'shipped' => 'heroicon-m-truck',
                        'delivered' => 'heroicon-m-check-badge',
                        'cancelled' => 'heroicon-m-x-circle',
                    })
                    ->sortable(),

                TextColumn::make('payment_method')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('payment_status')
                    ->sortable()
                    ->badge()
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Order Date')
                    ->dateTime(),
            ])
            ->actions([
              Action::make('View Order')
              ->url(fn (Order $record): string =>  OrderResource::getUrl('view', ['record' => $record]))
              ->icon('heroicon-m-eye'),  
            ]);
    }
}
