<?php

namespace Tests\Feature;

use App\Http\Controllers\UserDocumentController;
use App\Models\User;
use App\Services\CompanyContext;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class DocumentAccessHistoryTest extends TestCase
{
    public function test_history_reads_new_access_events_and_keeps_document_tables_separate(): void
    {
        config(['database.connections.tenant' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);
        DB::purge('tenant');
        $schema = Schema::connection('tenant');
        $schema->create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('role');
            $table->unsignedBigInteger('company_id');
        });
        foreach (['employee_documents', 'hr_documents'] as $name) {
            $schema->create($name, function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
            });
            DB::connection('tenant')->table($name)->insert(['id' => 1, 'user_id' => 1]);
        }
        $schema->create('document_views', function (Blueprint $table) {
            $table->id();
            $table->string('document_table');
            $table->unsignedBigInteger('document_id');
            $table->unsignedBigInteger('viewed_by_user_id');
            $table->timestamp('viewed_at');
        });
        DB::connection('tenant')->table('users')->insert([
            'id' => 1, 'name' => 'HR Viewer', 'email' => 'viewer@example.test',
            'role' => 'hr', 'company_id' => 1,
        ]);
        $this->actingAs(User::findOrFail(1));
        $this->mock(CompanyContext::class)->shouldReceive('id')->andReturn(1);
        $controller = new UserDocumentController();
        $this->assertSame([], $controller->history('employee', 1)->getData(true));

        DB::connection('tenant')->table('document_views')->insert([
            'document_table' => 'employee_documents', 'document_id' => 1,
            'viewed_by_user_id' => 1, 'viewed_at' => '2026-10-09 10:00:00',
        ]);
        $response = $controller->history('employee', 1);
        $this->assertSame('HR Viewer', $response->getData(true)[0]['viewer']['name']);
        $this->assertSame('viewer@example.test', $response->getData(true)[0]['viewer']['email']);
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertSame([], $controller->history('hr', 1)->getData(true));

        DB::connection('tenant')->table('users')->where('id', 1)->update(['company_id' => 2]);
        try {
            $controller->history('employee', 1);
            $this->fail('Cross-company history must be denied.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
    }
}
