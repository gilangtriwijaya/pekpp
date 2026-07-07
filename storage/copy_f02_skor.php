$sumberPeriode = \App\Models\Periode::where('nama', 'like', '%Pra PEKPPP 2026%')->first();
$targetPeriode = \App\Models\Periode::where('nama', 'like', '%PEKPPP 2026%')->where('id', '!=', $sumberPeriode->id)->first();

if (!$sumberPeriode || !$targetPeriode) {
    echo "Periode tidak ditemukan.\n";
    if ($sumberPeriode) echo "Sumber: " . $sumberPeriode->nama . "\n";
    if ($targetPeriode) echo "Target: " . $targetPeriode->nama . "\n";
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
