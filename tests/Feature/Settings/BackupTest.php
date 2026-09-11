<?php

use App\Enums\BackupStatus;
use App\Enums\UserRole;
use App\Jobs\GenerateDatabaseBackup;
use App\Models\Backup;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

test('administrator can view backups settings', function () {
    $admin = User::factory()->administrator()->create();

    $this->actingAs($admin)
        ->get(route('settings.backups.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('settings/backups/index')
            ->where('has_in_progress', false)
            ->has('backups.data', 0));
});

test('non administrator cannot view backups settings', function () {
    $user = User::factory()->create(['role' => UserRole::Manager]);

    $this->actingAs($user)
        ->get(route('settings.backups.index'))
        ->assertForbidden();
});

test('administrator can queue a database backup', function () {
    Queue::fake();

    $admin = User::factory()->administrator()->create();

    $this->actingAs($admin)
        ->post(route('settings.backups.store'))
        ->assertRedirect(route('settings.backups.index'));

    $backup = Backup::query()->first();

    expect($backup)->not->toBeNull()
        ->and($backup->status)->toBe(BackupStatus::Pending)
        ->and($backup->created_by)->toBe($admin->id);

    Queue::assertPushed(GenerateDatabaseBackup::class, function (GenerateDatabaseBackup $job) use ($backup): bool {
        return $job->backup->is($backup);
    });
});

test('generating a backup creates a downloadable zip', function () {
    Storage::fake('local');

    $admin = User::factory()->administrator()->create();

    $this->actingAs($admin)
        ->post(route('settings.backups.store'))
        ->assertRedirect(route('settings.backups.index'));

    $backup = Backup::query()->first();

    expect($backup)->not->toBeNull()
        ->and($backup->status)->toBe(BackupStatus::Completed)
        ->and($backup->filename)->toEndWith('.zip')
        ->and($backup->path)->not->toBeNull()
        ->and($backup->size)->toBeGreaterThan(0);

    Storage::disk('local')->assertExists($backup->path);

    $zip = new ZipArchive;
    $opened = $zip->open(Storage::disk('local')->path($backup->path));

    expect($opened)->toBeTrue()
        ->and($zip->locateName('database.sql'))->not->toBeFalse();

    $sql = $zip->getFromName('database.sql');
    $zip->close();

    expect($sql)->toBeString()
        ->and($sql)->toContain('CREATE TABLE')
        ->and($sql)->not->toContain('__Auth');

    $this->actingAs($admin)
        ->get(route('settings.backups.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('has_in_progress', false)
            ->has('backups.data', 1)
            ->where('backups.data.0.status', 'completed')
            ->where('backups.data.0.can_download', true)
            ->where('backups.data.0.filename', $backup->filename));

    $this->actingAs($admin)
        ->get(route('settings.backups.download', $backup))
        ->assertDownload($backup->filename);
});

test('a second backup cannot start while one is in progress', function () {
    Queue::fake();

    $admin = User::factory()->administrator()->create();
    Backup::factory()->processing()->create(['created_by' => $admin->id]);

    $this->actingAs($admin)
        ->post(route('settings.backups.store'))
        ->assertRedirect(route('settings.backups.index'));

    expect(Backup::query()->count())->toBe(1);
    Queue::assertNothingPushed();
});

test('download rejects paths outside the backup directory', function () {
    Storage::fake('local');
    Storage::disk('local')->put('secret.txt', 'nope');

    $admin = User::factory()->administrator()->create();
    $backup = Backup::factory()->completed()->create([
        'created_by' => $admin->id,
        'path' => 'backups/../secret.txt',
        'filename' => 'secret.txt',
    ]);

    $this->actingAs($admin)
        ->get(route('settings.backups.download', $backup))
        ->assertNotFound();
});

test('pending backups cannot be downloaded or deleted', function () {
    $admin = User::factory()->administrator()->create();
    $backup = Backup::factory()->create(['created_by' => $admin->id]);

    $this->actingAs($admin)
        ->get(route('settings.backups.download', $backup))
        ->assertNotFound();

    $this->actingAs($admin)
        ->delete(route('settings.backups.destroy', $backup))
        ->assertForbidden();

    $this->assertModelExists($backup);
});

test('administrator can delete a completed backup and its file', function () {
    Storage::fake('local');

    $admin = User::factory()->administrator()->create();
    $backup = Backup::factory()->completed()->create(['created_by' => $admin->id]);

    Storage::disk('local')->put($backup->path, 'zip-bytes');

    $this->actingAs($admin)
        ->delete(route('settings.backups.destroy', $backup))
        ->assertRedirect(route('settings.backups.index'));

    $this->assertModelMissing($backup);
    Storage::disk('local')->assertMissing($backup->path);
});

test('non administrator cannot generate a backup', function () {
    Queue::fake();

    $user = User::factory()->create(['role' => UserRole::Manager]);

    $this->actingAs($user)
        ->post(route('settings.backups.store'))
        ->assertForbidden();

    Queue::assertNothingPushed();
    expect(Backup::query()->count())->toBe(0);
});

test('settings hub includes backups card for administrators', function () {
    $admin = User::factory()->administrator()->create();

    $this->actingAs($admin)
        ->get(route('settings.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('settings/index')
            ->where('cards', fn ($cards) => collect($cards)->contains('title', 'النسخ الاحتياطي')));
});
