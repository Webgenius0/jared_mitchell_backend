<?php

namespace App\Exports;

use App\Models\ContestApplication;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ContestApplicationsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, ShouldAutoSize
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = ContestApplication::with(['business.user.profile', 'season', 'approver']);

        // Status filter
        if ($this->request->filled('status')) {
            $query->where('status', $this->request->status);
        }

        // Search
        if ($this->request->filled('search')) {
            $search = $this->request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('business', function ($b) use ($search) {
                    $b->where('business_name', 'like', "%{$search}%");
                })
                ->orWhereHas('business.user.profile', function ($p) use ($search) {
                    $p->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('business.user', function ($u) use ($search) {
                    $u->where('email', 'like', "%{$search}%");
                })
                ->orWhereHas('season', function ($s) use ($search) {
                    $s->where('title', 'like', "%{$search}%");
                });
            });
        }

        // Season filter
        if ($this->request->filled('season_id')) {
            $query->where('season_id', $this->request->season_id);
        }

        // Date range filter
        if ($this->request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $this->request->date_from);
        }
        if ($this->request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $this->request->date_to);
        }

        return $query->latest();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Business Name',
            'Owner Name',
            'Owner Email',
            'Season',
            'Status',
            'AI Rating',
            'Applied Date',
            'Approved Date',
            'Approved By',
            'Admin Note',
        ];
    }

    public function map($application): array
    {
        return [
            $application->id,
            $application->business?->business_name ?? '—',
            $application->business?->user?->profile?->name ?? '—',
            $application->business?->user?->email ?? '—',
            $application->season?->title ?? '—',
            ucfirst($application->status),
            $application->ai_score !== null ? number_format((float) $application->ai_score, 1) : '—',
            $application->created_at->format('Y-m-d H:i'),
            $application->approved_at?->format('Y-m-d H:i') ?? '—',
            $application->approver?->profile?->name ?? $application->approver?->email ?? '—',
            $application->admin_note ?? '—',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style the header row
        $sheet->getStyle('A1:K1')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF1B2A4A'],
            ],
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
                'size' => 12,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Set header row height
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Style data rows with alternating colors
        $lastRow = $sheet->getHighestRow();
        for ($row = 2; $row <= $lastRow; $row++) {
            $rowColor = $row % 2 === 0 ? 'FFF8F9FA' : 'FFFFFFFF';
            $sheet->getStyle("A{$row}:K{$row}")->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => $rowColor],
                ],
                'font' => [
                    'size' => 11,
                    'name' => 'Calibri',
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
            $sheet->getRowDimension($row)->setRowHeight(22);
        }

        // Add borders to all cells
        $sheet->getStyle("A1:K{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FFDEE2E6'],
                ],
            ],
        ]);

        // Freeze the header row
        $sheet->freezePane('A2');
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 30,
            'C' => 25,
            'D' => 30,
            'E' => 20,
            'F' => 14,
            'G' => 14,
            'H' => 20,
            'I' => 20,
            'J' => 25,
            'K' => 30,
        ];
    }
}
