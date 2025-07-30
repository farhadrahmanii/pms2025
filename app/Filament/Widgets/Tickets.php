<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use App\Models\Project;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Closure;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Collection;

class Tickets extends BaseWidget implements HasForms
{
    protected static ?int $sort = 1;
    protected int|string|array $columnSpan = [
        'sm' => 1,
        'md' => 6,
        'lg' => 12
    ];

    public $activeFilter = null;

    protected $listeners = ['refreshTable' => '$refresh'];

    public function mount(): void
    {
        self::$heading = __('My Tasks');
        $this->activeFilter = 'all';
    }

    public static function canView(): bool
    {
        // return auth()->user()->can('List tickets');
        return true;
    }

    protected function getTableQuery(): Builder
    {
        $query = Ticket::query()
            ->where('responsible_id', auth()->user()->id)
            ->latest();

        // Apply status filter if active
        if ($this->activeFilter) {
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
                case 'all':
                    // No additional filter needed
                    break;
            }
        }

        return $query;
    }

    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }

    protected function isTableFiltersEnabled(): bool
    {
        return true;
    }

    

    protected function getTableRecordUrlUsing(): ?Closure
    {
        return function (Ticket $record): string {
            return route('filament.resources.tickets.view', $record);
        };
    }

    protected function getTableFilters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('project_id')
                ->label(__('Project'))
                ->multiple()
                ->options(function () {
                    // Get all projects the user has access to
                    $projects = Project::query()
                        ->where(function ($query) {
                            $query->where('owner_id', auth()->user()->id)
                                ->orWhereHas('users', function ($subQuery) {
                                    $subQuery->where('users.id', auth()->user()->id);
                                });
                        })
                        ->orderBy('name')
                        ->get();
                    
                    // If no projects found, return empty array
                    if ($projects->isEmpty()) {
                        return [];
                    }
                    
                    return $projects->pluck('name', 'id')->toArray();
                })
                ->placeholder(__('All Projects'))
                ->searchable(),

            Tables\Filters\SelectFilter::make('status_id')
                ->label(__('Status'))
                ->multiple()
                ->options(function () {
                    return \App\Models\TicketStatus::all()->pluck('name', 'id')->toArray();
                })
                ->placeholder(__('All Statuses')),

            Tables\Filters\SelectFilter::make('priority_id')
                ->label(__('Priority'))
                ->multiple()
                ->options(function () {
                    return \App\Models\TicketPriority::all()->pluck('name', 'id')->toArray();
                })
                ->placeholder(__('All Priorities')),

            Tables\Filters\SelectFilter::make('type_id')
                ->label(__('Type'))
                ->multiple()
                ->options(function () {
                    return \App\Models\TicketType::all()->pluck('name', 'id')->toArray();
                })
                ->placeholder(__('All Types')),

            Tables\Filters\SelectFilter::make('owner_id')
                ->label(__('Assigned by'))
                ->multiple()
                ->options(function () {
                    return \App\Models\User::all()->pluck('name', 'id')->toArray();
                })
                ->placeholder(__('All Assigners')),

            Tables\Filters\Filter::make('has_deadline')
                ->label(__('Has Deadline'))
                ->query(function (Builder $query) {
                    return $query->whereNotNull('end_date');
                }),

            Tables\Filters\Filter::make('no_deadline')
                ->label(__('No Deadline'))
                ->query(function (Builder $query) {
                    return $query->whereNull('end_date');
                }),

            Tables\Filters\Filter::make('expired')
                ->label(__('Expired'))
                ->query(function (Builder $query) {
                    return $query->where('end_date', '<', now()->toDateString());
                }),

            Tables\Filters\Filter::make('due_today')
                ->label(__('Due Today'))
                ->query(function (Builder $query) {
                    return $query->whereDate('end_date', now()->toDateString());
                }),

            Tables\Filters\Filter::make('due_this_week')
                ->label(__('Due This Week'))
                ->query(function (Builder $query) {
                    return $query->whereBetween('end_date', [
                        now()->startOfWeek()->toDateString(),
                        now()->endOfWeek()->toDateString()
                    ]);
                }),

            Tables\Filters\Filter::make('overdue')
                ->label(__('Overdue'))
                ->query(function (Builder $query) {
                    return $query->where('end_date', '<', now()->toDateString());
                }),

            Tables\Filters\Filter::make('recently_created')
                ->label(__('Recently Created (Last 7 days)'))
                ->query(function (Builder $query) {
                    return $query->where('created_at', '>=', now()->subDays(7));
                }),

            Tables\Filters\Filter::make('recently_updated')
                ->label(__('Recently Updated (Last 7 days)'))
                ->query(function (Builder $query) {
                    return $query->where('updated_at', '>=', now()->subDays(7));
                }),
        ];
    }

    protected function getTableFiltersFormColumns(): int
    {
        return 4;
    }

    

    protected function getTableBulkActions(): array
    {
        return [
            Tables\Actions\BulkAction::make('setTodo')
                ->label(__('Set Todo'))
                ->color('primary')
                ->icon('heroicon-o-clock')
                ->action(function (Collection $records) {
                    try {
                        $records->each(function ($record) {
                            // Get the current record state before updating
                            $currentRecord = Ticket::find($record->id);
                            $oldStatusId = $currentRecord->status_id;
                            
                            // Update the status
                            $record->status_id = 1; // Todo status ID
                            $record->save();
                            
                            // Create activity record manually if needed
                            if ($oldStatusId != 1) {
                                \App\Models\TicketActivity::create([
                                    'ticket_id' => $record->id,
                                    'old_status_id' => $oldStatusId,
                                    'new_status_id' => 1,
                                    'user_id' => auth()->user()->id
                                ]);
                            }
                        });
                        $this->notify('success', __('Tickets set to Todo'));
                        $this->emit('refreshTable');
                    } catch (\Exception $e) {
                        $this->notify('error', __('Error updating tickets: ' . $e->getMessage()));
                    }
                }),

            Tables\Actions\BulkAction::make('setInProgress')
                ->label(__('Set In Progress'))
                ->color('warning')
                ->icon('heroicon-o-play')
                ->action(function (Collection $records) {
                    try {
                        $records->each(function ($record) {
                            // Get the current record state before updating
                            $currentRecord = Ticket::find($record->id);
                            $oldStatusId = $currentRecord->status_id;
                            
                            // Update the status
                            $record->status_id = 2; // In Progress status ID
                            $record->save();
                            
                            // Create activity record manually if needed
                            if ($oldStatusId != 2) {
                                \App\Models\TicketActivity::create([
                                    'ticket_id' => $record->id,
                                    'old_status_id' => $oldStatusId,
                                    'new_status_id' => 2,
                                    'user_id' => auth()->user()->id
                                ]);
                            }
                        });
                        $this->notify('success', __('Tickets set to In Progress'));
                        $this->emit('refreshTable');
                    } catch (\Exception $e) {
                        $this->notify('error', __('Error updating tickets: ' . $e->getMessage()));
                    }
                }),

            Tables\Actions\BulkAction::make('setDone')
                ->label(__('Set Done'))
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->action(function (Collection $records) {
                    try {
                        $records->each(function ($record) {
                            // Get the current record state before updating
                            $currentRecord = Ticket::find($record->id);
                            $oldStatusId = $currentRecord->status_id;
                            
                            // Update the status
                            $record->status_id = 3; // Done status ID
                            $record->save();
                            
                            // Create activity record manually if needed
                            if ($oldStatusId != 3) {
                                \App\Models\TicketActivity::create([
                                    'ticket_id' => $record->id,
                                    'old_status_id' => $oldStatusId,
                                    'new_status_id' => 3,
                                    'user_id' => auth()->user()->id
                                ]);
                            }
                        });
                        $this->notify('success', __('Tickets set to Done'));
                        $this->emit('refreshTable');
                    } catch (\Exception $e) {
                        $this->notify('error', __('Error updating tickets: ' . $e->getMessage()));
                    }
                }),

            Tables\Actions\BulkAction::make('setArchive')
                ->label(__('Set Archive'))
                ->color('secondary')
                ->icon('heroicon-o-archive')
                ->action(function (Collection $records) {
                    try {
                        $records->each(function ($record) {
                            // Get the current record state before updating
                            $currentRecord = Ticket::find($record->id);
                            $oldStatusId = $currentRecord->status_id;
                            
                            // Update the status
                            $record->status_id = 4; // Archive status ID
                            $record->save();
                            
                            // Create activity record manually if needed
                            if ($oldStatusId != 4) {
                                \App\Models\TicketActivity::create([
                                    'ticket_id' => $record->id,
                                    'old_status_id' => $oldStatusId,
                                    'new_status_id' => 4,
                                    'user_id' => auth()->user()->id
                                ]);
                            }
                        });
                        $this->notify('success', __('Tickets set to Archive'));
                        $this->emit('refreshTable');
                    } catch (\Exception $e) {
                        $this->notify('error', __('Error updating tickets: ' . $e->getMessage()));
                    }
                }),
        ];
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('code')
                ->label(__('Project & Code'))
                ->formatStateUsing(fn($record) => new HtmlString('
                    <div class="flex flex-col gap-1">
                        <span class="text-gray-400 font-medium text-xs">
                            ' . $record->project->name . '
                        </span>
                        <span>
                            <a href="' . route('filament.resources.tickets.share', $record->code)
                    . '" target="_blank" class="text-primary-500 text-sm hover:underline">'
                    . $record->code
                    . '</a>
                        </span>
                        ' . ($record->responsible ? '
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-1 text-xs text-gray-400">'
                    . view('components.user-avatar', ['user' => $record->responsible])
                    . '<span>' . $record->responsible?->name . '</span>'
                    . '</div>
                        </div>' : '') . '
                    </div>
                ')),

            Tables\Columns\TextColumn::make('name')
                ->label(__('Ticket Name'))
                ->formatStateUsing(fn($record) => new HtmlString('
                    <div class="font-medium text-sm text-gray-900 dark:text-white">
                        ' . $record->name . '
                    </div>
                '))
                ->sortable()
                ->searchable(),

            Tables\Columns\TextColumn::make('owner.name')
                ->label(__('Assigned by'))
                ->formatStateUsing(fn($record) => new HtmlString('
                    <div class="flex items-center gap-2">
                        ' . view('components.user-avatar', ['user' => $record->owner]) . '
                        <span class="text-sm">' . $record->owner->name . '</span>
                    </div>
                '))
                ->sortable(),

            Tables\Columns\TextColumn::make('end_date')
                ->label(__('Deadline'))
                ->formatStateUsing(function ($record) {
                    if (!$record->end_date) {
                        return new HtmlString('<span class="text-gray-400 text-sm">' . __('No deadline') . '</span>');
                    }
                    
                    $endDate = \Carbon\Carbon::parse($record->end_date);
                    $now = now();
                    
                    $isExpired = $endDate->isPast();
                    $isToday = $endDate->isToday();
                    
                    $colorClass = $isExpired ? 'text-red-500' : ($isToday ? 'text-orange-500' : 'text-gray-700');
                    $icon = $isExpired ? '⚠️' : ($isToday ? '⏰' : '📅');
                    
                    return new HtmlString('
                        <div class="flex items-center gap-1">
                            <span class="text-xs">' . $icon . '</span>
                            <span class="text-sm ' . $colorClass . '">' . $endDate->format('M d, Y') . '</span>
                        </div>
                    ');
                })
                ->sortable(),
        ];
    }
}