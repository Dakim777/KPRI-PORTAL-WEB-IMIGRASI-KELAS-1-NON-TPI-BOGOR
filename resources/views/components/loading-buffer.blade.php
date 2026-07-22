{{-- Global Clean & Professional Loading Buffer Animation --}}
<div id="kpri-global-loader" class="fixed inset-0 z-[999999] bg-slate-950/80 backdrop-blur-md flex items-center justify-center transition-all duration-300 opacity-0 pointer-events-none">
    <div id="kpri-global-loader-card" class="bg-slate-900/95 border border-slate-700/80 rounded-2xl p-6 sm:p-8 shadow-2xl shadow-blue-950/50 flex flex-col items-center justify-center gap-5 max-w-sm w-full mx-4 transform scale-95 transition-all duration-300">
        {{-- Animated Dual Orbital & Glowing Spinner --}}
        <div class="relative w-20 h-20 flex items-center justify-center">
            {{-- Outer Subtle Ring --}}
            <div class="absolute inset-0 rounded-full border-4 border-blue-500/15 shadow-inner"></div>
            {{-- Primary Spinning Ring --}}
            <div class="absolute inset-0 rounded-full border-4 border-transparent border-t-blue-500 border-r-indigo-500 animate-spin" style="animation-duration: 0.75s;"></div>
            {{-- Reverse Secondary Spinning Ring --}}
            <div class="absolute inset-2 rounded-full border-4 border-transparent border-b-amber-400 border-l-blue-400 animate-spin" style="animation-duration: 1.1s; animation-direction: reverse;"></div>
            {{-- Center Badge --}}
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-800 flex items-center justify-center shadow-md shadow-blue-500/40 animate-pulse">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        {{-- Status Text --}}
        <div class="text-center space-y-1.5 w-full px-2">
            <h4 id="kpri-loader-title" class="text-white font-extrabold text-base sm:text-lg tracking-tight">Memproses Data...</h4>
            <p id="kpri-loader-desc" class="text-slate-400 text-xs sm:text-sm leading-relaxed">Mohon tunggu sebentar, sistem sedang mengolah dan memverifikasi permintaan Anda.</p>
        </div>

        {{-- Smooth Pulse Progress Line --}}
        <div class="w-full bg-slate-800 rounded-full h-1 overflow-hidden">
            <div class="bg-gradient-to-r from-blue-500 via-indigo-400 to-amber-400 h-1 rounded-full animate-pulse w-full"></div>
        </div>
    </div>
</div>

<script>
window.KPRILoader = {
    show: function(title = 'Memproses Data...', desc = 'Mohon tunggu sebentar, sistem sedang mengolah dan memverifikasi permintaan Anda.') {
        const overlay = document.getElementById('kpri-global-loader');
        const card = document.getElementById('kpri-global-loader-card');
        const titleEl = document.getElementById('kpri-loader-title');
        const descEl = document.getElementById('kpri-loader-desc');
        
        if (titleEl) titleEl.innerText = title;
        if (descEl) descEl.innerText = desc;
        
        if (overlay) {
            overlay.classList.remove('opacity-0', 'pointer-events-none');
            overlay.classList.add('opacity-100', 'pointer-events-auto');
        }
        if (card) {
            card.classList.remove('scale-95');
            card.classList.add('scale-100');
        }
    },
    hide: function() {
        const overlay = document.getElementById('kpri-global-loader');
        const card = document.getElementById('kpri-global-loader-card');
        if (overlay) {
            overlay.classList.remove('opacity-100', 'pointer-events-auto');
            overlay.classList.add('opacity-0', 'pointer-events-none');
        }
        if (card) {
            card.classList.remove('scale-100');
            card.classList.add('scale-95');
        }
    }
};

// Global Listener for All Forms & Interactive Elements
document.addEventListener('DOMContentLoaded', function() {
    // Intercept form submissions
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            // Check HTML5 validity
            if (this.checkValidity && !this.checkValidity()) {
                return;
            }
            // If explicitly excluded
            if (this.hasAttribute('data-no-loader')) {
                return;
            }

            // Custom texts from data attributes
            const customTitle = this.getAttribute('data-loader-title') || 'Memproses Data...';
            const customDesc = this.getAttribute('data-loader-desc') || 'Mohon tunggu sebentar, sistem sedang memproses permintaan Anda.';

            // Disable submit buttons inside this form to prevent duplicate submissions
            const submitBtns = this.querySelectorAll('button[type="submit"], input[type="submit"]');
            submitBtns.forEach(btn => {
                btn.disabled = true;
                btn.classList.add('opacity-75', 'cursor-not-allowed');
            });

            window.KPRILoader.show(customTitle, customDesc);
        });
    });

    // Also support links with data-loader attribute (for quick actions / logout / export)
    document.querySelectorAll('a[data-loader]').forEach(link => {
        link.addEventListener('click', function(e) {
            if (this.getAttribute('href') === '#' || this.getAttribute('href') === 'javascript:void(0)') return;
            const customTitle = this.getAttribute('data-loader-title') || 'Memproses Perintah...';
            const customDesc = this.getAttribute('data-loader-desc') || 'Mohon tunggu sebentar...';
            window.KPRILoader.show(customTitle, customDesc);
        });
    });
});
</script>
