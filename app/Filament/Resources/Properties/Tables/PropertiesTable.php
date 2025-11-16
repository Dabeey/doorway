<?php

namespace App\Filament\Resources\Properties\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PropertiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('type'),
                TextColumn::make('listing_type'),
                TextColumn::make('status'),

                TextColumn::make('price')
                    ->formatStateUsing(fn ($state) => '₦' . number_format($state, 2))
                    ->money(currency:'NGN')
                    ->sortable(),
                TextColumn::make('price_per_sqft')
                    ->formatStateUsing(fn ($state) => '₦' . number_format($state, 2))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('address')
                    ->searchable(),
                TextColumn::make('city')
                    ->searchable(),

                // Hidden by default
                TextColumn::make('state')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('country')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('postal_code')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('latitude')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('longitude')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('total_area')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('built_year')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('furnished')
                    ->boolean(),
                IconColumn::make('parking')
                    ->boolean(),
                TextColumn::make('parking_spaces')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('features')
                    ->formatStateUsing(function ($state) {
                        if (empty($state) || $state === 'null' || $state === '[]') {
                            return '-';
                        }
                        
                        $features = json_decode($state, true);

                          // If json_decode failed or returned null
                        if (!is_array($features) || json_last_error() !== JSON_ERROR_NONE) {
                            return '-';
                        }
                        
                        // If array is empty
                        if (empty($features)) {
                            return '-';
                        }
                        
                        return implode(', ', $features);
                    })
                    ->badge()
                    ->colors(['primary'])
                    ->limit(50), // Show first 3 features

                    ImageColumn::make('images')
                    ->label('Images')
                    ->circular()
                    ->imageSize(50)
                    ->stacked() // Stack images on top of each other
                    ->limit(3) // Show max 3 images
                    ->limitedRemainingText(true) // Shows "+X more"
                    ->ring(2) // Add white ring around stacked images
                    ->getStateUsing(function ($record) {
                        // Return array of all image URLs (up to 3)
                        $urls = $record->image_urls;
                        return !empty($urls) ? $urls : ['https://via.placeholder.com/40x40.png?text=No+Image'];
                    }),

                // Hidden by default
                TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('meta_title')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_featured')
                    ->boolean(),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('featured_until')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('contact_name')
                    ->searchable(),
                TextColumn::make('contact_phone')
                    ->searchable(),
                TextColumn::make('contact_email')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
