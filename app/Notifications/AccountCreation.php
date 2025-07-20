<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\Client;
use App\Models\Template;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailBase;

class AccountCreation extends VerifyEmailBase
{
    protected $recipient;
    protected $password;

    public function __construct($recipient,$password)
    {
        $this->recipient = $recipient;
        $this->password = $password;
    }
    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $general_settings = get_settings('general_settings');
        $full_logo = !isset($general_settings['full_logo']) || empty($general_settings['full_logo']) ? 'storage/logos/default_full_logo.png' : 'storage/' . $general_settings['full_logo'];
        $company_title = $general_settings['company_title'] ?? 'Taskify - SaaS';
        $siteUrl = request()->getSchemeAndHttpHost() . '/master-panel';
        $fetched_data = Template::where('type', 'email')
            ->where('name', 'account_creation')
            ->first();

        // Define the placeholders and their corresponding values
        $subjectPlaceholders = [
            '{FIRST_NAME}' => $this->recipient->first_name,
            '{LAST_NAME}' => $this->recipient->last_name,
            '{COMPANY_TITLE}' => $company_title
        ];

        $subject = filled(Arr::get($fetched_data, 'subject')) ? $fetched_data->subject : 'Account Created - {COMPANY_TITLE}';

        $subject = str_replace(array_keys($subjectPlaceholders), array_values($subjectPlaceholders), $subject);

        $messagePlaceholders = [
            '{FIRST_NAME}' => $this->recipient->first_name,
            '{LAST_NAME}' => $this->recipient->last_name,
            '{USER_NAME}' => $this->recipient->email,
            '{PASSWORD}' => $this->password,
            '{COMPANY_TITLE}' => $company_title,
            '{SITE_URL}' => $siteUrl,
            '{CURRENT_YEAR}' => date('Y')
        ];
        if (filled(Arr::get($fetched_data, 'content'))) {
            $emailTemplate = $fetched_data->content;
        } else {
            $defaultTemplatePath = resource_path('views/mail/default_templates/account_creation.blade.php');
            $defaultTemplateContent = File::get($defaultTemplatePath);
            $emailTemplate = $defaultTemplateContent;
        }

        // Replace placeholders with actual values
        $emailTemplate = str_replace(array_keys($messagePlaceholders), array_values($messagePlaceholders), $emailTemplate);

        return (new MailMessage)
            ->view('mail.html', ['content' => $emailTemplate, 'logo_url' => asset($full_logo)])
            ->subject($subject);
    }
}
