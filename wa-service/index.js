const express = require('express');
const cors = require('cors');
const qrcode = require('qrcode');
const pino = require('pino');
const fs = require('fs');
const path = require('path');
const { makeWASocket, useMultiFileAuthState, DisconnectReason } = require('@whiskeysockets/baileys');

const app = express();
const PORT = process.env.WA_PORT || 3001;
const AUTH_DIR = path.join(__dirname, 'auth_info_baileys');

app.use(cors());
app.use(express.json());

// Global State
let sock = null;
let isConnected = false;
let connectedPhone = null;
let currentQR = null;
let currentQRDataURL = null;

const logger = pino({ level: 'silent' }); // Silent logger agar terminal bersih

async function connectToWhatsApp() {
    try {
        if (!fs.existsSync(AUTH_DIR)) {
            fs.mkdirSync(AUTH_DIR, { recursive: true });
        }

        const { state, saveCreds } = await useMultiFileAuthState(AUTH_DIR);

        sock = makeWASocket({
            auth: state,
            printQRInTerminal: true,
            logger: logger,
            browser: ['KPRI Portal Paspor', 'Chrome', '1.0.0']
        });

        sock.ev.on('creds.update', saveCreds);

        sock.ev.on('connection.update', async (update) => {
            const { connection, lastDisconnect, qr } = update;

            if (qr) {
                currentQR = qr;
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
                console.log('[WA-Service] QR Code baru siap di-scan (tersedia juga via API /status).');
            }

            if (connection === 'close') {
                const statusCode = lastDisconnect?.error?.output?.statusCode;
                const shouldReconnect = statusCode !== DisconnectReason.loggedOut;
                
                isConnected = false;
                connectedPhone = null;
                console.log(`[WA-Service] Koneksi terputus (status: ${statusCode}). Reconnect? ${shouldReconnect}`);

                if (statusCode === DisconnectReason.loggedOut) {
                    console.log('[WA-Service] Sesi ter-logout oleh user. Menghapus data sesi...');
                    cleanAuthDir();
                    // Generate QR baru setelah logout
                    setTimeout(connectToWhatsApp, 2000);
                } else if (shouldReconnect) {
                    setTimeout(connectToWhatsApp, 3000);
                }
            } else if (connection === 'open') {
                isConnected = true;
                currentQR = null;
                currentQRDataURL = null;

                // Ambil nomor HP yang terhubung dari user JID
                const rawId = sock.user?.id || '';
                connectedPhone = rawId.split(':')[0].split('@')[0];
                console.log(`[WA-Service] WhatsApp BERHASIL terhubung! Nomor: ${connectedPhone}`);
            }
        });
    } catch (error) {
        console.error('[WA-Service] Error saat connectToWhatsApp:', error);
        setTimeout(connectToWhatsApp, 5000);
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

// Cek status & QR code live
app.get('/status', (req, res) => {
    res.json({
        status: isConnected ? 'connected' : 'disconnected',
        phone: connectedPhone,
        qr: currentQR,
        qr_data_url: currentQRDataURL
    });
});

// Kirim pesan WhatsApp
app.post('/send', async (req, res) => {
    const { phone, message } = req.body;

    if (!isConnected || !sock) {
        return res.status(400).json({
            status: 'error',
            message: 'WhatsApp belum terhubung (Silakan scan QR Code terlebih dahulu di panel Admin).'
        });
    }

    if (!phone || !message) {
        return res.status(400).json({
            status: 'error',
            message: 'Nomor HP (phone) dan isi pesan (message) wajib diisi.'
        });
    }

    try {
        // Bersihkan dan format nomor HP ke standar internasional JID (628xxx@s.whatsapp.net)
        let cleanPhone = phone.replace(/\D/g, '');
        if (cleanPhone.startsWith('0')) {
            cleanPhone = '62' + cleanPhone.substring(1);
        } else if (cleanPhone.startsWith('+62')) {
            cleanPhone = '62' + cleanPhone.substring(3);
        }

        const jid = `${cleanPhone}@s.whatsapp.net`;

        // Kirim pesan via Baileys socket
        await sock.sendMessage(jid, { text: message });

        console.log(`[WA-Service] Pesan berhasil dikirim ke ${cleanPhone}`);
        return res.json({
            status: 'success',
            message: `Pesan berhasil dikirim ke nomor ${cleanPhone}`
        });
    } catch (error) {
        console.error(`[WA-Service] Gagal mengirim pesan ke ${phone}:`, error);
        return res.status(500).json({
            status: 'error',
            message: 'Gagal mengirim pesan dari bot: ' + (error.message || error)
        });
    }
});

// Logout / Reset session (Ganti nomor)
app.post('/logout', async (req, res) => {
    try {
        console.log('[WA-Service] Menerima perintah Logout dari Laravel Admin.');
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

        // Mulai ulang socket agar QR code baru langsung dibentuk
        setTimeout(connectToWhatsApp, 1500);

        return res.json({
            status: 'success',
            message: 'Sesi berhasil dihapus. QR Code baru sedang dipersiapkan.'
        });
    } catch (error) {
        console.error('[WA-Service] Error logout:', error);
        return res.status(500).json({
            status: 'error',
            message: 'Gagal melakukan logout: ' + error.message
        });
    }
});

// Start Express & WhatsApp Socket
app.listen(PORT, '127.0.0.1', () => {
    console.log(`[WA-Service] Server berjalan di http://127.0.0.1:${PORT}`);
    connectToWhatsApp();
});
