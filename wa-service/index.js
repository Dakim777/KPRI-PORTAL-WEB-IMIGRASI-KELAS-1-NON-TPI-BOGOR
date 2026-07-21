const express = require('express');
const cors = require('cors');
const qrcode = require('qrcode');
const qrcodeTerminal = require('qrcode-terminal');
const pino = require('pino');
const fs = require('fs');
const path = require('path');
const { 
    makeWASocket, 
    useMultiFileAuthState, 
    DisconnectReason, 
    fetchLatestBaileysVersion, 
    makeCacheableSignalKeyStore,
    Browsers
} = require('@whiskeysockets/baileys');

const app = express();
const PORT = process.env.WA_PORT || 3001;
const AUTH_DIR = path.join(__dirname, 'auth_info_baileys');

app.use(cors());
app.use(express.json());

// Global State
let sock = null;
let isConnected = false;
let isConnecting = false;
let connectedPhone = null;
let currentQR = null;
let currentQRDataURL = null;

const logger = pino({ level: 'silent' }); // Silent logger agar terminal bersih

async function connectToWhatsApp() {
    if (isConnecting) {
        return;
    }
    isConnecting = true;

    try {
        if (!fs.existsSync(AUTH_DIR)) {
            fs.mkdirSync(AUTH_DIR, { recursive: true });
        }

        // Bersihkan socket lama sebelum membuat yang baru agar tidak bentrok (440 connectionReplaced)
        if (sock) {
            try {
                sock.ev.removeAllListeners();
                if (sock.ws) sock.ws.close();
            } catch (e) { }
            sock = null;
        }

        const { version, isLatest } = await fetchLatestBaileysVersion();
        const { state, saveCreds } = await useMultiFileAuthState(AUTH_DIR);

        // Kunci nomor jika sudah ada kredensial tersimpan (Session Lock)
        if (state.creds?.me?.id) {
            connectedPhone = state.creds.me.id.split(':')[0].split('@')[0];
        }

        sock = makeWASocket({
            version,
            auth: {
                creds: state.creds,
                keys: makeCacheableSignalKeyStore(state.keys, logger),
            },
            printQRInTerminal: false,
            logger: logger,
            browser: Browsers.macOS('Desktop'),
            syncFullHistory: false,
            markOnlineOnConnect: false,
            generateHighQualityLinkPreview: false,
            connectTimeoutMs: 60000,
            defaultQueryTimeoutMs: 60000,
            keepAliveIntervalMs: 25000,
        });

        sock.ev.on('creds.update', saveCreds);

        sock.ev.on('connection.update', async (update) => {
            const { connection, lastDisconnect, qr } = update;

            // Hanya proses QR jika nomor BELUM terkunci / sesi belum terdaftar (kecuali logout)
            if (qr && !connectedPhone && !state.creds?.registered) {
                currentQR = qr;
                console.log('\n======================================================');
                console.log('[WA-Service] QR Code siap! Scan menggunakan HP Anda:');
                console.log('======================================================\n');
                qrcodeTerminal.generate(qr, { small: true });
                try {
                    currentQRDataURL = await qrcode.toDataURL(qr, {
                        errorCorrectionLevel: 'M',
                        margin: 2,
                        width: 300,
                        color: {
                            dark: '#0f172a',
                            light: '#ffffff'
                        }
                    });
                } catch (err) {
                    console.error('Gagal generate QR DataURL:', err);
                }
                console.log('\n[WA-Service] QR juga bisa discan via Web Dasbor Laravel (/admin/whatsapp).');
            }

            if (connection === 'close') {
                const statusCode = lastDisconnect?.error?.output?.statusCode;
                const shouldReconnect = statusCode !== DisconnectReason.loggedOut;
                
                isConnected = false;
                console.log(`[WA-Service] Koneksi terputus (status: ${statusCode}). Reconnect: ${shouldReconnect}`);

                if (statusCode === DisconnectReason.loggedOut) {
                    console.log('[WA-Service] Sesi diakhiri (Logout). Membersihkan folder otentikasi...');
                    connectedPhone = null;
                    currentQR = null;
                    currentQRDataURL = null;
                    cleanAuthDir();
                    setTimeout(connectToWhatsApp, 2000);
                } else if (shouldReconnect) {
                    // Berikan jeda 5 detik untuk mencegah reconnect loop / 440 conflict
                    setTimeout(connectToWhatsApp, 5000);
                }
            } else if (connection === 'open') {
                isConnected = true;
                currentQR = null;
                currentQRDataURL = null;

                const rawId = sock.user?.id || '';
                connectedPhone = rawId.split(':')[0].split('@')[0];
                console.log(`\n✅ [WA-Service] BERHASIL Terhubung ke WhatsApp! Nomor: +${connectedPhone}`);
            }
        });
    } catch (error) {
        console.error('[WA-Service] Error connectToWhatsApp:', error);
        setTimeout(connectToWhatsApp, 5000);
    } finally {
        isConnecting = false;
    }
}

