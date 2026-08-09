<?php

use App\Models\User;
use App\Services\MySqlDatabaseBackup;
use Carbon\Carbon;

test('guests cannot download a database backup', function () {
    $this->post(route('admin.database-backup'))
        ->assertRedirect(route('login'));
});

test('non admin users cannot download a database backup', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)
        ->post(route('admin.database-backup'))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('admins can download a generated database backup', function () {
    $user = User::factory()->create(['is_admin' => true]);
    $dumpPath = tempnam(sys_get_temp_dir(), 'inaya-backup-test-');

    file_put_contents($dumpPath, '-- complete database dump');

    $backup = Mockery::mock(MySqlDatabaseBackup::class);
    $backup->shouldReceive('create')->once()->andReturn($dumpPath);
    $this->app->instance(MySqlDatabaseBackup::class, $backup);

    config(['app.name' => 'Inaya Creation']);
    $this->travelTo(Carbon::parse('2026-08-09 14:30:15'));

    try {
        $this->actingAs($user)
            ->post(route('admin.database-backup'))
            ->assertOk()
            ->assertDownload('inaya-creation-database-2026-08-09_14-30-15.sql')
            ->assertHeader('Content-Type', 'application/sql');
    } finally {
        $this->travelBack();

        if (is_file($dumpPath)) {
            unlink($dumpPath);
        }
    }
});

test('database backup endpoint does not accept get requests', function () {
    $user = User::factory()->create(['is_admin' => true]);

    $this->actingAs($user)
        ->get('/admin/database-backup')
        ->assertMethodNotAllowed();
});

test('database backup button is only shown to admin users', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($admin)
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertSee('Download Backup')
        ->assertSee(route('admin.database-backup'), escape: false);

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertDontSee('Download Backup');
});
