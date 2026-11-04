<?php

namespace Tests\Feature;

use App\Models\Training;
use App\Models\TrainingAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class TrainingPortalTest extends TestCase
{
    use RefreshDatabase;

    private array $auth = ['training_admin' => true];

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
        $this->withSession($this->auth)->post('/trainings', ['name'=>'İş Güvenliği','description'=>'Temel eğitim','duration_minutes'=>60])
            ->assertRedirect();
        $training = Training::firstOrFail();
        $this->withSession($this->auth)->put('/trainings/'.$training->id, ['name'=>'İSG','description'=>'Güncel','duration_minutes'=>75])
            ->assertRedirect();
        $this->assertDatabaseHas('trainings', ['name'=>'İSG','duration_minutes'=>75]);
        $this->withSession($this->auth)->delete('/trainings/'.$training->id)->assertRedirect();
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
        $this->withSession($this->auth)->post('/assignments', $payload)->assertRedirect()->assertSessionHas('success');
        $this->withSession($this->auth)->post('/assignments', $payload)->assertRedirect()->assertSessionHas('error');
        $this->assertSame(1, TrainingAssignment::count());

        $assignment = TrainingAssignment::firstOrFail();
        $this->withSession($this->auth)->patch('/assignments/'.$assignment->id.'/complete')->assertRedirect();
        $this->assertNotNull($assignment->fresh()->completed_at);
    }

    public function test_assignment_screen_handles_unavailable_personnel_service(): void
    {
        Http::fake(fn () => Http::failedConnection());
        $this->withSession($this->auth)->get('/assignments')->assertOk()->assertSee('Personel servisi kullanılamıyor');
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
        $this->withSession($this->auth)->get('/assignments')->assertOk()->assertSee('Ad 1')->assertSee('Ad 2');
        Http::assertSentCount(2);
    }

    public function test_management_routes_require_authentication(): void
    {
        $this->get('/')->assertRedirect('/login');
        $this->post('/trainings', [])->assertRedirect('/login');
    }

    public function test_login_logout_and_real_xlsx_export(): void
    {
        $this->get('/login')->assertOk()->assertSee('Gelişime');
        $this->post('/login',['username'=>'admin','password'=>'wrong'])->assertSessionHasErrors('username');
        $this->post('/login',['username'=>'admin','password'=>'secret'])->assertRedirect('/')->assertSessionHas('training_admin',true);
        Training::create(['name'=>'Excel Eğitimi','duration_minutes'=>45]);
        $response=$this->withSession($this->auth)->get('/trainings-export?search=Excel');
        $response->assertOk()->assertHeader('content-type','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $path=$response->getFile()->getPathname();
        $this->assertStringStartsWith(str_replace('\\','/',storage_path('app/tmp/exports')),str_replace('\\','/',$path));
        $this->assertStringStartsWith('PK',$response->getFile()->getContent());
        $book=IOFactory::load($path);$sheet=$book->getSheetByName('Veriler');$this->assertNotNull($sheet);
        $this->assertSame('Eğitim adı',$sheet->getCell('B1')->getValue());$this->assertSame('Excel Eğitimi',$sheet->getCell('B2')->getValue());
        $this->assertSame(DataType::TYPE_NUMERIC,$sheet->getCell('D2')->getDataType());$this->assertSame(DataType::TYPE_NUMERIC,$sheet->getCell('E2')->getDataType());$book->disconnectWorksheets();
        $serverHash=hash_file('sha256',$path);ob_start();$response->sendContent();$body=ob_get_clean();$this->assertStringStartsWith("PK\x03\x04",$body);$this->assertSame($serverHash,hash('sha256',$body));$this->assertFileDoesNotExist($path);
        $this->withSession($this->auth)->post('/logout')->assertRedirect('/login');
    }

    public function test_exports_are_protected_and_ignore_crud_query_fields(): void
    {
        $this->get('/trainings-export')->assertRedirect('/login');
        Training::create(['name'=>'Türkçe Eğitim','duration_minutes'=>30]);
        $response=$this->withSession($this->auth)->get('/trainings-export?_token=secret&_method=PATCH&scope=filtered')->assertOk();
        $path=$response->getFile()->getPathname(); ob_start(); $response->sendContent(); ob_end_clean(); $this->assertFileDoesNotExist($path);
    }

    public function test_page_selected_empty_and_assignment_export_scopes(): void
    {
        $first=Training::create(['name'=>'Birinci','duration_minutes'=>30]);Training::create(['name'=>'İkinci','duration_minutes'=>40]);
        foreach(['/trainings-export?scope=page&per_page=1&page=1','/trainings-export?scope=selected&ids='.$first->id,'/trainings-export?scope=filtered&search=bulunamaz'] as $url){$response=$this->withSession($this->auth)->get($url)->assertOk();$path=$response->getFile()->getPathname();$book=IOFactory::load($path);$this->assertSame('Veriler',$book->getActiveSheet()->getTitle());$book->disconnectWorksheets();ob_start();$response->sendContent();ob_end_clean();$this->assertFileDoesNotExist($path);}
        TrainingAssignment::create(['training_id'=>$first->id,'employee_id'=>9,'employee_name'=>'Çağrı Şen','employee_email'=>'cagri@example.com','employee_department'=>'Üretim','assigned_at'=>now()]);
        $response=$this->withSession($this->auth)->get('/assignments-export?scope=filtered&status=pending')->assertOk();$path=$response->getFile()->getPathname();$book=IOFactory::load($path);$sheet=$book->getActiveSheet();$this->assertSame('Çağrı Şen',$sheet->getCell('A2')->getValue());$this->assertSame(DataType::TYPE_NUMERIC,$sheet->getCell('D2')->getDataType());$book->disconnectWorksheets();ob_start();$response->sendContent();ob_end_clean();$this->assertFileDoesNotExist($path);
    }
}
