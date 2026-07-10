<?php

namespace App\Exports;

use App\Models\EventRegistration;
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

class EventRegistrationsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, ShouldAutoSize
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = EventRegistration::with([
            'event:id,title,starts_at,venue_name,city,state,status',
            'ticketTier:id,name,price',
        ]);

        if ($this->request->filled('search_query')) {
            $search = $this->request->search_query;
            $query->where(function ($q) use ($search) {
                $q->where('booking_reference', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('event', function ($sub) use ($search) {
                      $sub->where('title', 'like', "%{$search}%");
                  });
            });
        }

        if ($this->request->filled('status')) {
            $query->where('status', $this->request->status);
        }

        if ($this->request->filled('payment_status')) {
            $query->where('payment_status', $this->request->payment_status);
        }

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
            'Booking Reference',
            'Event',
            'Event Date',
            'Ticket Tier',
            'Customer Name',
            'Email',
            'Phone',
            'Quantity',
            'Unit Price',
            'Service Fee',
            'Total',
            'Currency',
            'Payment Status',
            'Registration Status',
            'Registered At',
        ];
    }

    public function map($row): array
    {
        $name = trim($row->first_name . ' ' . $row->last_name);

        return [
            $row->id,
            $row->booking_reference,
            $row->event?->title ?? 'N/A',
            $row->event?->starts_at?->format('Y-m-d H:i') ?? 'N/A',
            $row->ticketTier?->name ?? 'N/A',
            $name ?: '—',
            $row->email,
            $row->phone_number ?? '—',
            $row->quantity ?? 1,
            number_format($row->unit_price ?? 0, 2),
            number_format($row->service_fee ?? 0, 2),
            number_format($row->total ?? 0, 2),
            $row->currency ?? 'USD',
            $row->payment_status ? ucfirst($row->payment_status) : 'Unpaid',
            ucfirst($row->status),
            $row->created_at->format('Y-m-d H:i'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:P1')->applyFromArray([
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
            $sheet->getStyle("A{$row}:P{$row}")->applyFromArray([
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

        $sheet->getStyle("A1:P{$lastRow}")->applyFromArray([
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
            'B' => 22,
            'C' => 30,
            'D' => 18,
            'E' => 18,
            'F' => 25,
            'G' => 28,
            'H' => 18,
            'I' => 10,
            'J' => 14,
            'K' => 14,
            'L' => 14,
            'M' => 10,
            'N' => 16,
            'O' => 20,
            'P' => 18,
        ];
    }
}
