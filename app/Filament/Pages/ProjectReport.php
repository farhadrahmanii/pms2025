<?php

namespace App\Filament\Pages;

use Carbon\Carbon;
use Filament\Pages\Page;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Project;
use App\Exports\ProjectReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;

class ProjectReport extends Page
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.project-report';
    protected static ?string $navigationTitle = "راپور پروژه";
    protected static ?string $navigationheader = "راپور پروژه";
    protected static ?string $navigationLabel = "راپور پروژه";
    
    public static function canView(): bool
    {
        return auth()->check();
    }

    public $report = [];
    public $startDate;
    public $endDate;
    public $selectedUser;
    public $selectedProject;
    public $rejectedTickets;
    public $summary = [];

    public function mount()
    {
        $this->startDate = now()->subDays(30)->toDateString();
        $this->endDate = now()->toDateString();
        $this->selectedUser = null; // Show all users by default
        $this->selectedProject = null; // Show all projects by default
        
        // Set default empty data
        $this->report = collect();
        $this->rejectedTickets = collect();
        $this->summary = [
            'total_tickets' => 0,
            'todo_tickets' => 0,
            'in_progress_tickets' => 0,
            'completed_tickets' => 0,
            'pending_tickets' => 0,
            'rejected_tickets' => 0,
        ];
        
        // Try to load data, but don't let it break the page
        $this->loadData();
    }

    protected function getUserProjects()
    {
        return Project::where(function ($query) {
            return $query->where('owner_id', auth()->user()->id)
                ->orWhereHas('users', function ($query) {
                    return $query->where('users.id', auth()->user()->id);
                });
        })->get();
    }

    public function loadData()
    {
        try {
            // Build base query for all tickets
            $query = Ticket::whereBetween('updated_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59'])
                ->with('project');
            
            // Add user filter if user is selected
           if($this->selectedUser){
            $query->where('responsible_id', $this->selectedUser);
           }
            
            // Add project filter if project is selected
            if ($this->selectedProject) {
                $query->where('project_id', $this->selectedProject);
            }
            
            $this->report = $query->get();
            
            $this->summary = [
                'total_tickets' => $this->report->count(),
                'todo_tickets' => $this->report->where('approved', 1)->count(),
                'in_progress_tickets' => $this->report->where('approved', 1)->count(),
                'completed_tickets' => $this->report->where('approved', 1)->count(),
                'pending_tickets' => $this->report->where('approved', 0)->count(),
                'rejected_tickets' => $this->report->where('approved', -1)->count(),
            ];
            
            // Build rejected tickets query
            $rejectedQuery = Ticket::where('approved', -1)
                ->whereBetween('updated_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59']);
            
            // Add user filter to rejected tickets if user is selected
            // if ($this->selectedUser) {
            //     $user = User::find($this->selectedUser);
               
            // }
            
            // Add project filter to rejected tickets if project is selected
            if ($this->selectedProject) {
                $rejectedQuery->where('project_id', $this->selectedProject);
            }
            
            $this->rejectedTickets = $rejectedQuery->get();
                
        } catch (\Exception $e) {
            // If anything fails, just keep the default empty data
            $this->report = collect();
            $this->rejectedTickets = collect();
            $this->summary = [
                'total_tickets' => 0,
                'todo_tickets' => 0,
                'in_progress_tickets' => 0,
                'completed_tickets' => 0,
                'pending_tickets' => 0,
                'rejected_tickets' => 0,
            ];
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('فیلترهای گزارش')
                    ->description('انتخاب کنید تا گزارش را فیلتر کنید')
                    ->schema([
                        DatePicker::make('startDate')
                            ->label('تاریخ شروع')
                            ->required()
                            ->native(false)
                            ->displayFormat('Y-m-d')
                            ->closeOnDateSelection(),
                        
                        DatePicker::make('endDate')
                            ->label('تاریخ پایان')
                            ->required()
                            ->native(false)
                            ->displayFormat('Y-m-d')
                            ->closeOnDateSelection(),
                        
                        Select::make('selectedUser')
                            ->label('وظیفه')
                            ->options(User::pluck('name', 'id'))
                            ->searchable()
                            ->placeholder('همه وظیفه‌ها')
                            ->default(null),
                        
                        Select::make('selectedProject')
                            ->label('پروژه')
                            ->options($this->getUserProjects()->pluck('name', 'id'))
                            ->searchable()
                            ->placeholder('همه پروژه‌ها')
                            ->default(null),
                    ])
                    ->columns(4)
            ]);
    }

    public function updatedStartDate()
    {
        $this->loadData();
    }

    public function updatedEndDate()
    {
        $this->loadData();
    }

    public function updatedSelectedUser()
    {
        $this->loadData();
    }

    public function updatedSelectedProject()
    {
        $this->loadData();
    }

    public function exportExcel(): BinaryFileResponse
    {
        try {
            // Pass the filtered data to the export
            $export = new ProjectReportExport(
                $this->startDate, 
                $this->endDate, 
                $this->selectedUser,
                $this->selectedProject
            );
            $filename = 'project_report_' . $this->startDate . '_to_' . $this->endDate . '.xlsx';
            
            Notification::make()
                ->title('گزارش با موفقیت صادر شد')
                ->success()
                ->send();
                
            return Excel::download($export, $filename);
        } catch (\Exception $e) {
            Notification::make()
                ->title('خطا در صادر کردن گزارش')
                ->body('لطفاً دوباره تلاش کنید')
                ->danger()
                ->send();
            
            throw $e;
        }
    }

    public function refreshReport()
    {
        $this->loadData();
        
        Notification::make()
            ->title('گزارش بروزرسانی شد')
            ->success()
            ->send();
    }
}