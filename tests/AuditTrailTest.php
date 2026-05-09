<?php

namespace Jeevanjoshi\LaravelAuditTrail\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Jeevanjoshi\LaravelAuditTrail\AuditTrailServiceProvider;
use Jeevanjoshi\LaravelAuditTrail\Models\Audit;
use Jeevanjoshi\LaravelAuditTrail\Traits\HasAuditTrail;
use Orchestra\Testbench\TestCase;

class AuditTrailTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Load our package
     */
    protected function getPackageProviders($app): array
    {
        return [AuditTrailServiceProvider::class];
    }

    /**
     * Set up the database for every test
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create audit_trails table
        $this->artisan('migrate', [
            '--path'     => __DIR__ . '/../src/database/migrations',
            '--realpath' => true,
        ]);
        // Create a dummy users table for testing
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('password')->nullable();
            $table->timestamps();
        });

        // Create a dummy posts table for testing
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body')->nullable();
            $table->timestamps();
        });
    }

    /** @test */
    public function it_logs_when_a_model_is_created(): void
    {
        $user = TestUser::create([
            'name'  => 'Jeevan',
            'email' => 'jeevan@gmail.com',
        ]);

        $this->assertDatabaseHas('audit_trails', [
            'model_type' => TestUser::class,
            'model_id'   => $user->id,
            'action'     => 'created',
        ]);
    }

    /** @test */
    public function it_logs_when_a_model_is_updated(): void
    {
        $user = TestUser::create([
            'name'  => 'Jeevan',
            'email' => 'jeevan@gmail.com',
        ]);

        $user->update(['name' => 'Jeevan Joshi']);

        $audit = Audit::where('action', 'updated')->first();

        $this->assertNotNull($audit);
        $this->assertEquals(['name' => 'Jeevan'], $audit->old_values);
        $this->assertEquals(['name' => 'Jeevan Joshi'], $audit->new_values);
    }

    /** @test */
    public function it_logs_when_a_model_is_deleted(): void
    {
        $user = TestUser::create([
            'name'  => 'Jeevan',
            'email' => 'jeevan@gmail.com',
        ]);

        $user->delete();

        $this->assertDatabaseHas('audit_trails', [
            'model_type' => TestUser::class,
            'model_id'   => $user->id,
            'action'     => 'deleted',
        ]);
    }

    /** @test */
    public function it_does_not_log_excluded_fields(): void
    {
        $user = TestUser::create([
            'name'     => 'Jeevan',
            'email'    => 'jeevan@gmail.com',
            'password' => 'secret123',
        ]);

        $audit = Audit::where('action', 'created')->first();

        $this->assertArrayNotHasKey('password', $audit->new_values);
    }

    /** @test */
    public function it_can_revert_a_model_to_previous_state(): void
    {
        $user = TestUser::create([
            'name'  => 'Jeevan',
            'email' => 'jeevan@gmail.com',
        ]);

        $user->update(['name' => 'Jeevan Joshi']);

        $audit = Audit::where('action', 'updated')->first();

        $user->revertTo($audit->id);

        $this->assertEquals('Jeevan', $user->fresh()->name);
    }

    /** @test */
    public function it_does_not_log_when_reverting(): void
    {
        $user = TestUser::create([
            'name'  => 'Jeevan',
            'email' => 'jeevan@gmail.com',
        ]);

        $user->update(['name' => 'Jeevan Joshi']);

        $countBefore = Audit::count();

        $audit = Audit::where('action', 'updated')->first();
        $user->revertTo($audit->id);

        $countAfter = Audit::count();

        $this->assertEquals($countBefore, $countAfter);
    }

    /** @test */
    public function it_can_query_audits_on_model(): void
    {
        $user = TestUser::create([
            'name'  => 'Jeevan',
            'email' => 'jeevan@gmail.com',
        ]);

        $user->update(['name' => 'Jeevan Joshi']);
        $user->update(['email' => 'new@gmail.com']);

        $this->assertEquals(3, $user->audits()->count());
    }

    /** @test */
    public function it_works_on_multiple_models(): void
    {
        TestUser::create([
            'name'  => 'Jeevan',
            'email' => 'jeevan@gmail.com',
        ]);

        TestPost::create([
            'title' => 'My first post',
            'body'  => 'Hello world',
        ]);

        $this->assertEquals(2, Audit::count());
        $this->assertEquals(1, Audit::forModel(TestUser::class)->count());
        $this->assertEquals(1, Audit::forModel(TestPost::class)->count());
    }

    /** @test */
    public function it_does_not_log_when_nothing_changed(): void
    {
        $user = TestUser::create([
            'name'  => 'Jeevan',
            'email' => 'jeevan@gmail.com',
        ]);

        $countBefore = Audit::count();

        // Save without changing anything
        $user->save();

        $countAfter = Audit::count();

        $this->assertEquals($countBefore, $countAfter);
    }

    /** @test */
    public function it_can_use_withoutaudit(): void
    {
        $user = TestUser::create([
            'name'  => 'Jeevan',
            'email' => 'jeevan@gmail.com',
        ]);

        $countBefore = Audit::count();

        $user->withoutAudit(function () use ($user) {
            $user->update(['name' => 'Silent Update']);
        });

        $countAfter = Audit::count();

        $this->assertEquals('Silent Update', $user->fresh()->name);
        $this->assertEquals($countBefore, $countAfter);
    }

    /** @test */
    public function it_can_scope_by_action(): void
    {
        $user = TestUser::create([
            'name'  => 'Jeevan',
            'email' => 'jeevan@gmail.com',
        ]);

        $user->update(['name' => 'Jeevan Joshi']);
        $user->delete();

        $this->assertEquals(1, Audit::ofAction('created')->count());
        $this->assertEquals(1, Audit::ofAction('updated')->count());
        $this->assertEquals(1, Audit::ofAction('deleted')->count());
    }
}

/**
 * Dummy User model for testing
 */
class TestUser extends Model
{
    use HasAuditTrail;

    protected $table = 'users';
    protected $guarded = [];

    protected array $auditExclude = ['password'];
}

/**
 * Dummy Post model for testing
 */
class TestPost extends Model
{
    use HasAuditTrail;

    protected $table = 'posts';
    protected $guarded = [];
}
