<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Support\HtmlString;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 1;

    protected static function getNavigationLabel(): string
    {
        return __('Users');
    }

    public static function getPluralLabel(): ?string
    {
        return static::getNavigationLabel();
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('Permissions');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label(__('Full name'))
                                    ->required()
                                    ->maxLength(255),


                                Forms\Components\TextInput::make('email')
                                    ->label(__('Email address'))
                                    ->email()
                                    ->required()
                                    ->rule(
                                        fn($record) => 'unique:users,email,'
                                        . ($record ? $record->id : 'NULL')
                                        . ',id,deleted_at,NULL'
                                    )
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('password')
                                    ->label(__('Password'))
                                    ->password()
                                    ->dehydrateStateUsing(fn($state) => filled($state) ? bcrypt($state) : null)
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('Leave blank if you don\'t want to change the password')
                                ,
                                Forms\Components\Toggle::make('is_active')
                                    ->label(__('User enabled'))
                                    ->default(true)
                                    ->helperText(__('Enable or disable this user account')),
                                Forms\Components\CheckboxList::make('roles')
                                    ->label(__('Permission roles'))
                                    ->columns(3)
                                    ->relationship('roles', 'name'),
                                Forms\Components\FileUpload::make('photo')
                                    ->label(__('Cover image'))
                                    ->image()
                                    ->disk('public')
                                    ->directory('users')
                                    ->helperText(
                                        __('If not selected, an image will be generated based on the project name')
                                    )
                                ,
                                // Group permissions by type and display in collapsible sections
                                // Only allow assigning permissions that the current user already has
                                Forms\Components\Hidden::make('current_user_permissions')
                                    ->default(auth()->user()?->getAllPermissions()->pluck('id')->toArray() ?? []),

                                Forms\Components\Grid::make()
                                    ->columns(3)
                                    ->schema(
                                        \Spatie\Permission\Models\Permission::query()
                                            ->select('type')
                                            ->distinct()
                                            ->pluck('type')
                                            ->map(function ($type, $index) {
                                                return Forms\Components\Section::make(__($type . ' Permissions'))
                                                    ->schema([
                                                        Forms\Components\CheckboxList::make('permissions')
                                                            ->label(__('Permissions'))
                                                            ->columns(2)
                                                            ->relationship('permissions', 'name')
                                                            ->options(
                                                                // Only show permissions the current user has
                                                                \Spatie\Permission\Models\Permission::where('type', $type)
                                                                    ->whereIn(
                                                                        'id',
                                                                        auth()->user()?->getAllPermissions()->pluck('id')->toArray() ?? []
                                                                    )
                                                                    ->pluck('name', 'id')
                                                            )
                                                            ->helperText(__('Assign permissions directly to this user')),
                                                    ])
                                                    ->collapsible()
                                                    ->collapsed($index !== 0)
                                                    ->columnSpan(1);
                                            })->values()->toArray()
                                    ),
                                Forms\Components\Grid::make()
                                    ->columns(3)
                                    ->schema(
                                        \Spatie\Permission\Models\Permission::query()
                                            ->select('type')
                                            ->distinct()
                                            ->pluck('type')
                                            ->map(function ($type, $index) {
                                                return Forms\Components\Section::make(__($type . ' Permissions'))
                                                    ->schema([
                                                        Forms\Components\CheckboxList::make('permissions')
                                                            ->label(__('Permissions'))
                                                            ->columns(2)
                                                            ->relationship('permissions', 'name')
                                                            ->options(
                                                                \Spatie\Permission\Models\Permission::where('type', $type)
                                                                    ->pluck('name', 'id')
                                                            )
                                                            ->helperText(__('Assign permissions directly to this user')),
                                                    ])
                                                    ->collapsible()
                                                    ->collapsed($index !== 0)
                                                    ->columnSpan(1);
                                            })->values()->toArray()
                                    ),
                            ]),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo')
                    ->label(__('Cover image'))
                    ->circular()
                    ->width(40)
                    ->height(40),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Full name'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TagsColumn::make('departments.name')
                    ->label(__('Department'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('email')
                    ->label(__('Email address'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TagsColumn::make('roles.name')
                    ->label(__('Roles'))
                    ->limit(2),


                // Tables\Columns\TextColumn::make('socials')
                //     ->label(__('Linked social networks'))
                //     ->view('partials.filament.resources.social-icon'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Created at'))
                    ->dateTime()
                    ->sortable()
                    ->hidden()
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('departments')
                    ->label(__('Department'))
                    ->relationship('departments', 'name')
                    ->searchable(),

            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->authorize('Delete user'),
            ])
            ->bulkActions([
                // Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
