<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Filament\Resources\TicketResource;
use App\Models\Project;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\TicketType;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Resources\Form;
use Filament\Resources\Table;

class TicketsRelationManager extends RelationManager
{
    protected static string $relationship = 'tickets';

    protected static string $resource = TicketResource::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('project_id')
                    ->label(__('Project'))
                    ->options(fn() => Project::all()->pluck('name', 'id')->toArray())
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->label(__('Ticket name'))
                    ->required(),
                Forms\Components\Select::make('owner_id')
                    ->label(__('Ticket owner'))
                    ->options(fn() => User::all()->pluck('name', 'id')->toArray())
                    ->required(),
                Forms\Components\Select::make('status_id')
                    ->label(__('Ticket status'))
                    ->options(fn() => TicketStatus::all()->pluck('name', 'id')->toArray())
                    ->required(),
                Forms\Components\Select::make('type_id')
                    ->label(__('Ticket type'))
                    ->options(fn() => TicketType::all()->pluck('name', 'id')->toArray())
                    ->required(),
                Forms\Components\Select::make('priority_id')
                    ->label(__('Ticket priority'))
                    ->options(fn() => TicketPriority::all()->pluck('name', 'id')->toArray())
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(TicketResource::tableColumns(false))
            ->filters([
                Tables\Filters\SelectFilter::make('status_id')
                    ->label(__('Status'))
                    ->multiple()
                    ->options(fn() => TicketStatus::all()->pluck('name', 'id')->toArray()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation
    {
        return parent::getTableQuery()->where('project_id', $this->ownerRecord->id);
    }
}
