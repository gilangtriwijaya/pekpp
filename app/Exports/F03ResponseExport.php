<?php

namespace App\Exports;

use App\Models\F03Token;
use App\Models\F03Aspek;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class F03ResponseExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $tokenId;
    protected $token;
    protected $indikators = [];

    public function __construct($tokenId)
    {
        $this->tokenId = $tokenId;
        $this->token = F03Token::with('periode')->findOrFail($tokenId);
        
        // Fetch all indicators for this period to use as columns
        $aspeks = F03Aspek::where('periode_id', $this->token->periode_id)
            ->with('indikator')
            ->orderBy('urutan')
            ->get();
            
        foreach ($aspeks as $aspek) {
            foreach ($aspek->indikator as $indikator) {
                $this->indikators[] = [
                    'id' => $indikator->id,
                    'aspek_nama' => $aspek->nama,
                    'pertanyaan' => $indikator->pertanyaan
                ];
            }
        }
    }

    public function collection()
    {
        return $this->token->pengisian()
            ->with(['demographic', 'jawaban'])
            ->orderBy('response_date', 'asc')
            ->get();
    }

    public function headings(): array
    {
        $headings = [
            'No',
            'Tanggal Pengisian',
            'Status',
            'Jenis Kelamin',
            'Umur',
            'Pendidikan Terakhir',
            'Pekerjaan Utama',
            'Skor Rata-Rata'
        ];

        foreach ($this->indikators as $indikator) {
            $headings[] = $indikator['aspek_nama'] . ' - ' . $indikator['pertanyaan'];
        }

        return $headings;
    }

    public function map($pengisian): array
    {
        static $rowNumber = 0;
        $rowNumber++;

        $gender = $pengisian->demographic->gender ?? '-';
        if ($gender !== '-') {
            $g = strtolower($gender);
            if (in_array($g, ['l', 'm', 'laki-laki', 'male', 'pria'])) {
                $gender = 'Pria';
            } elseif (in_array($g, ['p', 'f', 'perempuan', 'female', 'wanita'])) {
                $gender = 'Wanita';
            }
        }

        $row = [
            $rowNumber,
            $pengisian->response_date ? $pengisian->response_date->format('Y-m-d H:i:s') : '-',
            $pengisian->is_duplicate ? 'Duplikat' : 'Unik',
            $gender,
            $pengisian->demographic->age ?? '-',
            $pengisian->demographic->last_education ?? '-',
            $pengisian->demographic->occupation ?? '-',
            round($pengisian->getAverageScoreAttribute(), 2)
        ];

        // Map answers to the correct column
        $jawabanKeyed = $pengisian->jawaban->keyBy('f03_indikator_id');
        
        foreach ($this->indikators as $indikator) {
            if ($jawabanKeyed->has($indikator['id'])) {
                $jawaban = $jawabanKeyed->get($indikator['id']);
                $val = '';
                if ($jawaban->score !== null) {
                    $val = $jawaban->score;
                } elseif ($jawaban->response_text !== null) {
                    $decoded = json_decode($jawaban->response_text, true);
                    $val = is_array($decoded) ? implode(', ', $decoded) : $jawaban->response_text;
                }
                $row[] = $val;
            } else {
                $row[] = '-';
            }
        }

        return $row;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5'] // Indigo
                ]
            ],
        ];
    }
}
