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
     * CATATAN KEAMANAN: Sengaja tidak menyimpan nama_penerima (PII) secara plaintext
     * di tabel notifications. Hanya ID yang disimpan, nama diambil dari tabel
     * transaksi_paspors (yang sudah terenkripsi AES-256) saat ditampilkan di UI.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'transaksi_id' => $this->paspor->id,
            'id_transaksi' => $this->paspor->id_transaksi,
            'jumlah_paspor' => $this->paspor->jumlah_paspor,
            'status' => $this->paspor->status_pengiriman,
            'jenis_kurir' => $this->paspor->jenis_kurir,
        ];
    }
}
