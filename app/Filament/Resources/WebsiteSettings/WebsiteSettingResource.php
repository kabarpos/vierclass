<?php

namespace App\Filament\Resources\WebsiteSettings;

use App\Filament\Resources\WebsiteSettings\Pages\ManageWebsiteSettings;
use App\Filament\Resources\WebsiteSettings\Pages;
use App\Models\WebsiteSetting;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class WebsiteSettingResource extends Resource
{
    protected static ?string $model = WebsiteSetting::class;

    protected static string | \BackedEnum | null $navigationIcon = null;

    protected static ?string $navigationLabel = 'Pengaturan Website';

    protected static ?string $modelLabel = 'Pengaturan Website';

    protected static ?string $pluralModelLabel = 'Pengaturan Website';

    protected static string | \UnitEnum | null $navigationGroup = 'System';

    protected static ?int $navigationSort = 99;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Website Settings')
                    ->tabs([
                        // Tab SEO Settings
                        Tab::make('SEO & Meta')
                            ->icon('heroicon-o-magnifying-glass')
                            ->schema([
                                Section::make('Informasi Dasar Website')
                                    ->schema([
                                        TextInput::make('site_name')
                                            ->label('Nama Website')
                                            ->required()
                                            ->maxLength(255)
                                            ->default('Upversity.id')
                                            ->helperText('Nama website yang akan ditampilkan di title dan header'),
                                        
                                        TextInput::make('site_tagline')
                                            ->label('Tagline Website')
                                            ->maxLength(255)
                                            ->helperText('Tagline singkat yang mendeskripsikan website'),
                                        
                                        Textarea::make('site_description')
                                            ->label('Deskripsi Website')
                                            ->rows(3)
                                            ->helperText('Deskripsi website untuk meta description dan SEO'),
                                    ])->columns(1),

                                Section::make('Meta Tags SEO')
                                    ->schema([
                                        Textarea::make('meta_keywords')
                                            ->label('Meta Keywords')
                                            ->rows(2)
                                            ->helperText('Kata kunci untuk SEO, pisahkan dengan koma'),
                                        
                                        TextInput::make('meta_author')
                                            ->label('Meta Author')
                                            ->maxLength(255)
                                            ->helperText('Nama penulis atau organisasi'),
                                    ])->columns(1),
                            ]),

                        // Tab Media Settings
                        Tab::make('Media & Branding')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Section::make('Logo & Favicon')
                                    ->schema([
                                        FileUpload::make('logo')
                                            ->label('Logo Website')
                                            ->image()
                                            ->directory('website-settings/logos')
                                            ->disk('public')
                                            ->visibility('public')
                                            ->helperText('Upload logo website (format: PNG, JPG, SVG)'),
                                        
                                        FileUpload::make('favicon')
                                            ->label('Favicon')
                                            ->image()
                                            ->directory('website-settings/favicons')
                                            ->disk('public')
                                            ->visibility('public')
                                            ->helperText('Upload favicon (16x16 atau 32x32 pixel, format: ICO, PNG)'),
                                    ])->columns(2),

                                Section::make('Gambar Default')
                                    ->schema([
                                        FileUpload::make('default_thumbnail')
                                            ->label('Thumbnail Default')
                                            ->image()
                                            ->directory('website-settings/thumbnails')
                                            ->disk('public')
                                            ->visibility('public')
                                            ->helperText('Gambar default untuk konten yang tidak memiliki thumbnail'),
                                    ])->columns(1),
                            ]),

                        // Tab Scripts Settings
                        Tab::make('Scripts & Tracking')
                            ->icon('heroicon-o-code-bracket')
                            ->schema([
                                Section::make('Custom Scripts')
                                    ->schema([
                                        Textarea::make('head_scripts')
                                            ->label('Head Scripts')
                                            ->rows(5)
                                            ->helperText('Script yang akan ditempatkan di dalam tag <head> (Google Analytics, Facebook Pixel, dll)'),
                                        
                                        Textarea::make('body_scripts')
                                            ->label('Body Scripts')
                                            ->rows(5)
                                            ->helperText('Script yang akan ditempatkan sebelum penutup tag </body>'),
                                    ])->columns(1),

                                Section::make('Analytics & Tracking IDs')
                                    ->schema([
                                        TextInput::make('google_analytics_id')
                                            ->label('Google Analytics ID')
                                            ->placeholder('G-XXXXXXXXXX atau UA-XXXXXXXXX')
                                            ->helperText('ID Google Analytics untuk tracking website'),
                                        
                                        TextInput::make('facebook_pixel_id')
                                            ->label('Facebook Pixel ID')
                                            ->placeholder('123456789012345')
                                            ->helperText('ID Facebook Pixel untuk tracking konversi'),
                                    ])->columns(2),
                            ]),

                        // Tab Footer & Contact
                        Tab::make('Footer & Kontak')
                            ->icon('heroicon-o-phone')
                            ->schema([
                                Section::make('Footer Website')
                                    ->schema([
                                        Textarea::make('footer_text')
                                            ->label('Teks Footer')
                                            ->rows(3)
                                            ->helperText('Teks yang akan ditampilkan di footer website'),
                                        
                                        TextInput::make('footer_copyright')
                                            ->label('Copyright Text')
                                            ->placeholder('© ' . date('Y') . ' Upversity.id. All rights reserved.')
                                            ->helperText('Teks copyright di footer'),
                                    ])->columns(1),

                                Section::make('Informasi Kontak')
                                    ->schema([
                                        TextInput::make('contact_email')
                                            ->label('Email Kontak')
                                            ->email()
                                            ->helperText('Email utama untuk kontak'),
                                        
                                        TextInput::make('contact_phone')
                                            ->label('Nomor Telepon')
                                            ->tel()
                                            ->helperText('Nomor telepon untuk kontak'),

                                        Textarea::make('contact_address')
                                            ->label('Alamat Kantor')
                                            ->rows(3)
                                            ->maxLength(500)
                                            ->helperText('Alamat kantor atau korespondensi resmi'),
                                    ])->columns(2),
                            ]),

                        // Tab Advanced Settings
                        Tab::make('Pengaturan Lanjutan')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Section::make('Mode Maintenance')
                                    ->schema([
                                        Toggle::make('maintenance_mode')
                                            ->label('Mode Maintenance')
                                            ->helperText('Aktifkan untuk menampilkan halaman maintenance'),
                                        
                                        Textarea::make('maintenance_message')
                                            ->label('Pesan Maintenance')
                                            ->rows(3)
                                            ->helperText('Pesan yang ditampilkan saat mode maintenance aktif'),
                                    ])->columns(1),

                                Section::make('Pembayaran')
                                    ->schema([
                                        Select::make('default_payment_gateway')
                                            ->label('Gateway Pembayaran Default')
                                            ->options([
                                                'midtrans' => 'Midtrans',
                                                'tripay' => 'Tripay',
                                            ])
                                            ->required()
                                            ->default('midtrans')
                                            ->helperText('Pilih gateway pembayaran default untuk proses checkout.'),
                                    ])->columns(1),

                                Section::make('Social Media & Custom CSS')
                                    ->schema([
                                        Textarea::make('social_media_links')
                                            ->label('Link Social Media (JSON)')
                                            ->rows(4)
                                            ->placeholder('{"facebook": "https://facebook.com/...", "instagram": "https://instagram.com/...", "twitter": "https://twitter.com/..."}')
                                            ->helperText('Link social media dalam format JSON'),
                                        
                                        Textarea::make('custom_css')
                                            ->label('Custom CSS')
                                            ->rows(5)
                                            ->helperText('CSS kustom yang akan ditambahkan ke website'),
                                    ])->columns(1),
                            ]),
                    ])
                    ->columnSpanFull()
                    // removed ->persistTabInQueryString() to keep URL clean
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageWebsiteSettings::route('/'),
            'edit' => Pages\EditWebsiteSetting::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        // Standarisasi ke repository untuk query Filament
        $repo = app(\App\Repositories\WebsiteSettingRepositoryInterface::class);
        return $repo->filamentTableQuery();
    }
}

