<?php

namespace App\Console\Commands;

use App\Models\TransaksiPaspor;
use App\Models\User;
use App\Notifications\PasporSiapDikirimNotification;
use App\Services\LogistikService;
use Illuminate\Console\Command;

class CheckPasporEstimasiCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'paspor:check-estimasi';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check paspor yang tanggal estimasi selesainya adalah hari ini dan beri notifikasi.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = now()->format('Y-m-d');

        $paspors = TransaksiPaspor::whereDate('estimasi_tanggal_selesai', '<=', $today)
            ->where('status_pengiriman', '!=', 'selesai')
            ->where('status_pengiriman', '!=', 'batal')
            ->get();

        if ($paspors->isEmpty()) {
            $this->info('Tidak ada paspor yang siap dikirim hari ini.');
            return;
        }

        $users = User::whereIn('role', ['super_admin', 'kasir_paspor'])->get();

        $count = 0;
        foreach ($paspors as $paspor) {
            // Cek apakah sudah pernah dinotif untuk transaksi ini hari ini agar tidak duplikat
            $alreadyNotified = false;
            foreach ($users as $user) {
                $hasNotif = $user->notifications()
                    ->where('type', PasporSiapDikirimNotification::class)
                    ->where('data->transaksi_id', $paspor->id)
                    ->whereDate('created_at', today())
                    ->exists();

                if (!$hasNotif) {
                    $user->notify(new PasporSiapDikirimNotification($paspor));
                    $alreadyNotified = true;
                }
            }
            if ($alreadyNotified) {
                $count++;
            }
        }

        $this->info("Berhasil mengirim notifikasi untuk {$count} paspor.");

        // ── Auto-sync status resi dari BinderByte (jika status 'diproses' dan ada no_resi) ──
        $this->info("Memeriksa status resi ekspedisi via BinderByte...");
        $diprosesWithResi = TransaksiPaspor::where('status_pengiriman', 'diproses')
            ->whereNotNull('no_resi')
            ->where('no_resi', '!=', '')
            ->get();

        $logistikService = app(LogistikService::class);
        $syncedCount = 0;

        foreach ($diprosesWithResi as $paspor) {
            $track = $logistikService->lacakResi($paspor->no_resi, strtolower($paspor->jenis_kurir ?? 'pos'));
            if (($track['status'] ?? 0) == 200 && !empty($track['data']['summary']['status']) && empty($track['is_fallback'])) {
                $statusResi = strtoupper(trim($track['data']['summary']['status']));
                // Jika status dari ekspedisi adalah DELIVERED / BERHASIL TERKIRIM / SELESAI
                if (in_array($statusResi, ['DELIVERED', 'SELESAI', 'TERKIRIM', 'SUCCESS', 'DITERIMA'])) {
                    $paspor->update([
                        'status_pengiriman'       => 'selesai',
                        'tanggal_paspor_diterima' => today()
                    ]);
                    $syncedCount++;
                }
            }
        }

        if ($syncedCount > 0) {
            $this->info("Otomatis memperbarui {$syncedCount} paspor menjadi status Selesai berdasarkan resi.");
        }
    }
}
