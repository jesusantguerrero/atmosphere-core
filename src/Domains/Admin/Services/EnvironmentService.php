<?php

namespace Freesgen\Atmosphere\Domains\Admin\Services;

use Exception;
use Illuminate\Http\Request;
use App\Domains\Admin\Data\MailConfigData;

class EnvironmentService
{
    /**
     * @var string
     */
    private $envPath;

    /**
     * Set the .env and .env.example paths.
     */
    public function __construct()
    {
        $this->envPath = base_path('.env');
    }

    /**
    * Save the mail content to the .env file.
    *
    * @param Request $request
    * @return array
    */
    public function saveMailVariables(MailConfigData $data)
    {
        $mailData = $this->getMailData($data);

        try {
            file_put_contents($this->envPath, str_replace(
                $mailData['old_mail_data'],
                $mailData['new_mail_data'],
                file_get_contents($this->envPath)
            ));

            if ($mailData['extra_old_mail_data']) {
                file_put_contents($this->envPath, str_replace(
                    $mailData['extra_old_mail_data'],
                    $mailData['extra_mail_data'],
                    file_get_contents($this->envPath)
                ));
            } else {
                file_put_contents(
                    $this->envPath,
                    "\n".$mailData['extra_mail_data'],
                    FILE_APPEND
                );
            }
        } catch (Exception $e) {
            return [
                'error' => 'mail_variables_save_error',
            ];
        }

        return [
            'success' => 'mail_variables_save_successfully',
        ];
    }

    private function getMailData(MailConfigData $data)
    {
        $mailFromCredential = "";
        $extraMailData = "";
        $extraOldMailData = "";
        $oldMailData = "";
        $newMailData = "";

        if (env('MAIL_FROM_ADDRESS') !== null && env('MAIL_FROM_NAME') !== null) {
            $mailFromCredential =
                'MAIL_FROM_ADDRESS='.config('mail.from.address')."\n".
                'MAIL_FROM_NAME="'.config('mail.from.name')."\"\n\n";
        }

        switch ($data->driver) {
            case 'smtp':
                $oldMailData =
                    'MAIL_DRIVER='.config('mail.driver')."\n".
                    'MAIL_HOST='.config('mail.host')."\n".
                    'MAIL_PORT='.config('mail.port')."\n".
                    'MAIL_USERNAME='.config('mail.username')."\n".
                    'MAIL_PASSWORD='.config('mail.password')."\n".
                    'MAIL_ENCRYPTION='.config('mail.encryption')."\n\n".
                    $mailFromCredential;

                $newMailData =
                    'MAIL_DRIVER='.$data->driver."\n".
                    'MAIL_HOST='.$data->host."\n".
                    'MAIL_PORT='.$data->port."\n".
                    'MAIL_USERNAME='.$data->username."\n".
                    'MAIL_PASSWORD="'.$data->password."\"\n".
                    'MAIL_ENCRYPTION='.$data->encryption."\n\n".
                    'MAIL_FROM_ADDRESS='.$data->mail."\n".
                    'MAIL_FROM_NAME="'.$data->name."\"\n\n";
                break;
        }

        return [
            'old_mail_data' => $oldMailData,
            'new_mail_data' => $newMailData,
            'extra_mail_data' => $extraMailData,
            'extra_old_mail_data' => $extraOldMailData,
        ];
    }

    public function getMailEnvironment()
    {
      $driver = config('mail.default');
        return [
            'driver' => config('mail.default'),
            'host' => config("mail.mailers.$driver.host"),
            'port' => config("mail.mailers.$driver.port"),
            'username' => config("mail.mailers.$driver.username"),
            'password' => config('mail.password'),
            'encryption' => config("mail.mailers.$driver.encryption"),
            'name' => config('mail.from.name'),
            'mail' => config('mail.from.address'),
        ];

    }
}
