<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Exports\TicketHoursExport;
use App\Filament\Resources\TicketResource;
use App\Models\Activity;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\TicketHour;
use App\Models\TicketSubscriber;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class ViewTicket extends ViewRecord implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = TicketResource::class;

    protected static string $view = 'filament.resources.tickets.view';

    public string $tab = 'comments';

    protected $listeners = ['doDeleteComment'];

    public $selectedCommentId;

    public function mount($record): void
    {
        $Ticket = Ticket::findOrFail($record);

        parent::mount($record);
        $this->form->fill();
    }

    protected function getActions(): array
    {
        return [
            // if the current tikcet was in todo this sholuld not appear
            
            Actions\Action::make('setTodo')
                ->label(__('Set Todo'))
                ->color('primary')
                ->icon('heroicon-o-clock')
                ->action(function () {
                    $ticket = Ticket::findOrFail($this->record->id);
                    $oldStatusId = $ticket->status_id;
                    $ticket->status_id = 1; // Todo status ID
                    $ticket->save();

                    if ($oldStatusId != 1) {
                        \App\Models\TicketActivity::create([
                            'ticket_id' => $ticket->id,
                            'old_status_id' => $oldStatusId,
                            'new_status_id' => 1,
                            'user_id' => auth()->user()->id
                        ]);
                    }

                    $this->dispatchBrowserEvent('notification', [
                        'title' => __('Status Updated'),
                        'message' => __('Ticket set to Todo'),
                        'color' => 'success',
                    ]);
                }),

            Actions\Action::make('setInProgress')
                ->label(__('Set In Progress'))
                ->color('warning')
                ->icon('heroicon-o-play')
                ->action(function () {
                    $ticket = Ticket::findOrFail($this->record->id);
                    $oldStatusId = $ticket->status_id;
                    $ticket->status_id = 2; // In Progress status ID
                    $ticket->save();

                    if ($oldStatusId != 2) {
                        \App\Models\TicketActivity::create([
                            'ticket_id' => $ticket->id,
                            'old_status_id' => $oldStatusId,
                            'new_status_id' => 2,
                            'user_id' => auth()->user()->id
                        ]);
                    }

                    $this->dispatchBrowserEvent('notification', [
                        'title' => __('Status Updated'),
                        'message' => __('Ticket set to In Progress'),
                        'color' => 'success',
                    ]);
                }),

            Actions\Action::make('setDone')
                ->label(__('Set Done'))
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->action(function () {
                    $ticket = Ticket::findOrFail($this->record->id);
                    $oldStatusId = $ticket->status_id;
                    $ticket->status_id = 3; // Done status ID
                    $ticket->save();

                    if ($oldStatusId != 3) {
                        \App\Models\TicketActivity::create([
                            'ticket_id' => $ticket->id,
                            'old_status_id' => $oldStatusId,
                            'new_status_id' => 3,
                            'user_id' => auth()->user()->id
                        ]);
                    }

                    $this->dispatchBrowserEvent('notification', [
                        'title' => __('Status Updated'),
                        'message' => __('Ticket set to Done'),
                        'color' => 'success',
                    ]);
                }),

            Actions\Action::make('setArchive')
                ->label(__('Set Archive'))
                ->color('secondary')
                ->icon('heroicon-o-archive')
                ->action(function () {
                    $ticket = Ticket::findOrFail($this->record->id);
                    $oldStatusId = $ticket->status_id;
                    $ticket->status_id = 4; // Archive status ID
                    $ticket->save();

                    if ($oldStatusId != 4) {
                        \App\Models\TicketActivity::create([
                            'ticket_id' => $ticket->id,
                            'old_status_id' => $oldStatusId,
                            'new_status_id' => 4,
                            'user_id' => auth()->user()->id
                        ]);
                    }

                    $this->dispatchBrowserEvent('notification', [
                        'title' => __('Status Updated'),
                        'message' => __('Ticket archived'),
                        'color' => 'success',
                    ]);
                }),
            Actions\Action::make('rate')
                ->label('رضایتمندی')
                ->color('success')
                ->icon('heroicon-o-star')
                ->button()
                ->form([
                    \Filament\Forms\Components\Select::make('rating')
                        ->label('رضایتمندی')
                        ->options([
                            1 => '★☆☆☆☆ خیلی بد',
                            2 => '★★☆☆☆ بد',
                            3 => '★★★☆☆ متوسط',
                            4 => '★★★★☆ خوب',
                            5 => '★★★★★ خیلی خوب',
                        ])
                        ->required(),
                ])
                ->visible(function () {
                    $Ticket = \App\Models\Ticket::findOrFail($this->record->id);

                    // Only show for users with Manager role
                    return auth()->user()->hasRole('Project Manager') && 
                           !$Ticket->ratings()->where('user_id', auth()->id())->exists();
                })
                ->action(function (array $data) {
                    $Ticket = Ticket::findOrFail($this->record->id);
                    $Ticket->rateOnce($data['rating']);
                    $avg = number_format($Ticket->averageRating, 2);
                    $this->dispatchBrowserEvent('notification', [
                        'title' => 'رضایتمندی تنظیم شد',
                        'message' => 'رضایتمندی به ' . $data['rating'] . ' ستاره تنظیم شد. رضایتمندی میانگین: ' . $avg . ' / 5',
                        'color' => 'success',
                    ]);
                }),
            Actions\Action::make('editRating')
                ->label('ویرایش رضایتمندی')
                ->color('warning')
                ->icon('heroicon-o-pencil')
                ->button()
                ->form([
                    \Filament\Forms\Components\Select::make('rating')
                        ->label('رضایتمندی')
                        ->options([
                            1 => '★☆☆☆☆ خیلی بد',
                            2 => '★★☆☆☆ بد',
                            3 => '★★★☆☆ متوسط',
                            4 => '★★★★☆ خوب',
                            5 => '★★★★★ خیلی خوب',
                        ])
                        ->required()
                        ->default(function () {
                            $Ticket = \App\Models\Ticket::findOrFail($this->record->id);
                            $rating = $Ticket->ratings()->where('user_id', auth()->id())->first();
                            return $rating ? $rating->rating : null;
                        }),
                ])
                ->visible(function () {
                    $Ticket = \App\Models\Ticket::findOrFail($this->record->id);
                    // Only show for users with Manager role
                    return auth()->user()->hasRole('Project Manager') && 
                           $Ticket->ratings()->where('user_id', auth()->id())->exists();
                })
                ->action(function (array $data) {
                    $Ticket = Ticket::findOrFail($this->record->id);
                    $Ticket->rateOnce($data['rating']);
                    $avg = number_format($Ticket->averageRating, 2);
                    $this->dispatchBrowserEvent('notification', [
                        'title' => 'رضایتمندی تنظیم شد',
                        'message' => 'رضایتمندی به ' . $data['rating'] . ' ستاره تنظیم شد. رضایتمندی میانگین: ' . $avg . ' / 5',
                        'color' => 'warning',
                    ]);
                }),
            Actions\Action::make('toggleSubscribe')
                ->label(
                    fn() => $this->record->subscribers()->where('users.id', auth()->user()->id)->count() ?
                    __('Unsubscribe')
                    : __('Subscribe')
                )
                ->visible(fn() => auth()->user()->hasRole('Project Manager'))
                ->color(
                    fn() => $this->record->subscribers()->where('users.id', auth()->user()->id)->count() ?
                    'danger'
                    : 'success'
                )
                ->icon('heroicon-o-bell')
                ->button()
                ->action(function () {
                    if (
                        $sub = TicketSubscriber::where('user_id', auth()->user()->id)
                            ->where('ticket_id', $this->record->id)
                            ->first()
                    ) {
                        $sub->delete();
                        $this->notify('success', __('You unsubscribed from the ticket'));
                    } else {
                        TicketSubscriber::create([
                            'user_id' => auth()->user()->id,
                            'ticket_id' => $this->record->id
                        ]);
                        $this->notify('success', __('You subscribed to the ticket'));
                    }
                    $this->record->refresh();
                }),
            Actions\Action::make('share')
                ->label('')
                ->color('secondary')
                ->button()
                ->icon('heroicon-o-share')
                ->action(fn() => $this->dispatchBrowserEvent('shareTicket', [
                    'url' => route('filament.resources.tickets.share', $this->record->code)
                ])),
            Actions\EditAction::make(),
            Actions\Action::make('logHours')
                ->label(__('Log time by Hours'))
                ->icon('heroicon-o-clock')
                ->color('warning')
                ->modalWidth('sm')
                ->modalHeading(__('Log worked time'))
                ->modalSubheading(__('Use the following form to add your worked time in this ticket.'))
                ->modalButton(__('Log'))
                ->visible(fn() => in_array(
                    auth()->user()->id,
                    [$this->record->owner_id, $this->record->responsible_id]
                ))
                ->form([
                    TextInput::make('time')
                        ->label(__('Time to log'))
                        ->numeric()
                        ->required(),
                    Select::make('activity_id')
                        ->label(__('Activity'))
                        ->searchable()
                        ->reactive()
                        ->options(function ($get, $set) {
                            return Activity::all()->pluck('name', 'id')->toArray();
                        }),
                    Textarea::make('comment')
                        ->label(__('Comment'))
                        ->rows(3),
                ])
                ->action(function (Collection $records, array $data): void {
                    $value = $data['time'];
                    $comment = $data['comment'];
                    TicketHour::create([
                        'ticket_id' => $this->record->id,
                        'activity_id' => $data['activity_id'],
                        'user_id' => auth()->user()->id,
                        'value' => $value,
                        'comment' => $comment
                    ]);
                    $this->record->refresh();
                    $this->notify('success', __('Time logged into ticket'));
                }),
               
            Actions\ActionGroup::make([
                // Actions\Action::make('exportLogHours')
                //     ->label(__('Export time logged'))
                //     ->icon('heroicon-o-document-download')
                //     ->color('warning')
                //     ->visible(
                //         fn() => $this->record->watchers->where('id', auth()->user()->id)->count()
                //         && $this->record->hours()->count()
                //     )
                //     ->action(fn() => Excel::download(
                //         new TicketHoursExport($this->record),
                //         'time_' . str_replace('-', '_', $this->record->code) . '.xlsx',
                //         \Maatwebsite\Excel\Excel::XLSX,
                //         ['Content-Type' => 'text/xlsx']
                //     )),
                Actions\Action::make('Reject')
                ->label(__('reject'))
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->visible(fn() => auth()->user()->hasRole('Project Manager') && $this->record->approved === 1 || $this->record->approved === -1)
                ->action(function () {
                    $this->record->approved = -1;
                    $this->record->save();
                    $this->notify('danger', __('Rejected successfully.'));
                    $this->record->refresh();
                }),
                    Actions\Action::make('approve')
                    ->label(__('Approve'))
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->visible(fn() => $this->record->approved !== 1 && auth()->user()->hasRole('Project Manager'))
                    ->action(function () {
                        $this->record->approved = 1;
                        $this->record->save();
                        $this->notify('success', __('Ticket approved successfully.'));
                        $this->record->refresh();
                    }),
            ])
                ->visible(fn() => (in_array(
                    auth()->user()->id,
                    [$this->record->owner_id, $this->record->responsible_id]
                )) || (
                    $this->record->watchers->where('id', auth()->user()->id)->count()
                    && $this->record->hours()->count()
                ))
                ->color('secondary'),
                
           
           
        ];
    }

    public function selectTab(string $tab): void
    {
        $this->tab = $tab;
    }

    protected function getFormSchema(): array
    {
        return [
            RichEditor::make('comment')
                ->disableLabel()
                ->placeholder(__('Type a new comment'))
                ->required()
        ];
    }

    public function submitComment(): void
    {
        $data = $this->form->getState();
        if ($this->selectedCommentId) {
            TicketComment::where('id', $this->selectedCommentId)
                ->update([
                    'content' => $data['comment']
                ]);
        } else {
            TicketComment::create([
                'user_id' => auth()->user()->id,
                'ticket_id' => $this->record->id,
                'content' => $data['comment']
            ]);
        }
        $this->record->refresh();
        $this->cancelEditComment();
        $this->notify('success', __('Comment saved'));
    }

    public function isAdministrator(): bool
    {
        return $this->record
            ->project
            ->users()
            ->where('users.id', auth()->user()->id)
            ->where('role', 'Project Manager')
            ->count() != 0;
    }

    public function editComment(int $commentId): void
    {
        $this->form->fill([
            'comment' => $this->record->comments->where('id', $commentId)->first()?->content
        ]);
        $this->selectedCommentId = $commentId;
    }

    public function deleteComment(int $commentId): void
    {
        Notification::make()
            ->warning()
            ->title(__('Delete confirmation'))
            ->body(__('Are you sure you want to delete this comment?'))
            ->actions([
                Action::make('confirm')
                    ->label(__('Confirm'))
                    ->color('danger')
                    ->button()
                    ->close()
                    ->emit('doDeleteComment', compact('commentId')),
                Action::make('cancel')
                    ->label(__('Cancel'))
                    ->close()
            ])
            ->persistent()
            ->send();
    }

    public function doDeleteComment(int $commentId): void
    {
        TicketComment::where('id', $commentId)->delete();
        $this->record->refresh();
        $this->notify('success', __('Comment deleted'));
    }

    public function cancelEditComment(): void
    {
        $this->form->fill();
        $this->selectedCommentId = null;
    }
}