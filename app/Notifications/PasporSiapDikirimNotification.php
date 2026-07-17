<?php

namespace App\Notifications;

use App\Models\TransaksiPaspor;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PasporSiapDikirimNotification extends Notification
{
    use Queueable;

    protected $paspor;

    /**
     * Create a new notification instance.
     */
    public function __construct(TransaksiPaspor $paspor)
    {
        $this->paspor = $paspor;
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
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'transaksi_id' => $this->paspor->id,
            'id_transaksi' => $this->paspor->id_transaksi,
            'nama_penerima' => $this->paspor->nama_penerima,
            'pesan' => "Paspor atas nama {$this->paspor->nama_penerima} ({$this->paspor->id_transaksi}) siap dikirim hari ini."
        ];
    }
}
