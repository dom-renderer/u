<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TicketsExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithMapping
{
    protected $tickets;

    public function __construct($tickets)
    {
        $this->tickets = $tickets;
    }

    public function collection()
    {
        return $this->tickets;
    }

    public function headings(): array
    {
        return [
            'S.No', //Iteration
            'Id', //Ticket Number
            'Title',
            'Priority',
            'Department',
            'Particular',
            'Issue',
            'Created At',
            'Accepted At',
            'Closed At',
            'Reopened At',
            'SO Name',
            'Created By',
            'Agent Name', //show assigned primary user coma separeted
            'Approval ESC1',
            'Approval ESC2',
            'Completion ESC1',
            'Completion ESC2',
            'status'
        ];
    }



    protected $rowNumber = 0;

    public function map($ticket): array
    {
        $this->rowNumber++;

        // Get primary owners (agents) - comma separated
        $agentNames = $ticket->primaryOwners->map(function($owner) {
            if ($owner->user) {
                return trim($owner->user->name . ' ' . ($owner->user->middle_name ?? '') . ' ' . ($owner->user->last_name ?? ''));
            }
            return null;
        })->filter()->implode(', ') ?: '-';

        // Get accepted_at timestamp from histories
        $acceptedAt = '';
        $acceptedHistory = $ticket->histories->first(function($history) {
            return isset($history->data['status']) && $history->data['status'] === 'accepted';
        });
        if ($acceptedHistory) {
            $acceptedAt = $acceptedHistory->created_at ? $acceptedHistory->created_at->format('d M, Y h:i A') : '';
        }

        // Get closed_at timestamp from histories
        $closedAt = '';
        $closedHistory = $ticket->histories->first(function($history) {
            return isset($history->data['status']) && $history->data['status'] === 'closed';
        });
        if ($closedHistory) {
            $closedAt = $closedHistory->created_at ? $closedHistory->created_at->format('d M, Y h:i A') : '';
        }

        // Get reopened_at timestamp from histories
        $reopenedAt = '';
        $reopenedHistory = $ticket->histories->first(function($history) {
            return isset($history->data['reopened']) && $history->data['reopened'] === true;
        });
        if ($reopenedHistory) {
            $reopenedAt = $reopenedHistory->created_at ? $reopenedHistory->created_at->format('d M, Y h:i A') : '';
        }

        // Calculate ESC1 and ESC2 times if ticket is in progress
        $esc1 = '';
        $esc2 = '';
        if ($ticket->in_progress_at && $ticket->status !== 'closed') {
            $inProgressTime = \Carbon\Carbon::parse($ticket->in_progress_at);
            $now = \Carbon\Carbon::now();
            $diff = $inProgressTime->diff($now);
            
            // Format: "X days, Y hours, Z minutes"
            $timeString = $diff->d . ' days, ' . $diff->h . ' hours, ' . $diff->i . ' minutes';
            
            // ESC1 and ESC2 would be calculated based on escalation rules
            // For now, showing the time elapsed
            $esc1 = $timeString;
            $esc2 = $timeString;
        }

        $pesc1 = '';
        $pesc2 = '';
        if ($ticket->created_at && $ticket->status !== 'pending') {
            $inProgressTime = \Carbon\Carbon::parse($ticket->created_at);
            $now = \Carbon\Carbon::now();
            $diff = $inProgressTime->diff($now);
            
            // Format: "X days, Y hours, Z minutes"
            $timeString = $diff->d . ' days, ' . $diff->h . ' hours, ' . $diff->i . ' minutes';
            
            // ESC1 and ESC2 would be calculated based on escalation rules
            // For now, showing the time elapsed
            $pesc1 = $timeString;
            $esc2 = $timeString;
        }

        // Store name format: "code name"
        $storeName = ($ticket->store->code ?? '') . ' ' . ($ticket->store->name ?? '');
        $storeName = trim($storeName) ?: '-';

        return [
            $this->rowNumber, // S.No
            $ticket->ticket_number ?? '-', // Id
            $ticket->subject ?? '-', // Title
            ucfirst($ticket->priority ?? '-'), // Priority
            optional($ticket->department)->name ?? '-', // Department
            optional($ticket->particular)->name ?? '-', // Particular
            optional($ticket->issue)->name ?? '-', // Issue
            $ticket->created_at ? $ticket->created_at->format('d M, Y h:i A') : '', // Created At
            $acceptedAt, // Accepted At
            $closedAt, // Closed At
            $reopenedAt, // Reopened At
            $storeName, // SO Name
            $ticket->creator ? trim($ticket->creator->name . ' ' . ($ticket->creator->middle_name ?? '') . ' ' . ($ticket->creator->last_name ?? '')) : '-', // Created By
            $agentNames, // Agent Name
            $pesc1, // ESC1
            $pesc2, // ESC2
            $esc1, // ESC1
            $esc2, // ESC2
            ucfirst(str_replace('_', ' ', $ticket->status ?? '-')), // status
        ];
    }


    public function styles(Worksheet $sheet)
    {
        // Style the header row (A1:P1 for 16 columns)
        $sheet->getStyle('A1:S1')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A1:S1')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A1:S1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD9E1F2');

        // Set row height for header
        $sheet->getRowDimension(1)->setRowHeight(20);

        return [];
    }
}
