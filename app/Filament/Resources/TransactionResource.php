<?php

namespace App\Filament\Resources;

use BackedEnum;
use UnitEnum;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Carbon\Carbon;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use App\Filament\Resources\TransactionResource\Pages\ListTransactions;
use App\Filament\Resources\TransactionResource\Pages\CreateTransaction;
use App\Filament\Resources\TransactionResource\Pages\EditTransaction;
use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Course;
use Filament\Forms;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static string | \BackedEnum | null $navigationIcon = null;

    protected static string | \UnitEnum | null $navigationGroup = 'Customers';
    
    protected static ?int $navigationSort = 2;
    
    protected static ?string $navigationLabel = 'Transaksi';
    
    public static function getSlug(?\Filament\Panel $panel = null): string
    {
        return 'transactions';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
                Wizard::make([

                    Step::make('Product and Price')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    Select::make('course_id')
                                        ->relationship('course', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            if ($state) {
                                $course = Course::find($state);
                                $price = $course->price;
                                $adminFee = $course->admin_fee_amount ?? 0;
                                $discountAmount = $get('discount_amount') ?: 0;
                                $subTotal = $price;
                                $grandTotal = $subTotal + $adminFee - $discountAmount;
                                
                                $set('admin_fee_amount', $adminFee);
                                $set('grand_total_amount', $grandTotal);
                                $set('sub_total_amount', $price);
                            }
                        }),
                                ]),

                            Grid::make(2)
                            ->schema([
                                TextInput::make('sub_total_amount')
                                    ->required()
                                    ->numeric()
                                    ->prefix('IDR')
                                    ->readOnly(),

                                TextInput::make('admin_fee_amount')
                                    ->required()
                                    ->numeric()
                                    ->prefix('IDR')
                                    ->readOnly()
                                    ->helperText('Admin fee dari course'),
                            ]),

                            Grid::make(2)
                            ->schema([
                                TextInput::make('discount_amount')
                                    ->numeric()
                                    ->prefix('IDR')
                                    ->readOnly()
                                    ->helperText('Jumlah diskon yang diterapkan (otomatis dihitung)'),

                                Select::make('discount_id')
                                    ->relationship('discount', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->nullable()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        if ($state) {
                                            $discount = \App\Models\Discount::find($state);
                                            $subTotal = $get('sub_total_amount') ?? 0;
                                            
                                            if ($discount && $subTotal > 0) {
                                                $discountAmount = 0;
                                                
                                                if ($discount->type === 'percentage') {
                                                    $discountAmount = ($subTotal * $discount->value) / 100;
                                                } elseif ($discount->type === 'fixed') {
                                                    $discountAmount = $discount->value;
                                                }
                                                
                                                $adminFee = $get('admin_fee_amount') ?? 0;
                                                $grandTotal = $subTotal + $adminFee - $discountAmount;
                                                
                                                $set('discount_amount', $discountAmount);
                                                $set('grand_total_amount', $grandTotal);
                                            }
                                        } else {
                                            // Jika discount_id dihapus, reset discount_amount
                                            $subTotal = $get('sub_total_amount') ?? 0;
                                            $adminFee = $get('admin_fee_amount') ?? 0;
                                            $grandTotal = $subTotal + $adminFee;
                                            
                                            $set('discount_amount', 0);
                                            $set('grand_total_amount', $grandTotal);
                                        }
                                    })
                                    ->helperText('Diskon yang diterapkan (opsional)'),
                            ]),

                            Grid::make(1)
                            ->schema([
                                TextInput::make('grand_total_amount')
                                    ->required()
                                    ->numeric()
                                    ->prefix('IDR')
                                    ->readOnly()
                                    ->helperText('Total setelah admin fee dan diskon'),
                            ]),


                            Grid::make(2)
                            ->schema([
                                DatePicker::make('started_at')
                                ->required(),
                            ]),
                        ]),

                        Step::make('Customer Information')
                        ->schema([
                            Select::make('user_id')
                                ->relationship('student', 'email')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if ($state) {
                                        $user = User::find($state);
                                        if ($user) {
                                            $name = $user->name;
                                            $email = $user->email;

                                            $set('name', $name);
                                            $set('email', $email);
                                        }
                                    }
                                })
                                ->afterStateHydrated(function (callable $set, $state) {
                                    $userId = $state;
                                    if ($userId) {
                                        $user = User::find($userId);
                                        if ($user) {
                                            $name = $user->name;
                                            $email = $user->email;
                                            $set('name', $name);
                                            $set('email', $email);
                                        }
                                    }
                                }),
                            TextInput::make('name')
                                ->required()
                                ->readOnly()
                                ->maxLength(255),

                            TextInput::make('email')
                                ->required()
                                ->readOnly()
                                ->maxLength(255),
                        ]),


                    Step::make('Payment Information')
                        ->schema([

                            ToggleButtons::make('is_paid')
                                ->label('Apakah sudah membayar?')
                                ->boolean()
                                ->grouped()
                                ->icons([
                                    true => 'heroicon-o-pencil',
                                    false => 'heroicon-o-clock',
                                ])
                                ->required(),

                            Select::make('payment_type')
                                ->options([
                                    'Midtrans' => 'Midtrans',
                                    'Manual' => 'Manual',
                                ])
                                ->required(),

                            FileUpload::make('proof')
                                ->image(),
                        ]),

                ])
                ->columnSpan('full') // Use full width for the wizard
                ->columns(1) // Make sure the form has a single column layout
                ->skippable()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                ImageColumn::make('student.photo')
                ->circular()
                ->defaultImageUrl(fn ($record) => getUserAvatarWithColor($record->student, 100))
                ,

                TextColumn::make('student.name')
                    ->searchable()
                    ->extraAttributes(['class' => 'whitespace-normal break-words']),

                TextColumn::make('booking_trx_id')
                ->searchable(),

                // Allow long course names to wrap
                TextColumn::make('course.name')
                    ->label('Course')
                    ->wrap()
                    ->extraAttributes(['class' => 'whitespace-normal break-words'])
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('admin_fee_amount')
                    ->label('Admin Fee')
                    ->formatStateUsing(fn ($state) => $state > 0 ? 'Rp ' . number_format($state, 0, '', '.') : '-')
                    ->sortable(),

                TextColumn::make('discount_amount')
                    ->label('Diskon')
                    ->getStateUsing(function (\App\Models\Transaction $record) {
                        // Ambil nilai diskon yang tersimpan
                        $amount = is_numeric($record->discount_amount) ? (float) $record->discount_amount : 0.0;
                        // Jika tidak ada (0/null), hitung fallback dari komponennya: sub_total + admin_fee - grand_total
                        if ($amount <= 0) {
                            $sub = (float) ($record->sub_total_amount ?? 0);
                            $admin = (float) ($record->admin_fee_amount ?? 0);
                            $grand = (float) ($record->grand_total_amount ?? 0);
                            $diff = ($sub + $admin) - $grand;
                            $amount = $diff > 0 ? $diff : 0.0;
                        }
                        return $amount;
                    })
                    ->formatStateUsing(fn ($state) => ((float) $state > 0)
                        ? 'Rp ' . number_format((float) $state, 0, '', '.')
                        : '-')
                    ->sortable(),

                TextColumn::make('discount.name')
                    ->label('Nama Diskon')
                    ->getStateUsing(fn (\App\Models\Transaction $record) => $record->discount?->name ?? '-')
                    ->wrap()
                    ->extraAttributes(['class' => 'whitespace-normal break-words'])
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('grand_total_amount')
                    ->label('Total Amount')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, '', '.'))
                    ->sortable(),

                IconColumn::make('is_paid')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->label('Terverifikasi'),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                ViewAction::make(),

                Action::make('approve')
                    ->label('Approve')
                    ->action(function (Transaction $record) {
                        $record->is_paid = true;
                        $record->save();

                        // Trigger the custom notification
                        Notification::make()
                            ->title('Order Approved')
                            ->success()
                            ->body('The Order has been successfully approved.')
                            ->send();

                        // kirim email, kirim sms

                    })
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (?Transaction $record) => $record && !$record->is_paid),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
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
            'index' => ListTransactions::route('/'),
            'create' => CreateTransaction::route('/create'),
            'edit' => EditTransaction::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        // Standarisasi ke repository untuk query tabel Filament
        $repo = app(\App\Repositories\TransactionRepositoryInterface::class);
        return $repo->filamentTableQuery();
    }
}
