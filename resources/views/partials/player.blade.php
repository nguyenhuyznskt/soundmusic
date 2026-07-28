<div class="cm-player opacity-50" id="globalPlayer">
    <div class="d-flex align-items-center gap-3 min-w-0">
        <img data-player-cover class="cm-player-cover" src="{{ asset('images/default-cover.svg') }}" alt="Ảnh bài hát">
        <div class="min-w-0">
            <a data-player-title href="#" class="d-block fw-bold text-truncate">Chưa chọn bài hát</a>
            <a data-player-artist href="#" class="d-block small text-muted text-truncate">CloudMusic</a>
        </div>
    </div>
    <div class="cm-player-center">
        <div class="cm-controls mb-1">
            <button class="cm-control secondary-control" data-player-shuffle title="Phát ngẫu nhiên"><i class="bi bi-shuffle"></i></button>
            <button class="cm-control" data-player-prev title="Bài trước"><i class="bi bi-skip-start-fill"></i></button>
            <button class="cm-control cm-control-main" data-player-play title="Phát/Tạm dừng"><i class="bi bi-play-fill"></i></button>
            <button class="cm-control" data-player-next title="Bài tiếp"><i class="bi bi-skip-end-fill"></i></button>
            <button class="cm-control secondary-control" data-player-repeat title="Lặp lại"><i class="bi bi-repeat"></i></button>
        </div>
        <div class="cm-progress-row"><span data-player-current>0:00</span><input data-player-progress class="cm-range" type="range" min="0" max="1000" value="0"><span data-player-duration>0:00</span></div>
    </div>
    <div class="cm-player-right">
        <i class="bi bi-volume-up text-muted"></i><input data-player-volume class="cm-range" style="max-width:110px" type="range" min="0" max="1" step="0.01" value="0.75">
        <button class="cm-control" data-bs-toggle="offcanvas" data-bs-target="#queueOffcanvas" title="Hàng chờ"><i class="bi bi-list-ul"></i></button>
    </div>
    <audio id="globalAudio" preload="metadata"></audio>
</div>
<div class="offcanvas offcanvas-end" tabindex="-1" id="queueOffcanvas">
    <div class="offcanvas-header"><h5 class="offcanvas-title">Hàng chờ phát nhạc</h5><button class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button></div>
    <div class="offcanvas-body"><div class="list-group list-group-flush" id="playerQueueList"><p class="text-muted">Chưa có bài hát trong hàng chờ.</p></div></div>
</div>
