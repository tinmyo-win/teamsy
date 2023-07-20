<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Database\Factories\TenantFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\File;
// use League\Flysystem\File;
use Tests\TestCase;

class TenantScopeTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function a_model_has_a_tenant_id_on_the_migration()
    {
        // Example usage:
        $migrationName = 'create_tests_table';
        $fileName = $this->generateMigrationFileName($migrationName);

        $this->artisan('make:model Test -m');

        $this->assertTrue(File::exists(database_path("/migrations/$fileName")));
        $this->assertStringContainsString('$table->unsignedBigInteger(\'tenant_id\')->index();',
            File::get(database_path("migrations/$fileName")));

        File::delete(database_path("migrations/$fileName"));
        File::delete(app_path('Models/Test.php'));
    }

    function generateMigrationFileName($migrationName)
    {
        $timestamp = date('Y_m_d_His');
        $migrationName = str_replace(' ', '_', $migrationName);
        $fileName = "{$timestamp}_{$migrationName}.php";

        return $fileName;
    }

    /** @test */
    public function a_user_can_only_see_users_in_same_tenant()
    {
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();

        $user1 = User::factory()->create([
            'tenant_id' => $tenant1,
        ]);

        User::factory(9)->create([
            'tenant_id' => $tenant1,
        ]);

        User::factory(10)->create([
            'tenant_id' => $tenant2,
        ]);

        // $user = $this->actingAs(User::where('id', $user1->id)->first());

        auth()->login(User::where('id', $user1->id)->first());

        $this->assertEquals(10, User::count());

    }
}
