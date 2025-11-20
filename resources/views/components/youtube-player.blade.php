@props(['videoId', 'title' => 'Video'])

@if($videoId)
<div class="youtube-player-container mb-6" data-youtube-component="true">
    {{-- <!-- Video Title -->
    @if($title && $title !== 'Video')
    <div class="mb-3">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
            <svg class="w-5 h-5 mr-2 text-red-600" fill="currentColor" viewBox="0 0 24 24">
                <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
            </svg>
            {{ $title }}
        </h3>
    </div>
    @endif --}}

    <!-- YouTube Player enhanced by Plyr -->
    <div class="youtube-player-wrapper relative w-full aspect-video bg-gray-900 rounded-lg overflow-hidden cursor-pointer">
        <div class="plyr__video-embed live-video-player cursor-pointer" id="plyr-{{ $videoId }}">
            <iframe
                class="youtube-component-iframe"
                src="https://www.youtube-nocookie.com/embed/{{ $videoId }}?iv_load_policy=3&modestbranding=1&playsinline=1&showinfo=0&rel=0&enablejsapi=1&controls=0&disablekb=1&fs=0&origin={{ request()->getSchemeAndHttpHost() }}"
                title="{{ $title }}"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                allowfullscreen
                webkitallowfullscreen
                mozallowfullscreen
                referrerpolicy="strict-origin-when-cross-origin"
                frameborder="0"
            ></iframe>
        </div>
    </div>
</div>

<!-- Plyr assets and initialization -->
@once
<link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css">
<script src="https://cdn.plyr.io/3.7.8/plyr.js"></script>
@endonce
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
document.addEventListener('DOMContentLoaded', function() {
    var target = document.getElementById('plyr-{{ $videoId }}');
    if (target && window.Plyr) {
        var player = new Plyr(target, {
            ratio: '16:9',
            autoplay: false,
            clickToPlay: true,
            controls: [
                'play-large',
                'play',
                'progress',
                'current-time',
                'duration',
                'mute',
                'volume',
                'settings',
                'fullscreen'
            ],
            youtube: {
                rel: 0,
                modestbranding: 1,
                iv_load_policy: 3
            }
        });
        window.plyrInstance = player;

        // Kelola state pause/play agar atribusi YouTube tidak bisa diklik dan tetap ter-crop
        var wrapper = target.closest('.youtube-player-wrapper');
        function setPausedUI(isPaused) {
            if (!wrapper) return;
            var iframeEl = wrapper.querySelector('iframe');
            if (isPaused) {
                wrapper.classList.add('paused');
                if (iframeEl) iframeEl.style.pointerEvents = 'none';
            } else {
                wrapper.classList.remove('paused');
                if (iframeEl) iframeEl.style.pointerEvents = 'auto';
            }
        }

        // Paksa kualitas video YouTube ke 1080p (fallback ke 720p jika tidak tersedia)
        function setPreferredQuality(p, quality) {
            try {
                var embed = p && p.embed ? p.embed : null; // Instance YT.Player dari Plyr
                var desired = quality || 'hd1080';
                if (embed && typeof embed.getAvailableQualityLevels === 'function') {
                    var levels = embed.getAvailableQualityLevels();
                    if (Array.isArray(levels) && levels.length) {
                        var selected = levels.includes(desired)
                            ? desired
                            : (levels.includes('hd720') ? 'hd720' : null);
                        if (selected && typeof embed.setPlaybackQuality === 'function') {
                            embed.setPlaybackQuality(selected);
                            // Persist jika API mendukung range
                            if (typeof embed.setPlaybackQualityRange === 'function') {
                                embed.setPlaybackQualityRange(selected);
                            }
                        }
                    }
                }
            } catch (e) {
                console.warn('Gagal set kualitas YouTube melalui Plyr:', e);
            }
        }

        // Event listeners
        player.on('ready', function() { 
            setPausedUI(true);
            // Coba set kualitas saat player siap
            setPreferredQuality(player, 'hd1080');
        });
        player.on('pause', function() { setPausedUI(true); });
        player.on('play', function() { 
            setPausedUI(false);
            // Fallback: beberapa video hanya menyediakan daftar kualitas setelah mulai diputar
            setPreferredQuality(player, 'hd1080');
        });
        player.on('ended', function() { setPausedUI(true); });
    }
});
</script>

 

