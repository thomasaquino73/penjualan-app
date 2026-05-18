<?php

namespace App\Notifications;

use App\Models\General\Company;
use App\Models\PengaturanSistem;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PurchaseRequisitionNotification extends Notification
{
    use Queueable;

    private $purchaseRequisition;

    public function __construct($purchaseRequisition)
    {
        $this->purchaseRequisition = $purchaseRequisition;
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
            ->subject('Purchase Requisition New')
            ->view('emails.permintaan-pembelian-new', [
                'user' => $notifiable,
                'creator' => $this->purchaseRequisition->creator,
                'purchaseRequisition' => $this->purchaseRequisition,
                'company' => $company,
                'url' => route('permintaan-pembelian.show', $this->purchaseRequisition),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'ID_number' => $this->purchaseRequisition->code,
            'user_id' => $this->purchaseRequisition->creator->id,
            'avatar' => $this->purchaseRequisition->creator->avatar ?? asset('assets/img/avatars/1.png'),
            'title' => 'New Purchase Requisition Created',
            'messages' => $this->purchaseRequisition->creator->fullname .
                ' has created PR "' . $this->purchaseRequisition->code . '"',
            'link' => route('permintaan-pembelian.show', $this->purchaseRequisition),
        ];
    }
}
