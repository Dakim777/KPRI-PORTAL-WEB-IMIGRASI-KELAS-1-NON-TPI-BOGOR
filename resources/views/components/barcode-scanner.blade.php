@props(['targetId' => 'barcode'])

<div class="relative">
    <button type="button"
        onclick="window.openBarcodeScanner('{{ $targetId }}')"
        class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-white text-blue-900 border-2 border-blue-900 rounded-lg text-sm font-semibold hover:bg-blue-900 hover:text-white transition-all duration-200">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        Scan Webcam
    </button>
</div>

@once
<div id="globalScannerModal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.85);" onclick="if(event.target===this) window.closeBarcodeScanner();">
    <div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); background:white; border-radius:16px; width:min(540px,95vw); overflow:hidden; box-shadow:0 25px 60px rgba(0,0,0,0.5);">

        {{-- Header --}}
        <div style="background:linear-gradient(135deg,#1A3A6B,#2352A0); padding:14px 20px; display:flex; align-items:center; justify-content:space-between;">
            <div>
                <h3 style="color:white; font-weight:700; font-size:15px; margin:0;">Scan Barcode Produk</h3>
                <p style="color:#8AAFE8; font-size:11px; margin:3px 0 0 0;">Pastikan barcode berada di dalam area scan</p>
            </div>
            <button onclick="window.closeBarcodeScanner()" style="background:rgba(255,255,255,0.15); border:none; border-radius:8px; padding:6px 12px; cursor:pointer; color:white; font-size:20px; font-weight:bold; line-height:1;">&times;</button>
        </div>

        {{-- Status --}}
        <div id="scannerStatus" style="padding:8px 16px; background:#dbeafe; font-size:12px; color:#1e40af; text-align:center; font-weight:500;">
            Memuat kamera...
        </div>

        {{-- html5-qrcode container - lib will render camera here --}}
        <div id="html5-qrcode-reader" style="width:100%;"></div>

        {{-- Success --}}
        <div id="scannerSuccess" style="display:none; padding:12px 20px; background:#dcfce7; border-top:2px solid #86efac;">
            <p style="margin:0; font-size:12px; color:#166534; font-weight:600;">Barcode berhasil dibaca!</p>
            <p id="scannerSuccessVal" style="margin:3px 0 0; font-family:monospace; font-size:18px; color:#15803d; font-weight:700;"></p>
        </div>

        {{-- Error --}}
        <div id="scannerErrBox" style="display:none; padding:16px 20px; background:#fee2e2; border-top:1px solid #fca5a5; text-align:center;">
            <p style="font-size:13px; color:#991b1b; margin:0;" id="scannerErrMsg"></p>
            <button onclick="window.retryBarcodeScanner()" style="margin-top:10px; background:#1A3A6B;color:white;border:none;border-radius:8px;padding:8px 18px;font-size:13px;cursor:pointer;font-weight:600;">Coba Lagi</button>
        </div>

        {{-- Footer --}}
        <div style="padding:10px 16px; background:#f8fafc; border-top:1px solid #e5e7eb; text-align:right;">
            <button onclick="window.closeBarcodeScanner()" style="background:white;border:1px solid #d1d5db;border-radius:8px;padding:7px 16px;font-size:13px;cursor:pointer;color:#374151;font-weight:500;">Tutup</button>
        </div>
    </div>
</div>
@endonce

