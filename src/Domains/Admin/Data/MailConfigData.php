<?php

namespace App\Domains\Admin\Data;

use Spatie\LaravelData\Data;

class MailConfigData extends Data {
  public function __construct(
    public string $driver,
    public string $host,
    public string $port,
    public string $username,
    public string $password,
    public string $encryption,
    public string $mail,
    public string $name,
  )
  {

  }
}
