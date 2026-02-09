<?php

namespace App\Exports;

use App\Models\Incident;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class IncidentsExport implements 
    FromCollection, 
    WithHeadings, 
    WithMapping,
    WithStyles,
    WithDrawings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $reporter;
    protected $source;
    protected $dateFrom;
    protected $dateTo;

    public function __construct($reporter = null, $source = null, $dateFrom = null, $dateTo = null)
    {
        $this->reporter = $reporter;
        $this->source = $source;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    public function collection()
    {
        $query = Incident::orderBy('created_at', 'desc')
            ->with('barangay', 'classification', 'attachments');

        if ($this->reporter) {
            $query = $query->where('reporter', $this->reporter);
        }

        if ($this->source) {
            $query = $query->where('source', $this->source);
        }
        
        if ($this->dateFrom && $this->dateTo) {
            $query = $query->whereBetween('date_of_report', [$this->dateFrom, $this->dateTo]);
        }

        return $query->get();
    }

    public function map($incident): array
    {
        return [
            $incident->reference,
            $incident->file_number,
            $incident->classification->name ?? 'N/A',
            $incident->subject,
            $incident->date_of_report,
            $incident->reporter,
            $incident->designation,
            $incident->source,
            $incident->date_acquired,
            $incident->manner_acquired,
            $incident->evaluation,
            $incident->barangay->region ?? 'N/A',
            $incident->barangay->province ?? 'N/A',
            $incident->barangay->city_municipality ?? 'N/A',
            $incident->barangay->barangay ?? 'N/A',
            $incident->information_proper,
            $incident->analysis,
            $incident->actions,
        ];
    }
    
    public function headings(): array
    {
        return [
            'File Number',
            'Reference',
            'Classification',
            'Subject',
            'Date of Report',
            'Reporter',
            'Designation',
            'Source',
            'Date Acquired',
            'Manner Acquired',
            'Evaluation',
            'Region',
            'Province',  
            'City/Municipality',
            'Barangay',    
            'Information Proper',  
            'Analysis',  
            'Actions', 
            'Attachments',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $sheet->getStyle("A1:{$highestColumn}{$highestRow}")
            ->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => 'thin',
                    ],
                ],
                'alignment' => [
                    'wrapText' => true,
                    'vertical' => 'top',
                ],
            ]);

        $sheet->getStyle("A1:{$highestColumn}1")
            ->getFont()
            ->setBold(true);

        return [];
    }

    public function drawings()
    {
        $drawings = [];

        $rowIndex = 2; 

       foreach ($this->collection() as $incident) {
            $attachment = $incident->attachments->first();
            if ($attachment) {
                $filePath = storage_path('app/public/incident/attachments/' . $attachment->file_name);

                if (file_exists($filePath)) {
                    $drawing = new Drawing();
                    $drawing->setName('Attachment');
                    $drawing->setDescription('Attachment Image');
                    $drawing->setPath($filePath);
                    $drawing->setHeight(100);
                    $drawing->setCoordinates("S{$rowIndex}");
                    $drawings[] = $drawing;
                }
            }

            $rowIndex++;
        }

        return $drawings;
    }
}
