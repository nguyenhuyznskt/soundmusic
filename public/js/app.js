(() => {
    'use strict';

    const safeJson = (value, fallback = null) => {
        try { return JSON.parse(value); } catch { return fallback; }
    };

    const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

    const formatTime = (seconds) => {
        if (!Number.isFinite(seconds) || seconds < 0) return '0:00';
        const m = Math.floor(seconds / 60);
        const s = Math.floor(seconds % 60);
        return `${m}:${String(s).padStart(2, '0')}`;
    };

    class Player {
        constructor() {
            this.audio = document.getElementById('globalAudio');
            this.root = document.getElementById('globalPlayer');
            if (!this.audio || !this.root) return;

            this.queue = safeJson(localStorage.getItem('cloudmusic.queue'), []);
            this.index = Number(localStorage.getItem('cloudmusic.index') ?? -1);
            this.current = null;
            this.countTimer = null;
            this.countedSongId = null;
            this.shuffle = localStorage.getItem('cloudmusic.shuffle') === '1';
            this.repeat = localStorage.getItem('cloudmusic.repeat') || 'off';

            this.cacheElements();
            this.bind();
            this.updateModeButtons();
            this.restore();
        }

        cacheElements() {
            this.cover = this.root.querySelector('[data-player-cover]');
            this.title = this.root.querySelector('[data-player-title]');
            this.artist = this.root.querySelector('[data-player-artist]');
            this.playButton = this.root.querySelector('[data-player-play]');
            this.progress = this.root.querySelector('[data-player-progress]');
            this.currentTime = this.root.querySelector('[data-player-current]');
            this.duration = this.root.querySelector('[data-player-duration]');
            this.volume = this.root.querySelector('[data-player-volume]');
            this.queueList = document.getElementById('playerQueueList');
        }

        bind() {
            // Event delegation: các nút bài hát mới sau khi chuyển trang vẫn hoạt động.
            document.addEventListener('click', (event) => {
                const button = event.target.closest('[data-play-song]');
                if (!button) return;

                event.preventDefault();
                const song = safeJson(button.dataset.song);
                if (!song) return;

                const container = button.closest('[data-song-collection]');
                let collection = [];
                if (container) {
                    collection = [...container.querySelectorAll('[data-play-song]')]
                        .map((element) => safeJson(element.dataset.song))
                        .filter(Boolean);
                }

                this.playSong(song, collection.length ? collection : null);
            });

            this.playButton?.addEventListener('click', () => this.toggle());
            this.root.querySelector('[data-player-prev]')?.addEventListener('click', () => this.previous());
            this.root.querySelector('[data-player-next]')?.addEventListener('click', () => this.next());
            this.root.querySelector('[data-player-shuffle]')?.addEventListener('click', () => {
                this.shuffle = !this.shuffle;
                localStorage.setItem('cloudmusic.shuffle', this.shuffle ? '1' : '0');
                this.updateModeButtons();
            });
            this.root.querySelector('[data-player-repeat]')?.addEventListener('click', () => {
                this.repeat = this.repeat === 'off' ? 'all' : this.repeat === 'all' ? 'one' : 'off';
                localStorage.setItem('cloudmusic.repeat', this.repeat);
                this.updateModeButtons();
            });
            this.progress?.addEventListener('input', () => {
                if (Number.isFinite(this.audio.duration)) {
                    this.audio.currentTime = (Number(this.progress.value) / 1000) * this.audio.duration;
                }
            });
            this.volume?.addEventListener('input', () => {
                this.audio.volume = Number(this.volume.value);
                localStorage.setItem('cloudmusic.volume', String(this.audio.volume));
            });

            this.audio.volume = Number(localStorage.getItem('cloudmusic.volume') ?? .75);
            if (this.volume) this.volume.value = String(this.audio.volume);

            this.audio.addEventListener('play', () => {
                this.updatePlayIcon(true);
                this.scheduleCount();
            });
            this.audio.addEventListener('pause', () => this.updatePlayIcon(false));
            this.audio.addEventListener('timeupdate', () => {
                if (this.progress && Number.isFinite(this.audio.duration) && this.audio.duration > 0) {
                    this.progress.value = String((this.audio.currentTime / this.audio.duration) * 1000);
                }
                if (this.currentTime) this.currentTime.textContent = formatTime(this.audio.currentTime);
                this.persistPosition();
            });
            this.audio.addEventListener('loadedmetadata', () => {
                if (this.duration) this.duration.textContent = formatTime(this.audio.duration);
            });
            this.audio.addEventListener('ended', () => {
                if (this.repeat === 'one') {
                    this.audio.currentTime = 0;
                    this.audio.play();
                } else {
                    this.next();
                }
            });
            this.audio.addEventListener('error', () => this.toast('Không thể phát file âm thanh này.', 'danger'));
        }

        restore() {
            if (!Array.isArray(this.queue) || !this.queue.length || this.index < 0 || !this.queue[this.index]) return;

            this.load(this.queue[this.index], false);
            const state = safeJson(localStorage.getItem('cloudmusic.state'), {});
            this.audio.addEventListener('loadedmetadata', () => {
                if (
                    state.songId === this.current?.id
                    && state.position > 0
                    && state.position < this.audio.duration - 2
                ) {
                    this.audio.currentTime = state.position;
                }
            }, { once: true });
        }

        playSong(song, collection = null) {
            if (collection?.length) {
                this.queue = collection;
                this.index = Math.max(0, this.queue.findIndex((item) => Number(item.id) === Number(song.id)));
            } else {
                const found = this.queue.findIndex((item) => Number(item.id) === Number(song.id));
                if (found >= 0) {
                    this.index = found;
                } else {
                    this.queue.push(song);
                    this.index = this.queue.length - 1;
                }
            }

            this.saveQueue();
            this.load(song, true);
        }

        load(song, autoplay = true) {
            this.current = song;
            this.countedSongId = null;
            clearTimeout(this.countTimer);

            // Chỉ thay src khi thực sự chọn bài khác, tránh tải lại chính bài đang phát.
            if (this.audio.src !== new URL(song.url, window.location.origin).href) {
                this.audio.src = song.url;
            }

            this.cover.src = song.cover;
            this.cover.alt = song.title;
            this.title.textContent = song.title;
            this.title.href = song.detailUrl;
            this.artist.textContent = song.artist;
            this.artist.href = song.artistUrl;
            this.root.classList.remove('opacity-50');
            this.renderQueue();
            this.updateMediaSession();

            if (autoplay) {
                this.audio.play().catch(() => {
                    this.toast('Trình duyệt đã chặn tự động phát. Hãy bấm nút Play.', 'warning');
                });
            }
        }

        toggle() {
            if (!this.current && this.queue.length) {
                this.load(this.queue[Math.max(this.index, 0)], true);
            } else if (!this.current) {
                this.toast('Hãy chọn một bài hát.', 'info');
            } else {
                this.audio.paused ? this.audio.play() : this.audio.pause();
            }
        }

        previous() {
            if (!this.queue.length) return;
            if (this.audio.currentTime > 5) {
                this.audio.currentTime = 0;
                return;
            }

            this.index = (this.index - 1 + this.queue.length) % this.queue.length;
            this.saveQueue();
            this.load(this.queue[this.index], true);
        }

        next() {
            if (!this.queue.length) return;

            this.index = this.shuffle
                ? Math.floor(Math.random() * this.queue.length)
                : this.index + 1;

            if (this.index >= this.queue.length) {
                if (this.repeat === 'all') {
                    this.index = 0;
                } else {
                    this.index = this.queue.length - 1;
                    this.audio.pause();
                    return;
                }
            }

            this.saveQueue();
            this.load(this.queue[this.index], true);
        }

        updatePlayIcon(playing) {
            if (this.playButton) {
                this.playButton.innerHTML = `<i class="bi bi-${playing ? 'pause' : 'play'}-fill"></i>`;
            }
        }

        updateModeButtons() {
            this.root.querySelector('[data-player-shuffle]')?.classList.toggle('active', this.shuffle);
            const repeat = this.root.querySelector('[data-player-repeat]');
            repeat?.classList.toggle('active', this.repeat !== 'off');
            if (repeat) {
                repeat.innerHTML = `<i class="bi bi-repeat${this.repeat === 'one' ? '-1' : ''}"></i>`;
            }
        }

        saveQueue() {
            if (this.queue.length > 100) {
                const dropped = this.queue.length - 100;
                this.queue = this.queue.slice(-100);
                this.index = Math.max(0, this.index - dropped);
            }

            localStorage.setItem('cloudmusic.queue', JSON.stringify(this.queue));
            localStorage.setItem('cloudmusic.index', String(this.index));
        }

        persistPosition() {
            if (!this.current || Math.floor(this.audio.currentTime) % 3 !== 0) return;
            localStorage.setItem('cloudmusic.state', JSON.stringify({
                songId: this.current.id,
                position: Math.floor(this.audio.currentTime),
            }));
        }

        scheduleCount() {
            if (!this.current || this.countedSongId === this.current.id) return;

            clearTimeout(this.countTimer);
            const threshold = Math.max(3, Math.min(10, (Number(this.current.duration) || 30) * .3));
            const delay = Math.max(.5, threshold - this.audio.currentTime);
            this.countTimer = setTimeout(() => {
                if (!this.audio.paused && this.audio.currentTime >= threshold - 1) {
                    this.registerPlay();
                }
            }, delay * 1000);
        }

        async registerPlay() {
            if (!this.current || this.countedSongId === this.current.id) return;

            this.countedSongId = this.current.id;
            try {
                await fetch(this.current.playUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken(),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ position: Math.floor(this.audio.currentTime) }),
                    credentials: 'same-origin',
                });
            } catch {
                this.countedSongId = null;
            }
        }

        renderQueue() {
            if (!this.queueList) return;

            this.queueList.innerHTML = this.queue.map((song, index) => `
                <button
                    class="list-group-item list-group-item-action bg-transparent text-light border-soft d-flex align-items-center gap-3 ${index === this.index ? 'active' : ''}"
                    data-queue-index="${index}"
                >
                    <img src="${this.escape(song.cover)}" width="44" height="44" class="rounded object-fit-cover" alt="">
                    <span class="text-start min-w-0">
                        <strong class="d-block text-truncate">${this.escape(song.title)}</strong>
                        <small class="text-muted text-truncate d-block">${this.escape(song.artist)}</small>
                    </span>
                </button>
            `).join('');

            this.queueList.querySelectorAll('[data-queue-index]').forEach((element) => {
                element.addEventListener('click', () => {
                    this.index = Number(element.dataset.queueIndex);
                    this.saveQueue();
                    this.load(this.queue[this.index], true);
                });
            });
        }

        updateMediaSession() {
            if (!('mediaSession' in navigator) || !this.current) return;

            navigator.mediaSession.metadata = new MediaMetadata({
                title: this.current.title,
                artist: this.current.artist,
                artwork: [{ src: this.current.cover }],
            });
            navigator.mediaSession.setActionHandler('play', () => this.audio.play());
            navigator.mediaSession.setActionHandler('pause', () => this.audio.pause());
            navigator.mediaSession.setActionHandler('previoustrack', () => this.previous());
            navigator.mediaSession.setActionHandler('nexttrack', () => this.next());
        }

        escape(value) {
            const div = document.createElement('div');
            div.textContent = String(value ?? '');
            return div.innerHTML;
        }

        toast(message, type = 'info') {
            window.CloudToast?.(message, type);
        }
    }

    /**
     * Điều hướng kiểu SPA/PJAX.
     * Chỉ thay navbar, sidebar và main; global player + thẻ audio được giữ nguyên.
     */
    class CloudNavigator {
        constructor() {
            this.controller = null;
            this.bind();
            history.replaceState({ cloudMusic: true }, '', window.location.href);
        }

        bind() {
            document.addEventListener('click', (event) => {
                const link = event.target.closest('a[href]');
                if (!this.shouldHandleLink(link, event)) return;

                event.preventDefault();
                this.visit(link.href, { push: true });
            });

            document.addEventListener('submit', (event) => {
                if (event.defaultPrevented) return;

                const form = event.target.closest('form');
                if (!form || form.dataset.noNavigate !== undefined) return;
                if ((form.method || 'get').toLowerCase() !== 'get') return;
                if (form.target && form.target !== '_self') return;

                const url = new URL(form.action || window.location.href, window.location.href);
                if (url.origin !== window.location.origin) return;

                event.preventDefault();
                const data = new FormData(form, event.submitter || undefined);
                url.search = new URLSearchParams(data).toString();
                this.visit(url.href, { push: true });
            });

            window.addEventListener('popstate', () => {
                this.visit(window.location.href, { push: false, scroll: false });
            });
        }

        shouldHandleLink(link, event) {
            if (!link || event.defaultPrevented) return false;
            if (event.button !== 0) return false;
            if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return false;
            if (link.dataset.noNavigate !== undefined) return false;
            if (link.hasAttribute('download')) return false;
            if (link.target && link.target !== '_self') return false;

            const href = link.getAttribute('href');
            if (!href || href.startsWith('#')) return false;
            if (/^(mailto:|tel:|javascript:)/i.test(href)) return false;

            const url = new URL(link.href, window.location.href);
            if (url.origin !== window.location.origin) return false;
            if (!['http:', 'https:'].includes(url.protocol)) return false;

            // Link chỉ đổi hash trong cùng một trang dùng hành vi mặc định của trình duyệt.
            const current = new URL(window.location.href);
            if (
                url.pathname === current.pathname
                && url.search === current.search
                && url.hash
            ) {
                return false;
            }

            return true;
        }

        async visit(url, options = {}) {
            const { push = true, scroll = true } = options;
            const requestedUrl = new URL(url, window.location.href);

            this.controller?.abort();
            this.controller = new AbortController();
            const activeController = this.controller;
            document.body.classList.add('cloud-navigating');

            try {
                const response = await fetch(requestedUrl.href, {
                    method: 'GET',
                    headers: {
                        'Accept': 'text/html, application/xhtml+xml',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CloudMusic-Navigate': '1',
                    },
                    credentials: 'same-origin',
                    signal: activeController.signal,
                });

                if (!response.ok) {
                    window.location.assign(requestedUrl.href);
                    return;
                }

                const html = await response.text();
                const nextDocument = new DOMParser().parseFromString(html, 'text/html');

                if (!this.canSwap(nextDocument)) {
                    window.location.assign(response.url || requestedUrl.href);
                    return;
                }

                this.swap(nextDocument);

                const finalUrl = response.url || requestedUrl.href;
                if (push) {
                    history.pushState({ cloudMusic: true }, '', finalUrl);
                }

                if (scroll) window.scrollTo(0, 0);
                initPageFeatures();

                document.dispatchEvent(new CustomEvent('cloudmusic:navigated', {
                    detail: { url: finalUrl },
                }));
            } catch (error) {
                if (error.name !== 'AbortError') {
                    window.location.assign(requestedUrl.href);
                }
            } finally {
                if (this.controller === activeController) {
                    document.body.classList.remove('cloud-navigating');
                }
            }
        }

        canSwap(nextDocument) {
            return Boolean(
                nextDocument.getElementById('appNavbar')
                && nextDocument.getElementById('appSidebar')
                && nextDocument.getElementById('appMain')
            );
        }

        swap(nextDocument) {
            ['appNavbar', 'appSidebar', 'appMain'].forEach((id) => {
                const current = document.getElementById(id);
                const next = nextDocument.getElementById(id);
                current.replaceWith(document.importNode(next, true));
            });

            document.title = nextDocument.title;

            const nextCsrf = nextDocument.querySelector('meta[name="csrf-token"]')?.content;
            const currentCsrf = document.querySelector('meta[name="csrf-token"]');
            if (nextCsrf && currentCsrf) currentCsrf.content = nextCsrf;

            // Không thay body hoàn toàn vì player đang nằm trong body hiện tại.
            const navigating = document.body.classList.contains('cloud-navigating');
            document.body.className = nextDocument.body.className;
            if (navigating) document.body.classList.add('cloud-navigating');
        }
    }

    window.CloudToast = (message, type = 'info') => {
        const host = document.getElementById('toastHost');
        if (!host) return;

        const element = document.createElement('div');
        element.className = `toast align-items-center text-bg-${type} border-0`;
        element.setAttribute('role', 'alert');
        element.innerHTML = `
            <div class="d-flex">
                <div class="toast-body"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        element.querySelector('.toast-body').textContent = message;
        host.appendChild(element);

        const toast = new bootstrap.Toast(element, { delay: 3500 });
        toast.show();
        element.addEventListener('hidden.bs.toast', () => element.remove());
    };

    function bindAudioMetadataInput() {
        const audioInput = document.querySelector('[data-audio-input]');
        if (!audioInput || audioInput.dataset.cloudBound === '1') return;

        audioInput.dataset.cloudBound = '1';
        audioInput.addEventListener('change', () => {
            const file = audioInput.files?.[0];
            if (!file) return;

            const durationInput = document.querySelector('[name="duration_seconds"]');
            const durationLabel = document.querySelector('[data-audio-duration]');
            const audio = document.createElement('audio');
            audio.preload = 'metadata';

            audio.onloadedmetadata = () => {
                URL.revokeObjectURL(audio.src);
                const duration = Math.ceil(audio.duration);
                if (durationInput) durationInput.value = String(duration);
                if (durationLabel) durationLabel.textContent = `Thời lượng: ${formatTime(duration)}`;
            };
            audio.onerror = () => {
                if (durationLabel) durationLabel.textContent = 'Không đọc được thời lượng file.';
            };
            audio.src = URL.createObjectURL(file);
        });
    }

    function initPageFeatures() {
        bindAudioMetadataInput();
    }

    // Confirm dùng event delegation để cả form mới tải bằng SPA vẫn hoạt động.
    document.addEventListener('submit', (event) => {
        const form = event.target.closest('form[data-confirm]');
        if (!form) return;

        if (!confirm(form.dataset.confirm || 'Bạn chắc chắn chứ?')) {
            event.preventDefault();
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        window.CloudPlayer = new Player();
        window.CloudNavigator = new CloudNavigator();
        initPageFeatures();
    });
})();