function cleanAuthDir() {
    try {
        if (fs.existsSync(AUTH_DIR)) {
            fs.rmSync(AUTH_DIR, { recursive: true, force: true });
        }
    } catch (err) {
        console.error('Error membersihkan folder auth:', err);
    }
}

// ==========================================
// API Endpoints untuk Laravel Backend
// ==========================================

app.get('/status', (req, res) => {
    let statusStr = 'disconnected';
    if (isConnected) {
        statusStr = 'connected';
    } else if (connectedPhone) {
        statusStr = 'reconnecting';
    }

    res.json({
        status: statusStr,
        phone: connectedPhone,
        qr: isConnected || connectedPhone ? null : currentQR,
        qr_data_url: isConnected || connectedPhone ? null : currentQRDataURL
    });
});

app.post('/send', async (req, res) => {
    const { phone, message } = req.body;

    if (!isConnected || !sock) {
        return res.status(400).json({
            status: 'error',
            message: 'WhatsApp belum terhubung (Silakan scan QR Code terlebih dahulu).'
        });
    }

    if (!phone || !message) {
        return res.status(400).json({
            status: 'error',
            message: 'Nomor HP (phone) dan isi pesan (message) wajib diisi.'
        });
    }

    try {
        let cleanPhone = phone.replace(/\D/g, '');
        if (cleanPhone.startsWith('0')) {
            cleanPhone = '62' + cleanPhone.substring(1);
        } else if (cleanPhone.startsWith('+62')) {
            cleanPhone = '62' + cleanPhone.substring(3);
        }

        const jid = `${cleanPhone}@s.whatsapp.net`;
        try { await sock.presenceSubscribe(jid); } catch (e) { }

        await Promise.race([
            sock.sendMessage(jid, { text: message }),
            new Promise((_, reject) => setTimeout(() => reject(new Error('Waktu pengiriman habis (30 detik). Silakan coba kirim ulang.')), 30000))
        ]);

        console.log(`[WA-Service] Pesan berhasil dikirim ke ${cleanPhone}`);
        return res.json({
            status: 'success',
            message: `Pesan berhasil dikirim ke nomor ${cleanPhone}`
        });
    } catch (error) {
        // Jika terjadi error 408 (USync Timed Out) saat sinkronisasi kontak baru, lakukan 1x retry otomatis
        if (error?.output?.statusCode === 408 || (error?.message && error.message.includes('Timed Out'))) {
            try {
                console.log(`[WA-Service] Terjadi timeout USync (408) untuk ${phone}, mencoba kirim ulang otomatis...`);
                await new Promise(r => setTimeout(r, 2500));
                let cleanPhone = phone.replace(/\D/g, '');
                if (cleanPhone.startsWith('0')) cleanPhone = '62' + cleanPhone.substring(1);
                else if (cleanPhone.startsWith('+62')) cleanPhone = '62' + cleanPhone.substring(3);
                const jid = `${cleanPhone}@s.whatsapp.net`;
                await sock.sendMessage(jid, { text: message });
                console.log(`[WA-Service] Pesan BERHASIL dikirim ke ${cleanPhone} pada percobaan kedua!`);
                return res.json({
                    status: 'success',
                    message: `Pesan berhasil dikirim ke nomor ${cleanPhone}`
                });
            } catch (retryErr) {
                console.error(`[WA-Service] Gagal pada percobaan kedua ke ${phone}:`, retryErr);
            }
        }

        console.error(`[WA-Service] Gagal mengirim pesan ke ${phone}:`, error);
        return res.status(500).json({
            status: 'error',
            message: 'Gagal mengirim pesan: ' + (error.message || error)
        });
    }
});

app.post('/logout', async (req, res) => {
    try {
        console.log('[WA-Service] Menerima perintah Logout.');
        if (sock && isConnected) {
            try {
                await sock.logout();
            } catch (e) {
                console.warn('Error socket logout:', e.message);
            }
        }
        
        isConnected = false;
        connectedPhone = null;
        currentQR = null;
        currentQRDataURL = null;
        cleanAuthDir();

        setTimeout(connectToWhatsApp, 1500);

        return res.json({
            status: 'success',
            message: 'Sesi berhasil dihapus. QR Code baru sedang disiapkan.'
        });
    } catch (error) {
        console.error('[WA-Service] Error logout:', error);
        return res.status(500).json({
            status: 'error',
            message: 'Gagal melakukan logout: ' + error.message
        });
    }
});

app.listen(PORT, '127.0.0.1', () => {
    console.log(`[WA-Service] Server berjalan di http://127.0.0.1:${PORT}`);
    connectToWhatsApp();
});
