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
                $item['fecha_liberacion'],
                $item['fecha_inicio'],
                $item['fecha_fin'],
                $item['tiempo_espera_texto'],
                $item['tiempo_reaccion_texto'],
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
            'Fecha Liberación',
            'Fecha Inicio',
            'Fecha Fin',
            'T. Espera (h/m)',
            'T. Reacción (h/m)',
            'T. Ejecución (h/m)',
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
            'D' => 20, // Fase
            'E' => 18, // Fecha Liberación (con hora)
            'F' => 18, // Fecha Inicio (con hora)
            'G' => 18, // Fecha Fin (con hora)
            'H' => 15, // T. Espera (h/m)
            'I' => 15, // T. Reacción (h/m)
            'J' => 15, // T. Ejecución (h/m)
            'K' => 15, // Estado
            'L' => 12, // Avance %
            'M' => 40, // Observaciones
        ];
    }

    public function title(): string
    {
        return 'Reporte General';
    }
}
