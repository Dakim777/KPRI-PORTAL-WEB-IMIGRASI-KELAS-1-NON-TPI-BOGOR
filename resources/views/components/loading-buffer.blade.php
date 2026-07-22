{{-- Global Clean & Professional Loading Buffer Animation (Optimized for Low-Spec Office PCs) --}}
<div id="kpri-global-loader" class="fixed inset-0 z-[999999] bg-slate-950/85 flex items-center justify-center transition-opacity duration-200 opacity-0 pointer-events-none">
    <div id="kpri-global-loader-card" class="bg-slate-900 border border-slate-700 rounded-2xl p-6 sm:p-8 shadow-2xl flex flex-col items-center justify-center gap-5 max-w-sm w-full mx-4 transform scale-95 transition-transform duration-200">
        {{-- Clean Single Orbital Spinner (Low CPU/GPU Load) --}}
        <div class="relative w-16 h-16 flex items-center justify-center">
            <div class="absolute inset-0 rounded-full border-4 border-slate-800"></div>
            <div class="absolute inset-0 rounded-full border-4 border-transparent border-t-blue-500 border-r-indigo-500 animate-spin" style="animation-duration: 0.7s; will-change: transform;"></div>
            <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center shadow">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        {{-- Status Text --}}
        <div class="text-center space-y-1.5 w-full px-2">
            <h4 id="kpri-loader-title" class="text-white font-extrabold text-base sm:text-lg tracking-tight">Memproses Data...</h4>
            <p id="kpri-loader-desc" class="text-slate-400 text-xs sm:text-sm leading-relaxed">Mohon tunggu sebentar, sistem sedang mengolah dan memverifikasi permintaan Anda.</p>
        </div>

        {{-- Static Clean Progress Bar --}}
        <div class="w-full bg-slate-800 rounded-full h-1 overflow-hidden">
            <div class="bg-gradient-to-r from-blue-500 to-indigo-500 h-1 rounded-full w-full"></div>
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
