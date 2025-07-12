<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepartmentResource\Pages;
use App\Filament\Resources\DepartmentResource\RelationManagers;
use App\Models\Department;
use Filament\Forms;
use Filament\Forms\Components\RichEditor;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use AskerAkbar\GptTrixEditor\Components\GptTrixEditor;
use App\Models\User;
use Awcodes\FilamentBadgeableColumn\Components\Badge;
use Awcodes\FilamentBadgeableColumn\Components\BadgeableColumn;
class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->hasRole('Project Manager');
    }

    public static function canUpdate(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->hasRole('Project Manager');
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->hasRole('Project Manager');
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasRole('Project Manager');
    }

    public static function canView(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->hasRole('Project Manager');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('Project Manager');
    }

    protected static ?string $navigationIcon = 'heroicon-o-user';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('users')
                    ->label('Users')
                    ->relationship('users', 'name') // references the relationship in your model
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                RichEditor::make('description'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable()
                ,
                Tables\Columns\ImageColumn::make('users.0.photo')
                    ->label('Photos')
                    ->rounded(),
                Tables\Columns\TextColumn::make('users.name')
                    ->label('Users')
                    ->searchable()
                    ->label(__('User')),
                // Tables\Columns\TextColumn::make('created_at')
                //     ->dateTime()
                //     ,
                // Tables\Columns\TextColumn::make('updated_at')
                //     ->dateTime()
                //     ,
            ])
            ->filters([

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListDepartments::route('/'),
            'create' => Pages\CreateDepartment::route('/create'),
            'edit' => Pages\EditDepartment::route('/{record}/edit'),
        ];
    }
}
