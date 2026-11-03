<?php

namespace Tests\Feature;

use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

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
        $auth = ['PHP_AUTH_USER' => 'admin', 'PHP_AUTH_PW' => 'secret'];
        $this->withServerVariables($auth)->post('/employees', [
            'first_name' => 'Çağrı', 'last_name' => 'Şen',
            'email' => 'CAGRI@example.com', 'department' => 'Üretim',
        ])->assertRedirect();

        $employee = Employee::firstOrFail();
        $this->assertSame('cagri@example.com', $employee->email);
        $this->withServerVariables($auth)->get('/employees?first_name=cagri')
            ->assertOk()->assertSee('Çağrı');

        $this->withServerVariables($auth)->put('/employees/'.$employee->id, [
            'first_name' => 'Çağrı', 'last_name' => 'Şen',
            'email' => 'cagri@example.com', 'department' => 'Kalite',
        ])->assertRedirect();
        $this->assertDatabaseHas('employees', ['id' => $employee->id, 'department' => 'Kalite']);

        $this->withServerVariables($auth)->delete('/employees/'.$employee->id)->assertRedirect();
        $this->assertSoftDeleted('employees', ['id' => $employee->id]);
        $this->withToken('test-token')->getJson('/api/v1/employees')
            ->assertOk()->assertJsonMissing(['id' => $employee->id]);
    }

    public function test_email_must_be_unique(): void
    {
        Employee::create(['first_name'=>'A','last_name'=>'B','email'=>'same@example.com','department'=>'IT']);
        $this->withServerVariables(['PHP_AUTH_USER'=>'admin','PHP_AUTH_PW'=>'secret'])
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
}