@once
@push('scripts')
{{-- html5-qrcode: most battle-tested browser barcode library --}}
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
(function () {
    if (window._kScanInit) return;
    window._kScanInit = true;

    let _targetId = null;
    let _scanner  = null;
    let _running  = false;

    function setStatus(msg, type) {
        const el = document.getElementById('scannerStatus');
        if (!el) return;
        el.textContent = msg;
        const map = {
            info: { bg:'#dbeafe', c:'#1e40af' },
            ok:   { bg:'#dcfce7', c:'#166534' },
            err:  { bg:'#fee2e2', c:'#991b1b' },
            warn: { bg:'#fef9c3', c:'#854d0e' },
        };
        const s = map[type] || map.info;
        el.style.background = s.bg;
        el.style.color = s.c;
    }

    function showErr(msg) {
        document.getElementById('scannerErrBox').style.display = 'block';
        document.getElementById('scannerErrMsg').textContent = msg;
        setStatus('Gagal mengakses kamera', 'err');
    }

    function stopScanner() {
        if (_scanner && _running) {
            _running = false;
            _scanner.stop().then(function () {
                _scanner.clear();
            }).catch(function () {});
        }
        _running = false;
    }

    function startScanner() {
        document.getElementById('scannerErrBox').style.display = 'none';
        document.getElementById('scannerSuccess').style.display = 'none';
        document.getElementById('html5-qrcode-reader').innerHTML = '';
        setStatus('Meminta izin kamera...', 'info');

        _scanner = new Html5Qrcode('html5-qrcode-reader', {
            verbose: false,
            experimentalFeatures: { useBarCodeDetectorIfSupported: true }
        });

        const readerEl = document.getElementById('html5-qrcode-reader');
        const readerWidth = readerEl.offsetWidth || 480;

        // Area scan lebar untuk barcode 1D panjang (seperti resi POS Indonesia)
        const boxW = Math.min(readerWidth - 20, 460);
        const boxH = Math.round(boxW * 0.25); // sangat tipis horizontal = fokus baca barcode 1D

        const config = {
            fps: 25,                           // FPS lebih tinggi = lebih cepat deteksi
            qrbox: { width: boxW, height: boxH },
            experimentalFeatures: {
                useBarCodeDetectorIfSupported: true // Native browser API jauh lebih tajam untuk barcode 1D rapat
            },
            videoConstraints: {
                width: { ideal: 1920 },
                height: { ideal: 1080 }
            },
            // Tidak mengunci aspectRatio agar kamera pakai resolusi penuh
            formatsToSupport: [
                Html5QrcodeSupportedFormats.CODE_128,  // Format utama resi kurir Indonesia
                Html5QrcodeSupportedFormats.CODE_39,
                Html5QrcodeSupportedFormats.CODE_93,
                Html5QrcodeSupportedFormats.CODABAR,
                Html5QrcodeSupportedFormats.EAN_13,
                Html5QrcodeSupportedFormats.EAN_8,
                Html5QrcodeSupportedFormats.UPC_A,
                Html5QrcodeSupportedFormats.UPC_E,
                Html5QrcodeSupportedFormats.ITF,
                Html5QrcodeSupportedFormats.QR_CODE,
                Html5QrcodeSupportedFormats.DATA_MATRIX,
            ]
        };

        _scanner.start(
            { facingMode: 'environment' },
            config,
            function onSuccess(decodedText) {
                onDetected(decodedText);
            },
            function onError() { /* scanning frames, ignore misses */ }
        ).then(function () {
            _running = true;
            setStatus('Arahkan barcode ke area scan — jaga jarak ±15–20cm', 'info');
        }).catch(function (err) {
            let msg = 'Gagal memulai kamera: ' + err;
            if (typeof err === 'string') {
                if (err.includes('NotAllowed') || err.includes('Permission'))
                    msg = 'Izin kamera ditolak.\n\nDi Brave: klik ikon kunci di address bar → Site permissions → Camera → Allow, lalu reload halaman.';
                if (err.includes('NotFound'))
                    msg = 'Tidak ada kamera yang terdeteksi.';
                if (err.includes('NotReadable'))
                    msg = 'Kamera sedang digunakan aplikasi lain.';
            }
            showErr(msg);
        });
    }

    // ─── Global Beep (Supermarket Scanner Style) ──────────────────
    window.playBeep = function() {
        try {
            const ctx  = new (window.AudioContext || window.webkitAudioContext)();
            const t    = ctx.currentTime;
            const osc  = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.type            = 'sine';
            osc.frequency.value = 1880;
            gain.gain.setValueAtTime(0, t);
            gain.gain.linearRampToValueAtTime(0.5, t + 0.005);
            gain.gain.setValueAtTime(0.5, t + 0.08);
            gain.gain.linearRampToValueAtTime(0, t + 0.10);
            osc.start(t);
            osc.stop(t + 0.12);
            osc.onended = () => ctx.close();
        } catch(e) {}
    };

    function onDetected(value) {
        stopScanner();
        document.getElementById('scannerSuccess').style.display = 'block';
        document.getElementById('scannerSuccessVal').textContent = value;
        setStatus('Barcode berhasil dibaca!', 'ok');

        window.playBeep(); // Beep saat webcam berhasil baca barcode

        if (_targetId) {
            const el = document.getElementById(_targetId);
            if (el) {
                el.value = value;
                el.dispatchEvent(new Event('input',  { bubbles: true }));
                el.dispatchEvent(new Event('change', { bubbles: true }));
                el.focus();
            }
        }

        setTimeout(window.closeBarcodeScanner, 1500);
    }

    /* ─── Public API ─────────────────────────────────────────── */
    window.openBarcodeScanner = function (targetId) {
        _targetId = targetId;
        document.getElementById('globalScannerModal').style.display = 'block';
        // Give modal time to fully render before initializing camera
        setTimeout(startScanner, 250);
    };

    window.closeBarcodeScanner = function () {
        stopScanner();
        document.getElementById('globalScannerModal').style.display = 'none';
        document.getElementById('scannerSuccess').style.display = 'none';
        document.getElementById('scannerErrBox').style.display = 'none';
        document.getElementById('html5-qrcode-reader').innerHTML = '';
        _targetId = null;
    };

    window.retryBarcodeScanner = function () {
        document.getElementById('scannerErrBox').style.display = 'none';
        startScanner();
    };
})();
</script>
@endpush
@endonce
