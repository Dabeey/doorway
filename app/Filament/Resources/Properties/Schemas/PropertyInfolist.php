<?php

namespace App\Filament\Resources\Properties\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PropertyInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('title'),
                TextEntry::make('type'),
                TextEntry::make('listing_type'),
                TextEntry::make('status'),
                TextEntry::make('price')
                    ->money(currency: 'NGN', locale: 'en_NG'),
                TextEntry::make('price_per_sqft')
                    ->money(currency: 'NGN', locale: 'en_NG'),
                TextEntry::make('address'),
                TextEntry::make('city'),
                TextEntry::make('state'),
                TextEntry::make('country'),
                TextEntry::make('postal_code'),
                TextEntry::make('latitude')
                    ->numeric(),
                TextEntry::make('longitude')
                    ->numeric(),
                TextEntry::make('bedrooms')
                    ->numeric(),
                TextEntry::make('bathrooms')
                    ->numeric(),
                TextEntry::make('total_area')
                    ->numeric(),
                TextEntry::make('built_year')
                    ->numeric(),
                IconEntry::make('furnished')
                    ->boolean(),
                IconEntry::make('parking')
                    ->boolean(),
                TextEntry::make('parking_spaces')
                    ->numeric(),
                TextEntry::make('slug'),
                TextEntry::make('meta_title'),
                IconEntry::make('is_featured')
                    ->boolean(),
                IconEntry::make('is_active')
                    ->boolean(),
                TextEntry::make('featured_until')
                    ->dateTime(),
                TextEntry::make('contact_name'),
                TextEntry::make('contact_phone'),
                TextEntry::make('contact_email'),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
