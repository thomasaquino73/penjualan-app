<?php

namespace App\Notifications;

use App\Models\PengaturanSistem;
use App\Models\Setting\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SalesQuotationNotification extends Notification
{
    use Queueable;

    private $salesQuotation;

    public function __construct($salesQuotation)
    {
        $this->salesQuotation = $salesQuotation;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $sistem = PengaturanSistem::first();
        $appName = $sistem->nama_aplikasi ?? config('app.name');
        $company = Company::first();
        $companyName = $company->nama_perusahaan ?? config('app.name');

        return (new MailMessage)
            ->from(env('MAIL_FROM_ADDRESS'), $appName)
            ->subject('Sales Quotation New')
            ->view('emails.sales-quotation-new', [
                'user' => $notifiable,
                'creator' => $this->salesQuotation->creator,
                'salesQuotation' => $this->salesQuotation,
                'company' => $company,
                'url' => route('sales-quotation.show', $this->salesQuotation),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'ID_number' => $this->salesQuotation->code,
            'user_id' => $this->salesQuotation->creator->id,
            'avatar' => $this->salesQuotation->creator->avatar ?? asset('assets/img/avatars/1.png'),
            'title' => 'New Sales Quotation Created',
            'messages' => $this->salesQuotation->creator->fullname.
                ' has created SQ "'.$this->salesQuotation->sales_quotation_code.'"',
            'link' => route('sales-quotation.show', $this->salesQuotation),
        ];
    }
}
