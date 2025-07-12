<?php

namespace App\Filament\Pages;

use Carbon\Carbon;
use Filament\Pages\Page;
use App\Models\Ticket;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DailyReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.daily-report';
    protected static ?string $navigationTitle = "Daily Task & Report";
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public $report = [];
    public $date;

    public $rejectedTickets;

    public function mount()
    {
        $this->date = now()->toDateString();
        $this->generateReport();
        $userId = auth()->id();
        $this->rejectedTickets = Ticket::where('approved', -1)
            ->where(function ($q) use ($userId) {
                $q->where('owner_id', $userId)
                    ->orWhere('responsible_id', $userId);
            })
            ->get();
    }

    public function updatedDate()
    {
        $this->generateReport();
    }

    public function generateReport()
    {
        $user = auth()->user();
        $tickets = Ticket::where(function ($q) use ($user) {
            $q->where('responsible_id', $user->id);
        })
            ->whereDate('updated_at', '<=', $this->date)
            ->whereHas('status', function ($q) {
                $q->whereIn('name', ['Todo', 'In progress']);
            })
            ->where('approved', '!=', -1)
            ->get();
        $this->report = $tickets;
    }

    public function exportExcel(): BinaryFileResponse
    {
        $export = new class ($this->report) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
            private $report;
            public function __construct($report)
            {
                $this->report = $report;
            }
            public function collection()
            {
                // Map each ticket to an array of all its attributes
                return collect($this->report)->map(function ($ticket) {
                    return [
                        'ID' => $ticket->id,
                        'Name' => $ticket->name,
                        'Content' => strip_tags($ticket->content),
                        'Owner' => optional($ticket->owner)->name,
                        'Responsible' => optional($ticket->responsible)->name,
                        'Status' => optional($ticket->status)->name,
                        'Project' => optional($ticket->project)->name,
                        'Approved' => $ticket->approved ? 'Yes' : 'No',
                        'Estimation' => $ticket->estimation,
                        'Created At' => Carbon::parse($ticket->created_at)->diffForHumans(),
                        'Updated At' => Carbon::parse($ticket->updated_at)->diffForHumans(),
                        'Type' => optional($ticket->type)->name,
                        'Priority' => optional($ticket->priority)->name,
                        'Epic' => optional($ticket->epic)->name,
                        'Sprint' => optional($ticket->sprint)->name,
                        // Add more fields as needed
                    ];
                });
            }
            public function headings(): array
            {
                return [
                    'ID',
                    'Name',
                    'Content',
                    'Owner',
                    'Responsible',
                    'Status',
                    'Project',
                    'Approved',
                    'Estimation',
                    'Created At',
                    'Updated At',
                    'Type',
                    'Priority',
                    'Epic',
                    'Sprint',
                ];
            }
        };
        return Excel::download($export, 'daily_report_' . $this->date . '.xlsx');
    }
}
