$sumberPeriode = \App\Models\Periode::find(1); // Pra PEKPPP Mandiri 2026
$targetPeriode = \App\Models\Periode::find(5); // PEKPPP Mandiri 2026

if (!$sumberPeriode || !$targetPeriode) {
    echo "Periode tidak ditemukan.\n";
    exit;
}

echo "Sumber: " . $sumberPeriode->nama . " (ID: " . $sumberPeriode->id . ")\n";
echo "Target: " . $targetPeriode->nama . " (ID: " . $targetPeriode->id . ")\n";

$sumberSkors = \App\Models\F02Skor::where('periode_id', $sumberPeriode->id)->get();
$count = 0;

foreach ($sumberSkors as $sumberSkor) {
    $sumberIndikator = \App\Models\Indikator::find($sumberSkor->indikator_id);
    if (!$sumberIndikator) continue;

    $targetIndikator = \App\Models\Indikator::whereHas('aspek', function($q) use ($targetPeriode) {
        $q->where('periode_id', $targetPeriode->id);
    })->where('kode', $sumberIndikator->kode)->first();

    if ($targetIndikator) {
        $existingSkor = \App\Models\F02Skor::where('indikator_id', $targetIndikator->id)
            ->where('periode_id', $targetPeriode->id)
            ->first();

        if (!$existingSkor) {
            $newSkor = $sumberSkor->replicate();
            $newSkor->indikator_id = $targetIndikator->id;
            $newSkor->periode_id = $targetPeriode->id;
            $newSkor->save();
            $count++;
        }
    }
}

echo "Berhasil menyalin $count skor F02.\n";
