<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use App\Models\Ticket;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListTickets extends ListRecords
{
    protected static string $resource = TicketResource::class;

    public $activeFilter = 'all';

    protected function shouldPersistTableFiltersInSession(): bool
    {
        return true;
    }

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('filterAll')
                ->label(__('All'))
                ->color($this->activeFilter === 'all' ? 'primary' : 'secondary')
                ->icon('heroicon-o-view-list')
                ->action(function () {
                    $this->activeFilter = 'all';
                }),

            Actions\Action::make('filterTodo')
                ->label(__('Todo'))
                ->color($this->activeFilter === 'todo' ? 'primary' : 'secondary')
                ->icon('heroicon-o-clock')
                ->action(function () {
                    $this->activeFilter = 'todo';
                }),

            Actions\Action::make('filterInProgress')
                ->label(__('In Progress'))
                ->color($this->activeFilter === 'in_progress' ? 'primary' : 'secondary')
                ->icon('heroicon-o-play')
                ->action(function () {
                    $this->activeFilter = 'in_progress';
                }),

            Actions\Action::make('filterDone')
                ->label(__('Done'))
                ->color($this->activeFilter === 'done' ? 'primary' : 'secondary')
                ->icon('heroicon-o-check-circle')
                ->action(function () {
                    $this->activeFilter = 'done';
                }),

            Actions\Action::make('filterArchive')
                ->label(__('Archive'))
                ->color($this->activeFilter === 'archived' ? 'primary' : 'secondary')
                ->icon('heroicon-o-archive')
                ->action(function () {
                    $this->activeFilter = 'archived';
                }),
        ];
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery()
            ->where(function ($query) {
                return $query->where('owner_id', auth()->user()->id) 
                    ->orWhere('responsible_id', auth()->user()->id)
                    ->orWhereHas('project', function ($query) {
                        return $query->where('owner_id', auth()->user()->id)
                            ->orWhereHas('users', function ($query) {
                                return $query->where('users.id', auth()->user()->id);
                            });
                    });
            });

        // Apply status filter if active
        if ($this->activeFilter && $this->activeFilter !== 'all') {
            switch ($this->activeFilter) {
                case 'todo':
                    $query->where('status_id', 1);
                    break;
                case 'in_progress':
                    $query->where('status_id', 2);
                    break;
                case 'done':
                    $query->where('status_id', 3);
                    break;
                case 'archived':
                    $query->where('status_id', 4);
                    break;
            }
        }

        return $query;
    }
}