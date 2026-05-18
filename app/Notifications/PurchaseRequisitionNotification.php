<?php

namespace App\Notifications;

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
        return (new MailMessage)
            ->subject('Purchase Requisition New')
            ->view('emails.permintaan-pembelian-new', [
                'user' => $notifiable,
                'creator' => $this->purchaseRequisition->creator,
                'purchaseRequisition' => $this->purchaseRequisition,
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
