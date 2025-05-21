<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Closure;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class ProjectTickets extends BaseWidget
{
    protected function getTableQuery(): Builder
    {
        return Ticket::query()->latest();
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('id'),
        ];
    }
}
