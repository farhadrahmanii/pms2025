<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Ticket;

class TicketApprovel extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.ticket-approvel';

    public $tickets;

    protected static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->hasRole('Default role');
    }

    public function mount()
    {
        $this->tickets = Ticket::where('approved', 0)->where('owner_id', auth()->id())->get();
    }

    public function approve($ticketId)
    {
        $ticket = Ticket::findOrFail($ticketId);
        $ticket->approved = 1;
        $ticket->save();
        $this->tickets = Ticket::where('approved', 0)->where('owner_id', auth()->id())->get();
    }

    public function reject($ticketId)
    {
        $ticket = Ticket::findOrFail($ticketId);
        $ticket->approved = -1; // Use -1 to represent rejection
        $ticket->save();
        $this->tickets = Ticket::where('approved', 0)->where('owner_id', auth()->id())->get();
    }

    public function deleteTicket($ticketId)
    {
        $ticket = Ticket::findOrFail($ticketId);
        $ticket->delete();
        $this->tickets = Ticket::where('approved', 0)->where('owner_id', auth()->id())->get();
    }
}
