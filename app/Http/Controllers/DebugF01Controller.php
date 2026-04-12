<?php

namespace App\Http\Controllers;

use App\Models\F01Pengisian;
use App\Models\F01Jawaban;
use App\Models\Pertanyaan;

class DebugF01Controller extends Controller
{
    /**
     * Debug endpoint to check jawaban in database for a specific pengisian
     */
    public function checkJawaban($pengisianId)
    {
        $pengisian = F01Pengisian::findOrFail($pengisianId);
        
        $jawaban = F01Jawaban::where('f01_pengisian_id', $pengisianId)
            ->with('pertanyaan')
            ->get();
        
        $yesnoQuestions = Pertanyaan::where('tipe_input', 'yesno')
            ->whereNull('opsi_jawaban')
            ->get();
        
        $result = [
            'pengisian_id' => $pengisianId,
            'total_jawaban_in_db' => $jawaban->count(),
            'yesno_questions_total' => $yesnoQuestions->count(),
            'jawaban_data' => $jawaban->map(function($j) {
                return [
                    'pertanyaan_id' => $j->pertanyaan_id,
                    'pertanyaan_tipe' => $j->pertanyaan->tipe_input ?? null,
                    'nilai_raw' => $j->getAttributes()['nilai'],
                    'nilai_casted' => $j->nilai,
                    'nilai_type' => gettype($j->nilai),
                    'is_yesno' => ($j->pertanyaan->tipe_input ?? null) === 'yesno'
                ];
            }),
            'yesno_questions' => $yesnoQuestions->map(function($q) {
                $jawaban = F01Jawaban::where('f01_pengisian_id', $pengisianId)
                    ->where('pertanyaan_id', $q->id)
                    ->first();
                
                return [
                    'id' => $q->id,
                    'label' => $q->label,
                    'opsi_jawaban' => $q->opsi_jawaban,
                    'jawaban_exists' => $jawaban ? true : false,
                    'jawaban_nilai' => $jawaban?->nilai,
                    'jawaban_nilai_raw' => $jawaban ? $jawaban->getAttributes()['nilai'] : null,
                ];
            })
        ];
        
        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }

    /**
     * View debug log file
     */
    public function viewLog()
    {
        $logFile = '/tmp/f01_debug.log';
        
        if (!file_exists($logFile)) {
            return response()->json([
                'success' => true,
                'log' => 'No log file yet. Make a save request to generate log.',
                'log_file' => $logFile
            ]);
        }
        
        $content = file_get_contents($logFile);
        $lines = explode("\n", $content);
        
        return response()->json([
            'success' => true,
            'log_file' => $logFile,
            'total_lines' => count($lines),
            'last_100_lines' => implode("\n", array_slice($lines, -100)),
            'full_log' => $content
        ]);
    }

    /**
     * Clear debug log
     */
    public function clearLog()
    {
        $logFile = '/tmp/f01_debug.log';
        if (file_exists($logFile)) {
            unlink($logFile);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Debug log cleared'
        ]);
    }
}
