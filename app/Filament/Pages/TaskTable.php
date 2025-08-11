<?php

namespace App\Filament\Pages;

use App\Models\Ticket;
use App\Models\Project;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Pages\Actions\Action;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Closure;
use Illuminate\Support\Collection;

class TaskTable extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationLabel = 'وظایف من';
    protected static ?string $title = 'وظایف من';
    protected static ?string $slug = 'my-tasks';

    protected static string $view = 'filament.pages.task-table';

    public $activeFilter = null;

    protected $listeners = ['refreshTable' => '$refresh'];

    public function mount(): void
    {
        $this->activeFilter = 'todo';
    }

    protected function getActions(): array
    {
        return [
            Action::make('filterAll')
                ->label('همه')
                ->color($this->activeFilter === 'all' ? 'primary' : 'secondary')
                ->icon('heroicon-o-view-list')
                ->action(function () {
                    $this->activeFilter = 'all';
                }),

            Action::make('filterTodo')
                ->label('انجام نشده')
                ->color($this->activeFilter === 'todo' ? 'primary' : 'secondary')
                ->icon('heroicon-o-clock')
                ->action(function () {
                    $this->activeFilter = 'todo';
                }),

            Action::make('filterInProgress')
                ->label('در حال انجام')
                ->color($this->activeFilter === 'in_progress' ? 'primary' : 'secondary')
                ->icon('heroicon-o-play')
                ->action(function () {
                    $this->activeFilter = 'in_progress';
                }),

            Action::make('filterDone')
                ->label('انجام شده')
                ->color($this->activeFilter === 'done' ? 'primary' : 'secondary')
                ->icon('heroicon-o-check-circle')
                ->action(function () {
                    $this->activeFilter = 'done';
                }),

            Action::make('filterArchive')
                ->label('آرشیو')
                ->color($this->activeFilter === 'archived' ? 'primary' : 'secondary')
                ->icon('heroicon-o-archive')
                ->action(function () {
                    $this->activeFilter = 'archived';
                }),

            Action::make('filterRejected')
                ->label('رد شده')
                ->color($this->activeFilter === 'rejected' ? 'primary' : 'secondary')
                ->icon('heroicon-o-x-circle')
                ->action(function () {
                    $this->activeFilter = 'rejected';
                }),
        ];
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
                    $query->where('status_id', 1)
                          ->where('approved', '!=', -1); // Exclude rejected tasks
                    break;
                case 'in_progress':
                    $query->where('status_id', 2)
                          ->where('approved', '!=', -1); // Exclude rejected tasks
                    break;
                case 'done':
                    $query->where('status_id', 3)
                          ->where('approved', '!=', -1); // Exclude rejected tasks
                    break;
                case 'archived':
                    $query->where('status_id', 4)
                          ->where('approved', '!=', -1); // Exclude rejected tasks
                    break;
                case 'rejected':
                     $query->where('approved', -1); // Show only rejected tasks
                    break;
                case 'all':
                    $query->where('approved', '!=', -1); // Exclude rejected tasks
                    break;
            }
        } else {
            // Default filter (todo) - exclude rejected tasks
            $query->where('approved', '!=', -1);
        }

        return $query;
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

            Tables\Columns\TextColumn::make('status.name')
                ->label(__('Status'))
                ->formatStateUsing(fn($record) => new HtmlString('
                    <div class="flex items-center gap-2">
                        <span class="filament-tables-color-column relative flex h-4 w-4 rounded-md"
                            style="background-color: ' . $record->status->color . '"
                            title="' . $record->status->name . '"></span>
                        <span class="text-sm">' . $record->status->name . '</span>
                    </div>
                '))
                ->sortable(),

            Tables\Columns\TextColumn::make('priority.name')
                ->label(__('Priority'))
                ->formatStateUsing(fn($record) => new HtmlString('
                    <div class="flex items-center gap-2">
                        <span class="filament-tables-color-column relative flex h-4 w-4 rounded-md"
                            style="background-color: ' . $record->priority->color . '"
                            title="' . $record->priority->name . '"></span>
                        <span class="text-sm">' . $record->priority->name . '</span>
                    </div>
                '))
                ->sortable(),
        ];
    }

    protected function getTableBulkActions(): array
    {
        return [
            Tables\Actions\BulkAction::make('setTodo')
                ->label('تنظیم به عنوان انجام نشده')
                ->color('primary')
                ->icon('heroicon-o-clock')
                ->visible(fn() => $this->activeFilter !== 'todo')
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
                        $this->notify('success', 'کارها به عنوان انجام نشده تنظیم شدند');
                        $this->emit('refreshTable');
                    } catch (\Exception $e) {
                        $this->notify('error', 'خطا در بروزرسانی کارها: ' . $e->getMessage());
                    }
                }),

            Tables\Actions\BulkAction::make('setInProgress')
                ->label('تنظیم به عنوان در حال انجام')
                ->color('warning')
                ->icon('heroicon-o-play')
                ->visible(fn() => $this->activeFilter !== 'in_progress')
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
                        $this->notify('success', 'کارها به عنوان در حال انجام تنظیم شدند');
                        $this->emit('refreshTable');
                    } catch (\Exception $e) {
                        $this->notify('error', 'خطا در بروزرسانی کارها: ' . $e->getMessage());
                    }
                }),

            Tables\Actions\BulkAction::make('setDone')
                ->label('تنظیم به عنوان انجام شده')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->visible(fn() => $this->activeFilter !== 'done')
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
                        $this->notify('success', 'کارها به عنوان انجام شده تنظیم شدند');
                        $this->emit('refreshTable');
                    } catch (\Exception $e) {
                        $this->notify('error', 'خطا در بروزرسانی کارها: ' . $e->getMessage());
                    }
                }),

            Tables\Actions\BulkAction::make('setArchive')
                ->label('تنظیم به عنوان آرشیو')
                ->color('secondary')
                ->icon('heroicon-o-archive')
                ->visible(fn() => $this->activeFilter !== 'archived')
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
                        $this->notify('success', 'کارها به عنوان آرشیو تنظیم شدند');
                        $this->emit('refreshTable');
                    } catch (\Exception $e) {
                        $this->notify('error', 'خطا در بروزرسانی کارها: ' . $e->getMessage());
                    }
                }),

            Tables\Actions\BulkAction::make('setRejected')
                ->label('تنظیم به عنوان رد شده')
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->visible(fn() => auth()->user()->hasRole('Project Manager') && $this->activeFilter !== 'rejected')
                ->action(function (Collection $records) {
                    try {
                        $records->each(function ($record) {
                            // Update the approval status to rejected
                            $record->approved = -1;
                            $record->approved_by = auth()->user()->id;
                            $record->save();
                        });
                        $this->notify('success', 'کارها به عنوان رد شده تنظیم شدند');
                        $this->emit('refreshTable');
                    } catch (\Exception $e) {
                        $this->notify('error', 'خطا در بروزرسانی کارها: ' . $e->getMessage());
                    }
                }),
        ];
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
        return 3;
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
}