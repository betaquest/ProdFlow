<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithTitle;

class ReporteGeneralExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return array_map(function ($item) {
            return [
                $item['cliente'],
                $item['proyecto'],
                $item['programa'],
                $item['fase'],
                $item['fecha_inicio'],
                $item['fecha_fin'],
                $item['duracion_texto'],
                $item['estado'],
                $item['porcentaje'] . '%',
                $item['observaciones'],
            ];
        }, $this->data);
    }

    public function headings(): array
    {
        return [
            'Cliente',
            'Proyecto',
            'Programa',
            'Fase',
            'Fecha Inicio',
            'Fecha Fin',
            'Duración (h/m)',
            'Estado',
            'Avance %',
            'Observaciones',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5'],
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25, // Cliente
            'B' => 25, // Proyecto
            'C' => 25, // Programa
            'D' => 25, // Fase
            'E' => 18, // Fecha Inicio (con hora)
            'F' => 18, // Fecha Fin (con hora)
            'G' => 15, // Duración (h/m)
            'H' => 18, // Estado
            'I' => 12, // Avance %
            'J' => 40, // Observaciones
        ];
    }

    public function title(): string
    {
        return 'Reporte General';
    }
}
