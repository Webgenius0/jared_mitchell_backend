<?php

namespace App\Exports;

use App\Models\Event;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class EventsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, ShouldAutoSize
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = Event::query();

        if ($this->request->filled('status')) {
            $query->where('status', $this->request->status);
        }

        if ($this->request->filled('event_type')) {
            $query->where('event_type', $this->request->event_type);
        }

        if ($this->request->filled('search')) {
            $search = $this->request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('venue_name', 'like', "%{$search}%")
                  ->orWhere('hosted_by', 'like', "%{$search}%");
            });
        }

        if ($this->request->filled('date_from')) {
            $query->whereDate('starts_at', '>=', $this->request->date_from);
        }

        if ($this->request->filled('date_to')) {
            $query->whereDate('starts_at', '<=', $this->request->date_to);
        }

        return $query->latest();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Title',
            'Event Type',
            'Status',
            'City',
            'Venue',
            'Hosted By',
            'Starts At',
            'Ends At',
            'Timezone',
        ];
    }

    public function map($event): array
    {
        return [
            $event->id,
            $event->title,
            ucwords(str_replace('_', ' ', $event->event_type)),
            ucfirst($event->status),
            $event->city ?? '—',
            $event->venue_name ?? '—',
            $event->hosted_by ?? '—',
            $event->starts_at?->format('Y-m-d H:i') ?? '—',
            $event->ends_at?->format('Y-m-d H:i') ?? '—',
            $event->timezone ?? '—',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:J1')->applyFromArray([
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF1B2A4A'],
            ],
            'font' => [
                'bold'  => true,
                'color' => ['argb' => 'FFFFFFFF'],
                'size'  => 12,
                'name'  => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(30);

        $lastRow = $sheet->getHighestRow();
        for ($row = 2; $row <= $lastRow; $row++) {
            $rowColor = $row % 2 === 0 ? 'FFF8F9FA' : 'FFFFFFFF';
            $sheet->getStyle("A{$row}:J{$row}")->applyFromArray([
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
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

        $sheet->getStyle("A1:J{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['argb' => 'FFDEE2E6'],
                ],
            ],
        ]);

        $sheet->freezePane('A2');
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 35,
            'C' => 20,
            'D' => 14,
            'E' => 18,
            'F' => 28,
            'G' => 22,
            'H' => 20,
            'I' => 20,
            'J' => 10,
        ];
    }
}
