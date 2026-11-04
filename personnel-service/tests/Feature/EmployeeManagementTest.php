<?php

namespace Tests\Feature;

use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class EmployeeManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'personnel.admin_username' => 'admin',
            'personnel.admin_password_hash' => password_hash('secret', PASSWORD_DEFAULT),
            'personnel.api_token' => 'test-token',
        ]);
    }

    public function test_admin_can_create_update_filter_and_soft_delete_employee(): void
    {
        $auth = ['personnel_admin' => true];
        $this->withSession($auth)->post('/employees', [
            'first_name' => 'Çağrı', 'last_name' => 'Şen',
            'email' => 'CAGRI@example.com', 'department' => 'Üretim',
        ])->assertRedirect();

        $employee = Employee::firstOrFail();
        $this->assertSame('cagri@example.com', $employee->email);
        $this->withSession($auth)->get('/employees?first_name=cagri')
            ->assertOk()->assertSee('Çağrı');

        $this->withSession($auth)->put('/employees/'.$employee->id, [
            'first_name' => 'Çağrı', 'last_name' => 'Şen',
            'email' => 'cagri@example.com', 'department' => 'Kalite',
        ])->assertRedirect();
        $this->assertDatabaseHas('employees', ['id' => $employee->id, 'department' => 'Kalite']);

        $this->withSession($auth)->delete('/employees/'.$employee->id)->assertRedirect();
        $this->assertSoftDeleted('employees', ['id' => $employee->id]);
        $this->withToken('test-token')->getJson('/api/v1/employees')
            ->assertOk()->assertJsonMissing(['id' => $employee->id]);
    }

    public function test_email_must_be_unique(): void
    {
        Employee::create(['first_name'=>'A','last_name'=>'B','email'=>'same@example.com','department'=>'IT']);
        $this->withSession(['personnel_admin'=>true])
            ->from('/employees')->post('/employees', [
                'first_name'=>'C','last_name'=>'D','email'=>'same@example.com','department'=>'IT',
            ])->assertSessionHasErrors('email');
    }

    public function test_api_requires_valid_token(): void
    {
        $this->getJson('/api/v1/employees')->assertUnauthorized()->assertJsonPath('error_code', 'UNAUTHORIZED');
        $this->withToken('wrong')->getJson('/api/v1/employees')->assertUnauthorized();
        $this->withToken('test-token')->getJson('/api/v1/employees')->assertOk();
    }

    public function test_login_logout_and_protected_routes(): void
    {
        $this->get('/employees')->assertRedirect('/login');
        $this->get('/login')->assertOk()->assertSee('Personel Yönetim Sistemi');
        $this->post('/login',['username'=>'admin','password'=>'wrong'])->assertSessionHasErrors('username');
        $this->post('/login',['username'=>'admin','password'=>'secret'])->assertRedirect('/employees')->assertSessionHas('personnel_admin',true);
        $this->withSession(['personnel_admin'=>true])->post('/logout')->assertRedirect('/login');
    }

    public function test_filtered_real_xlsx_export(): void
    {
        Employee::create(['first_name'=>'Gökçe','last_name'=>'Şahin','email'=>'gokce@example.com','department'=>'Kalite']);
        $response=$this->withSession(['personnel_admin'=>true])->get('/employees-export?search=gokce');
        $response->assertOk()->assertHeader('content-type','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $path=$response->getFile()->getPathname();
        $this->assertStringStartsWith(str_replace('\\','/',storage_path('app/tmp/exports')),str_replace('\\','/',$path));
        $this->assertStringStartsWith('PK', $response->getFile()->getContent());
        $book=IOFactory::load($path);$sheet=$book->getSheetByName('Veriler');
        $this->assertNotNull($sheet);$this->assertSame('Ad',$sheet->getCell('B1')->getValue());
        $this->assertSame('Gökçe',$sheet->getCell('B2')->getValue());
        $this->assertSame(DataType::TYPE_NUMERIC,$sheet->getCell('A2')->getDataType());
        $this->assertSame(DataType::TYPE_NUMERIC,$sheet->getCell('F2')->getDataType());
        $this->assertSame(2,$sheet->getHighestDataRow());$book->disconnectWorksheets();
        $serverHash=hash_file('sha256',$path);ob_start();$response->sendContent();$body=ob_get_clean();
        $this->assertStringStartsWith("PK\x03\x04",$body);$this->assertSame($serverHash,hash('sha256',$body));
        $this->assertFileDoesNotExist($path);
    }

    public function test_export_is_protected_and_ignores_crud_query_fields(): void
    {
        $this->get('/employees-export')->assertRedirect('/login');
        Employee::create(['first_name'=>'Ayşe','last_name'=>'Yılmaz','email'=>'ayse@example.com','department'=>'IT']);
        $response=$this->withSession(['personnel_admin'=>true])->get('/employees-export?_token=secret&_method=PATCH&scope=filtered')->assertOk();
        $path=$response->getFile()->getPathname(); ob_start(); $response->sendContent(); ob_end_clean(); $this->assertFileDoesNotExist($path);
    }

    public function test_page_selected_and_empty_export_scopes(): void
    {
        $first=Employee::create(['first_name'=>'Ali','last_name'=>'Bir','email'=>'ali1@example.com','department'=>'IT']);
        Employee::create(['first_name'=>'Veli','last_name'=>'İki','email'=>'veli2@example.com','department'=>'IT']);
        foreach(['/employees-export?scope=page&per_page=1&page=1','/employees-export?scope=selected&ids='.$first->id,'/employees-export?scope=filtered&search=bulunamaz'] as $url){
            $response=$this->withSession(['personnel_admin'=>true])->get($url)->assertOk(); $path=$response->getFile()->getPathname();
            $book=IOFactory::load($path);$this->assertSame('Veriler',$book->getActiveSheet()->getTitle());$book->disconnectWorksheets();ob_start();$response->sendContent();ob_end_clean();$this->assertFileDoesNotExist($path);
        }
    }

    public function test_export_keeps_formula_like_user_data_as_text(): void
    {
        Employee::create(['first_name'=>'=CMD','last_name'=>'+Risk','email'=>'safe@example.com','department'=>'@Test']);
        $response=$this->withSession(['personnel_admin'=>true])->get('/employees-export');$path=$response->getFile()->getPathname();$book=IOFactory::load($path);$sheet=$book->getActiveSheet();
        $this->assertSame(DataType::TYPE_STRING,$sheet->getCell('B2')->getDataType());$this->assertSame("'=CMD",$sheet->getCell('B2')->getValue());$book->disconnectWorksheets();ob_start();$response->sendContent();ob_end_clean();
    }
}