@push('after-styles')
<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    /* Ensure the player is fully interactive */
    .youtube-player-wrapper,
    .youtube-player-wrapper iframe {
        pointer-events: auto;
    }

    .youtube-player-container {
        position: relative;
        background: transparent;
    }

    .youtube-player-wrapper {
        position: relative;
        background: #000;
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        /* Fallback: enforce 16:9 ratio even if Tailwind aspect-video is purged */
        aspect-ratio: 16 / 9;
    }

    /* Masking overlay: sembunyikan title & tombol YouTube saat paused */
    .youtube-player-wrapper.paused::before,
    .live-video-player.paused::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: clamp(96px, 12vw, 140px); /* lebih tinggi untuk menutup title/tombol */
        background: linear-gradient(to bottom, rgba(0,0,0,0.98) 0%, rgba(0,0,0,0.75) 60%, rgba(0,0,0,0) 100%);
        z-index: 9999;
        pointer-events: none;
    }

    /* Mask watermark kanan bawah saat paused */
    .youtube-player-wrapper.paused::after,
    .live-video-player.paused::after {
        content: "";
        position: absolute;
        right: 0;
        bottom: 0;
        width: clamp(180px, 20vw, 240px);
        height: clamp(120px, 12vw, 160px);
        background: linear-gradient(135deg, rgba(0,0,0,0.92) 0%, rgba(0,0,0,0.7) 60%, rgba(0,0,0,0) 100%);
        z-index: 9999;
        pointer-events: none;
    }

    /* Crop/zoom prioritas tinggi: target struktur embed Plyr & TipTap */
    .youtube-player-wrapper .plyr--youtube .plyr__video-embed iframe,
    .live-video-player .plyr--youtube iframe {
        position: absolute !important;
        top: -55% !important;
        left: 0 !important;
        width: 100% !important;
        height: 210% !important;
        transform: translateY(-3%) scale(1.10) !important;
        transform-origin: center top !important;
    }
    /* Varian layar lebar: sedikit kurang zoom untuk menjaga komposisi */
    @media (min-width: 1280px) {
        .youtube-player-wrapper .plyr--youtube .plyr__video-embed iframe,
        .live-video-player .plyr--youtube iframe {
            top: -52% !important;
            height: 206% !important;
            transform: translateY(-2%) scale(1.08) !important;
        }
    }
    /* Saat paused/end, lakukan crop lebih agresif dan nonaktifkan interaksi iframe */
    .youtube-player-wrapper.paused .plyr--youtube .plyr__video-embed iframe,
    .live-video-player.paused .plyr--youtube iframe {
        top: -70% !important;
        height: 240% !important;
        transform: translateY(-8%) scale(1.15) !important;
        transform-origin: center top !important;
        pointer-events: none !important;
    }
      .live-video-player
        .plyr--youtube.plyr--paused.plyr--loading.plyr__poster-enabled
        .plyr__poster {
        opacity: 1 !important;
     }

    /* Hilangkan background abu dari Plyr */
    .plyr__video-wrapper {
        background-color: transparent !important;
    }

    /* Fallback sizing ketika CSS Plyr tidak tersedia (akan ditimpa oleh aturan crop di atas) */
    .youtube-player-wrapper .plyr__video-embed {
        position: relative;
        width: 100%;
        height: 100%;
    }
    .youtube-player-wrapper .plyr__video-embed iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }
    
    .youtube-player-wrapper iframe {
        border: none;
        border-radius: 0.5rem;
    }
</style>
@endpush
@endif
