<?php

namespace Tests\Feature;

use App\Models\Training;
use App\Models\TrainingAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TrainingPortalTest extends TestCase
{
    use RefreshDatabase;

    private array $auth = ['PHP_AUTH_USER' => 'admin', 'PHP_AUTH_PW' => 'secret'];

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'training.admin_username' => 'admin',
            'training.admin_password_hash' => password_hash('secret', PASSWORD_DEFAULT),
        ]);
    }

    public function test_training_can_be_created_updated_and_deleted(): void
    {
        $this->withServerVariables($this->auth)->post('/trainings', ['name'=>'İş Güvenliği','description'=>'Temel eğitim','duration_minutes'=>60])
            ->assertRedirect();
        $training = Training::firstOrFail();
        $this->withServerVariables($this->auth)->put('/trainings/'.$training->id, ['name'=>'İSG','description'=>'Güncel','duration_minutes'=>75])
            ->assertRedirect();
        $this->assertDatabaseHas('trainings', ['name'=>'İSG','duration_minutes'=>75]);
        $this->withServerVariables($this->auth)->delete('/trainings/'.$training->id)->assertRedirect();
        $this->assertDatabaseMissing('trainings', ['id'=>$training->id]);
    }

    public function test_employee_can_receive_training_only_once_and_complete_it(): void
    {
        $training = Training::create(['name'=>'Laravel','duration_minutes'=>90]);
        Http::fake([
            '*/employees/42' => Http::response(['data'=>[
                'id'=>42,'first_name'=>'Ayşe','last_name'=>'Yılmaz',
                'email'=>'ayse@example.com','department'=>'IT',
            ]]),
        ]);

        $payload = ['training_id'=>$training->id,'employee_id'=>42];
        $this->withServerVariables($this->auth)->post('/assignments', $payload)->assertRedirect()->assertSessionHas('success');
        $this->withServerVariables($this->auth)->post('/assignments', $payload)->assertRedirect()->assertSessionHas('error');
        $this->assertSame(1, TrainingAssignment::count());

        $assignment = TrainingAssignment::firstOrFail();
        $this->withServerVariables($this->auth)->patch('/assignments/'.$assignment->id.'/complete')->assertRedirect();
        $this->assertNotNull($assignment->fresh()->completed_at);
    }

    public function test_assignment_screen_handles_unavailable_personnel_service(): void
    {
        Http::fake(fn () => Http::failedConnection());
        $this->withServerVariables($this->auth)->get('/assignments')->assertOk()->assertSee('Personel servisi kullanılamıyor');
    }

    public function test_personnel_client_follows_all_api_pages(): void
    {
        Training::create(['name'=>'Laravel','duration_minutes'=>90]);
        Http::fake(function ($request) {
            $page = (int) $request->data()['page'];
            return Http::response([
                'data'=>[['id'=>$page,'first_name'=>'Ad','last_name'=>(string)$page,'email'=>'a'.$page.'@x.test','department'=>'IT']],
                'last_page'=>2,
            ]);
        });
        $this->withServerVariables($this->auth)->get('/assignments')->assertOk()->assertSee('Ad 1')->assertSee('Ad 2');
        Http::assertSentCount(2);
    }

    public function test_management_routes_require_authentication(): void
    {
        $this->get('/')->assertUnauthorized()->assertHeader('WWW-Authenticate');
        $this->post('/trainings', [])->assertUnauthorized();
    }
}
