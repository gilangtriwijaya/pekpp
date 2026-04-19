<?php

namespace Tests\Feature;

use App\Models\Aspek;
use App\Models\Indikator;
use App\Models\Pertanyaan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConditionalQuestionsTest extends TestCase
{
    use RefreshDatabase;

    protected $aspek;
    protected $indikator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->aspek = Aspek::factory()->create();
        $this->indikator = Indikator::factory()->create(['aspek_id' => $this->aspek->id]);
    }

    /** @test */
    public function can_create_pertanyaan_with_conditional_questions()
    {
        $data = [
            'indikator_id' => $this->indikator->id,
            'label' => 'Apakah Anda setuju?',
            'tipe_input' => 'yesno',
            'kode' => 'Q1',
            'urutan' => 1,
            'aktif' => true,
            'wajib' => false,
            'conditional_label' => ['Pertanyaan A', 'Pertanyaan B'],
            'conditional_tipe' => ['text', 'textarea'],
            'conditional_show_when' => ['ya', 'tidak'],
        ];

        $response = $this->post(route('admin.f01.pertanyaan.store'), $data);

        $this->assertEquals(1, Pertanyaan::where('label', 'Apakah Anda setuju?')->count());

        $parent = Pertanyaan::where('label', 'Apakah Anda setuju?')->first();
        $this->assertEquals(2, $parent->conditionalQuestions()->count());

        $this->assertEquals('Pertanyaan A', $parent->conditionalQuestions()->first()->label);
        $this->assertEquals('ya', $parent->conditionalQuestions()->first()->show_when);
    }

    /** @test */
    public function conditional_questions_inherit_indikator()
    {
        $parent = Pertanyaan::create([
            'indikator_id' => $this->indikator->id,
            'label' => 'Parent Question',
            'tipe_input' => 'yesno',
            'urutan' => 1,
            'aktif' => true,
        ]);

        $child = Pertanyaan::create([
            'parent_pertanyaan_id' => $parent->id,
            'indikator_id' => $this->indikator->id,
            'label' => 'Child Question',
            'tipe_input' => 'text',
            'show_when' => 'ya',
            'urutan' => 1,
            'aktif' => true,
        ]);

        $this->assertEquals($parent->indikator_id, $child->indikator_id);
        $this->assertEquals($parent->id, $child->parent_pertanyaan_id);
    }

    /** @test */
    public function deleting_parent_deletes_conditional_questions()
    {
        $parent = Pertanyaan::create([
            'indikator_id' => $this->indikator->id,
            'label' => 'Parent',
            'tipe_input' => 'yesno',
            'urutan' => 1,
            'aktif' => true,
        ]);

        Pertanyaan::create([
            'parent_pertanyaan_id' => $parent->id,
            'indikator_id' => $this->indikator->id,
            'label' => 'Child',
            'tipe_input' => 'text',
            'show_when' => 'ya',
            'urutan' => 1,
            'aktif' => true,
        ]);

        $this->assertEquals(1, $parent->conditionalQuestions()->count());

        $parent->delete();

        // After delete, children are soft-deleted so not counted when excluding deleted_at
        $this->assertEquals(0, Pertanyaan::where('parent_pertanyaan_id', $parent->id)->whereNull('deleted_at')->count());
    }

    /** @test */
    public function can_load_conditional_questions_on_show()
    {
        $parent = Pertanyaan::create([
            'indikator_id' => $this->indikator->id,
            'label' => 'Parent',
            'tipe_input' => 'yesno',
            'urutan' => 1,
            'aktif' => true,
        ]);

        $child1 = Pertanyaan::create([
            'parent_pertanyaan_id' => $parent->id,
            'indikator_id' => $this->indikator->id,
            'label' => 'Child 1',
            'tipe_input' => 'text',
            'show_when' => 'ya',
            'urutan' => 1,
            'aktif' => true,
        ]);

        $child2 = Pertanyaan::create([
            'parent_pertanyaan_id' => $parent->id,
            'indikator_id' => $this->indikator->id,
            'label' => 'Child 2',
            'tipe_input' => 'textarea',
            'show_when' => 'tidak',
            'urutan' => 2,
            'aktif' => true,
        ]);

        $response = $this->get(route('admin.f01.pertanyaan.show', $parent->id), [
            'Accept' => 'application/json',
        ]);

        // Verify response has children in conditional_questions relation
        $response->assertJsonPath('data.conditional_questions.0.label', 'Child 1');
        $response->assertJsonPath('data.conditional_questions.1.label', 'Child 2');
    }
}
