<?php

namespace App\Exports;

use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ProjectReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected $startDate;
    protected $endDate;
    protected $userId;
    protected $projectId;

    public function __construct($startDate, $endDate, $userId = null, $projectId = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->userId = $userId ?? auth()->id();
        $this->projectId = $projectId;
    }

    public function title(): string
    {
        return 'Project Report - ' . Carbon::parse($this->startDate)->format('Y-m-d') . ' to ' . Carbon::parse($this->endDate)->format('Y-m-d');
    }

    public function headings(): array
    {
        return [
            'Ticket ID',
            'Project Name',
            'Ticket Name',
            'Description',
            'Owner',
            'Responsible',
            'Status',
            'Priority',
            'Type',
            'Estimated Hours',
            'Progress %',
            'End Date',
            'Days Remaining',
            'Approval Status',
            'Created Date',
            'Last Updated',
            'Epic',
            'Sprint',
        ];
    }

    public function map($ticket): array
    {
        $endDate = $ticket->end_date ? Carbon::parse($ticket->end_date) : null;
        $now = now();
        
        // Calculate progress
        if (!$endDate) {
            $progress = 0;
            $daysRemaining = 'No deadline';
        } else {
            $createdAt = $ticket->created_at ? Carbon::parse($ticket->created_at) : $now;
            $totalPeriod = $createdAt->diffInDays($endDate, false);
            $elapsed = $createdAt->diffInDays($now, false);
            $progress = $totalPeriod > 0 ? min(100, max(0, round(($elapsed / $totalPeriod) * 100))) : 0;
            
            if ($endDate->isPast()) {
                $daysRemaining = 'Expired ' . $endDate->diffForHumans();
            } elseif ($endDate->isToday()) {
                $daysRemaining = 'Due today';
            } else {
                $daysRemaining = $endDate->diffForHumans() . ' remaining';
            }
        }

        return [
            $ticket->id,
            optional($ticket->project)->name ?? 'N/A',
            $ticket->name,
            strip_tags($ticket->content ?? ''),
            optional($ticket->owner)->name ?? 'N/A',
            optional($ticket->responsible)->name ?? 'N/A',
            optional($ticket->status)->name ?? 'N/A',
            optional($ticket->priority)->name ?? 'N/A',
            optional($ticket->type)->name ?? 'N/A',
            $ticket->estimation ?? 0,
            $progress . '%',
            $endDate ? $endDate->format('Y-m-d') : 'N/A',
            $daysRemaining,
            $this->getApprovalStatus($ticket->approved),
            $ticket->created_at ? Carbon::parse($ticket->created_at)->format('Y-m-d H:i') : 'N/A',
            $ticket->updated_at ? Carbon::parse($ticket->updated_at)->format('Y-m-d H:i') : 'N/A',
            optional($ticket->epic)->name ?? 'N/A',
            optional($ticket->sprint)->name ?? 'N/A',
        ];
    }

    public function collection(): Collection
    {
        // Build base query - show all users' tasks, not just current user
        $query = Ticket::whereBetween('updated_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59'])
            ->with(['project', 'owner', 'responsible', 'status', 'priority', 'type', 'epic', 'sprint']);
        
        // Add project filter if project is specified
        if ($this->projectId) {
            $query->where('project_id', $this->projectId);
        }
        
        // Add user filter if user is specified
        if ($this->userId) {
            $query->where('responsible_id', $this->userId);
        }
        
        return $query->get();
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,  // Ticket ID
            'B' => 20,  // Project Name
            'C' => 30,  // Ticket Name
            'D' => 40,  // Description
            'E' => 15,  // Owner
            'F' => 15,  // Responsible
            'G' => 12,  // Status
            'H' => 12,  // Priority
            'I' => 12,  // Type
            'J' => 15,  // Estimated Hours
            'K' => 12,  // Progress %
            'L' => 12,  // End Date
            'M' => 20,  // Days Remaining
            'N' => 15,  // Approval Status
            'O' => 18,  // Created Date
            'P' => 18,  // Last Updated
            'Q' => 15,  // Epic
            'R' => 15,  // Sprint
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Header styling
        $sheet->getStyle('A1:R1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2E5BBA'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Data rows styling
        $lastRow = $sheet->getHighestRow();
        if ($lastRow > 1) {
            $sheet->getStyle('A2:R' . $lastRow)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);

            // Alternate row colors
            for ($row = 2; $row <= $lastRow; $row++) {
                if ($row % 2 == 0) {
                    $sheet->getStyle('A' . $row . ':R' . $row)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F8F9FA'],
                        ],
                    ]);
                }
            }
        }

        // Auto-filter
        $sheet->setAutoFilter('A1:R1');

        // Freeze first row
        $sheet->freezePane('A2');
    }

    private function getApprovalStatus($approved): string
    {
        return match($approved) {
            1 => 'Approved',
            0 => 'Pending',
            -1 => 'Rejected',
            default => 'Unknown'
        };
    }
} 