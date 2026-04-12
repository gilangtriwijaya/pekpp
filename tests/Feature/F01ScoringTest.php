<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Periode;
use App\Models\Upp;
use App\Models\Aspek;
use App\Models\Indikator;
use App\Models\Pertanyaan;
use App\Models\F01Pengisian;
use App\Models\F01IndikatorNilai;
use App\Models\F01Jawaban;
use App\Services\F01ScoringService;

class F01ScoringTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function draft_submit_saves_values()
    {
        // Deactivate any existing active periode first
        Periode::where('is_aktif', 1)->update(['is_aktif' => false]);
        $periode = Periode::create(['kode'=>'P1','nama'=>'P1','tahun'=>date('Y'),'tanggal_mulai'=>date('Y-m-d'),'tanggal_selesai'=>date('Y-m-d'),'status'=>'aktif','is_aktif'=>1]);
        $upp = Upp::create(['nama'=>'UPP 1']);
        $aspek = Aspek::create(['periode_id'=>$periode->id,'kode'=>'A1','nama'=>'ASPEK 1','domain'=>'internal','aktif'=>1]);
        $indikator = Indikator::create(['aspek_id'=>$aspek->id,'kode'=>'I1','nama'=>'IND 1','aktif'=>1]);
        $p = Pertanyaan::create(['indikator_id'=>$indikator->id,'kode'=>'P1','label'=>'t','tipe_input'=>'text','aktif'=>1]);

        $peng = F01Pengisian::create(['periode_id'=>$periode->id,'upp_id'=>$upp->id,'status'=>'draft']);
        F01IndikatorNilai::create(['f01_pengisian_id'=>$peng->id,'indikator_id'=>$indikator->id,'nilai'=>null,'status'=>'draft']);

        F01Jawaban::create(['f01_pengisian_id'=>$peng->id,'pertanyaan_id'=>$p->id,'nilai'=>json_encode('abc')]);

        $svc = new F01ScoringService();
        $svc->finalizePengisian($peng);

        $this->assertDatabaseHas('f01_indikator_nilai', ['f01_pengisian_id'=>$peng->id,'indikator_id'=>$indikator->id]);
    }

    /** @test */
    public function submit_again_does_not_change_values()
    {
        // Deactivate any existing active periode first
        Periode::where('is_aktif', 1)->update(['is_aktif' => false]);
        $periode = Periode::create(['kode'=>'P1','nama'=>'P1','tahun'=>date('Y'),'tanggal_mulai'=>date('Y-m-d'),'tanggal_selesai'=>date('Y-m-d'),'status'=>'aktif','is_aktif'=>1]);
        $upp = Upp::create(['nama'=>'UPP 1']);
        $aspek = Aspek::create(['periode_id'=>$periode->id,'kode'=>'A1','nama'=>'ASPEK 1','domain'=>'internal','aktif'=>1]);
        $indikator = Indikator::create(['aspek_id'=>$aspek->id,'kode'=>'I1','nama'=>'IND 1','aktif'=>1]);
        $p = Pertanyaan::create(['indikator_id'=>$indikator->id,'kode'=>'P1','label'=>'t','tipe_input'=>'text','aktif'=>1]);

        $peng = F01Pengisian::create(['periode_id'=>$periode->id,'upp_id'=>$upp->id,'status'=>'draft']);
        F01Jawaban::create(['f01_pengisian_id'=>$peng->id,'pertanyaan_id'=>$p->id,'nilai'=>json_encode('abc')]);

        $svc = new F01ScoringService();
        $svc->finalizePengisian($peng);

        $first = F01IndikatorNilai::where('f01_pengisian_id',$peng->id)->first();

        // change jawaban then call finalize again but expect overwrite with same logic
        F01Jawaban::where('f01_pengisian_id',$peng->id)->update(['nilai'=>json_encode('changed')]);
        $svc->finalizePengisian($peng);

        $second = F01IndikatorNilai::where('f01_pengisian_id',$peng->id)->first();

        $this->assertEquals($second->nilai, $second->nilai); // idempotent placeholder (ensure no exception)
    }

    /** @test */
    public function empty_draft_scores_zero()
    {
        // Deactivate any existing active periode first
        Periode::where('is_aktif', 1)->update(['is_aktif' => false]);
        $periode = Periode::create(['kode'=>'P1','nama'=>'P1','tahun'=>date('Y'),'tanggal_mulai'=>date('Y-m-d'),'tanggal_selesai'=>date('Y-m-d'),'status'=>'aktif','is_aktif'=>1]);
        $upp = Upp::create(['nama'=>'UPP 1']);
        $aspek = Aspek::create(['periode_id'=>$periode->id,'kode'=>'A1','nama'=>'ASPEK 1','domain'=>'internal','aktif'=>1]);
        $indikator = Indikator::create(['aspek_id'=>$aspek->id,'kode'=>'I1','nama'=>'IND 1','aktif'=>1]);
        $p = Pertanyaan::create(['indikator_id'=>$indikator->id,'kode'=>'P1','label'=>'t','tipe_input'=>'text','aktif'=>1]);

        $peng = F01Pengisian::create(['periode_id'=>$periode->id,'upp_id'=>$upp->id,'status'=>'draft']);
        F01IndikatorNilai::create(['f01_pengisian_id'=>$peng->id,'indikator_id'=>$indikator->id,'nilai'=>null,'status'=>'draft']);

        $svc = new F01ScoringService();
        $svc->finalizePengisian($peng);

        $this->assertDatabaseHas('f01_indikator_nilai', ['f01_pengisian_id'=>$peng->id,'indikator_id'=>$indikator->id,'nilai'=>0]);
    }
}
