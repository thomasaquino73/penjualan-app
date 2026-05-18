<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PurchaseRequisitionNotification extends Notification
{
    use Queueable;

    private $PurchaseRequisition;

    public function __construct($PurchaseRequisition)
    {
        $this->PurchaseRequisition = $PurchaseRequisition;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'ID_number' => $this->PurchaseRequisition->code,
            'user_id' => $this->PurchaseRequisition->creator->id,
            'avatar' => $this->PurchaseRequisition->creator->avatar ?? asset('assets/img/avatars/1.png'),
            'title' => 'New Purchase Requisition Created',
            'messages' => $this->PurchaseRequisition->creator->fullname .
                ' has created PR "' . $this->PurchaseRequisition->code . '"',
            'link' => route('permintaan-pembelian.index'),
            'module_app' => 'permintaan-pembelian',
        ];
    }
}
