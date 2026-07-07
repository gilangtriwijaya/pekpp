<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Periode;
use App\Models\Upp;
use App\Models\UserUpp;
use App\Models\Pengumuman;

class DashboardAndPengumumanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // We want to test middleware, so re-enable it
        if (method_exists($this, 'withMiddleware')) {
            $this->withMiddleware();
        }
    }

    /** @test */
    public function dashboard_accessible_by_authenticated_user()
    {
        // Deactivate any existing active periode first
        Periode::where('is_aktif', 1)->update(['is_aktif' => false]);
        $periode = Periode::create([
            'kode' => 'P1',
            'nama' => 'P1',
            'tahun' => date('Y'),
            'tanggal_mulai' => date('Y-m-d'),
            'tanggal_selesai' => date('Y-m-d', strtotime('+30 days')),
            'status' => 'aktif',
            'is_aktif' => 1
        ]);

        $upp = Upp::create([
            'nama' => 'UPP 1',
            'status' => 'AKTIF',
            'aktif' => 1
        ]);

        $user = User::create([
            'nama' => 'User UPP',
            'email' => 'userupp@example.com',
            'aktif' => 1,
            'role_sso' => 'user'
        ]);

        UserUpp::create([
            'user_id' => $user->id,
            'upp_id' => $upp->id,
            'peran' => 'admin_upp',
            'aktif' => 1
        ]);

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertViewHas('isAdminUPP', true);
    }

    /** @test */
    public function internal_admin_can_access_announcement_crud()
    {
        $admin = User::create([
            'nama' => 'Internal Admin',
            'email' => 'admin@example.com',
            'aktif' => 1,
            'role_sso' => 'superadmin'
        ]);

        $response = $this->actingAs($admin)->get('/pengumuman');
        $response->assertStatus(200);
    }

    /** @test */
    public function upp_user_cannot_access_announcement_crud()
    {
        $upp = Upp::create([
            'nama' => 'UPP 1',
            'status' => 'AKTIF',
            'aktif' => 1
        ]);

        $user = User::create([
            'nama' => 'User UPP',
            'email' => 'userupp@example.com',
            'aktif' => 1,
            'role_sso' => 'user'
        ]);

        UserUpp::create([
            'user_id' => $user->id,
            'upp_id' => $upp->id,
            'peran' => 'admin_upp',
            'aktif' => 1
        ]);

        $response = $this->actingAs($user)->get('/pengumuman');
        $response->assertStatus(403);
    }
}
