<?php

namespace Freesgen\Atmosphere\Domains\Admin\Services;

use Exception;
use App\Jobs\GenerateBackup;
use App\Jobs\SendBackupEmail;
use App\Mail\BackupGenerated;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;

class BackupService {
  public function list() {
    $backupDir = config('app.name');
    return collect(array_diff(scandir(storage_path("app/$backupDir")), ['.', '..']))->values();
  }

  public function createFile($fileName) {
    $message  = "This is the backup for icloan";
    $from = config('atmosphere.backup.email') ?? config('atmosphere.superadmin.email');
    $to = $from;
    $backupDir = config('app.name');

    try {
      Mail::to($to)->send(new BackupGenerated(storage_path("app/$backupDir/$fileName"), $fileName, auth()->user()?->name ?? "Admin"));

      activity()
      ->withProperties(['file' => $fileName])
      ->log("System sent the backup $to with the file $fileName");
    } catch (Exception $e) {
      activity()
      ->withProperties([
        'file' => $fileName,
        'error' => $e->getMessage()
        ])
      ->log("System failed to send  $fileName");
    }
  }

  public function sendFileViaEmail($fileName) {
    $to = config('atmosphere.backup.email');

    try {
      Mail::to($to)->send(new BackupGenerated($this->getBackupFile($fileName), $fileName, auth()->user()?->name ?? "Admin"));

      activity()
      ->withProperties(['file' => $fileName])
      ->log("System sent the backup $to with the file $fileName");
    } catch (Exception $e) {
      activity()
      ->withProperties([
        'file' => $fileName,
        'error' => $e->getMessage()
        ])
      ->log("System failed to send  $fileName");
    }
  }

  public function sendFile($fileName) {
    SendBackupEmail::dispatch($fileName);
  }

  public function generate() {
    GenerateBackup::dispatch()
    ->delay(now()->addSeconds(30));

    return activity()
    ->causedBy(auth()->user())
    ->log("Admin started to generate backup");
  }

  public function removeFile($fileName) {
    $backupDir = config('app.name');

    File::delete(storage_path("app/$backupDir/$fileName"));
    return activity()
    ->causedBy(auth()->user())
    ->log("Admin removed backup file $fileName");
  }

  public function getBackupFile($fileName) {
    $backupDir = config('app.name');
    $pathName = storage_path('app') . "/$backupDir/$fileName";
    if (!file_exists($pathName)) {
      throw new Exception(__("this file doen't exists"));
    }
    return $pathName;
  }
}
