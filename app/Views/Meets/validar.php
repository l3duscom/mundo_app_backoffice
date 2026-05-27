<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<style>
    .mv-wrap { max-width: 720px; margin: 0 auto; }
    .mv-card {
        background: #1a1d29;
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 18px;
    }
    .mv-title { color: #fff; font-weight: 700; margin: 0 0 16px; }
    #mv-reader { width: 100%; border-radius: 10px; overflow: hidden; background: #000; }
    #mv-reader video { width: 100% !important; height: auto !important; }
    .mv-actions { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 12px; }
    .mv-actions .btn { flex: 1; min-width: 140px; }
    .mv-input-code {
        background: #0f1119;
        border: 1px solid rgba(255,255,255,0.15);
        color: #fff;
        padding: 10px 12px;
        border-radius: 8px;
        width: 100%;
        font-size: 1.05rem;
        letter-spacing: 2px;
        text-transform: uppercase;
    }
    .mv-result { padding: 14px; border-radius: 10px; margin-top: 6px; }
    .mv-result.ok { background: rgba(40, 167, 69, 0.15); border: 1px solid #28a745; color: #b4ff48; }
    .mv-result.warn { background: rgba(255, 193, 7, 0.12); border: 1px solid #ffc107; color: #ffc107; }
    .mv-result.err { background: rgba(220, 53, 69, 0.15); border: 1px solid #dc3545; color: #ff7980; }
    .mv-result h5 { margin: 0 0 8px; font-weight: 700; }
    .mv-result .mv-row { display: flex; justify-content: space-between; gap: 12px; padding: 4px 0; border-bottom: 1px dashed rgba(255,255,255,0.08); font-size: .92rem; }
    .mv-result .mv-row:last-child { border-bottom: 0; }
    .mv-result .mv-label { color: rgba(255,255,255,0.65); }
    .mv-result .mv-val { color: #fff; font-weight: 600; text-align: right; }
    .mv-ordem-big { font-size: 2.4rem; font-weight: 800; color: #b4ff48; text-align: center; margin: 6px 0 4px; }
    .mv-ordem-big small { font-size: .9rem; display: block; color: rgba(255,255,255,0.6); font-weight: 400; letter-spacing: 1px; }
    .mv-hint { color: rgba(255,255,255,0.5); font-size: .85rem; margin-top: 8px; }
    .mv-pill { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; }
    .mv-pill.vip   { background: #6c5ce7; color: #fff; }
    .mv-pill.epic  { background: #e84393; color: #fff; }
    .mv-pill.comum { background: #00b894; color: #fff; }
</style>
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>
<div class="row">
    <div class="col-12">
        <div class="mv-wrap">
            <h3 class="mv-title"><i class="bx bx-qr-scan"></i> Validar Meet &amp; Greet</h3>

            <div class="mv-card">
                <div id="mv-reader"></div>
                <div class="mv-actions">
                    <button type="button" id="mv-start" class="btn btn-success"><i class="bx bx-camera"></i> Iniciar câmera</button>
                    <button type="button" id="mv-stop" class="btn btn-secondary" style="display:none;"><i class="bx bx-stop-circle"></i> Parar</button>
                </div>
                <p class="mv-hint">Aponte a câmera para o QR Code do ingresso de Meet &amp; Greet do participante.</p>
            </div>

            <div class="mv-card">
                <h6 style="color:#fff; margin-bottom:10px;">Ou digite o código manualmente:</h6>
                <form id="mv-form" autocomplete="off">
                    <input type="text" id="mv-code" class="mv-input-code" maxlength="20" placeholder="EX.: AB12CD34EF">
                    <div class="mv-actions">
                        <button type="submit" class="btn btn-primary"><i class="bx bx-check"></i> Validar</button>
                        <button type="button" id="mv-clear" class="btn btn-outline-secondary">Limpar</button>
                    </div>
                </form>
            </div>

            <div id="mv-result"></div>
        </div>
    </div>
</div>
<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<script src="https://unpkg.com/html5-qrcode@2.3.10/html5-qrcode.min.js"></script>
<script>
    (function () {
        var URL_VALIDAR = '<?= site_url('meets/validar-code') ?>';
        var $form = document.getElementById('mv-form');
        var $code = document.getElementById('mv-code');
        var $clear = document.getElementById('mv-clear');
        var $result = document.getElementById('mv-result');
        var $start = document.getElementById('mv-start');
        var $stop = document.getElementById('mv-stop');

        var qr = null;
        var scanning = false;
        var ultimoCode = null;
        var ultimoTs = 0;

        function tipoPill(tipo) {
            var t = (tipo || '').toLowerCase();
            var cls = t === 'vip' ? 'vip' : (t === 'epic' ? 'epic' : 'comum');
            return '<span class="mv-pill ' + cls + '">' + (tipo || '-') + '</span>';
        }

        function render(json) {
            var d = json.data || {};
            var html = '';
            var classe = 'ok';
            var titulo = 'Validado com sucesso';

            if (!json.success && !json.ja_validado) {
                $result.innerHTML = '<div class="mv-result err"><h5><i class="bx bx-x-circle"></i> ' +
                    (json.message || 'Erro ao validar') + '</h5></div>';
                return;
            }

            if (json.ja_validado) {
                classe = 'warn';
                titulo = 'Já validado anteriormente';
            }

            html += '<div class="mv-result ' + classe + '">';
            html += '<h5><i class="bx bx-check-circle"></i> ' + titulo + '</h5>';
            html += '<div class="mv-ordem-big">' + (d.ordem ? d.ordem + 'º' : '-') + '<small>Posição na fila</small></div>';
            html += '<div class="mv-row"><span class="mv-label">Artista</span><span class="mv-val">' + (d.artista || '-') + '</span></div>';
            html += '<div class="mv-row"><span class="mv-label">Dia / Hora</span><span class="mv-val">' + (d.dia || '-') + ' &middot; ' + (d.hora_inicial || '-') + '</span></div>';
            html += '<div class="mv-row"><span class="mv-label">Tipo</span><span class="mv-val">' + tipoPill(d.tipo) + '</span></div>';
            html += '<div class="mv-row"><span class="mv-label">Participante</span><span class="mv-val">' + (d.usuario_nome || '-') + '</span></div>';
            if (d.usuario_email) html += '<div class="mv-row"><span class="mv-label">E-mail</span><span class="mv-val">' + d.usuario_email + '</span></div>';
            html += '<div class="mv-row"><span class="mv-label">Ingresso</span><span class="mv-val">' + (d.ingresso_nome || '-') + '</span></div>';
            html += '<div class="mv-row"><span class="mv-label">Código</span><span class="mv-val" style="font-family:monospace;">' + (d.code || '-') + '</span></div>';
            html += '</div>';

            $result.innerHTML = html;
        }

        function enviar(code) {
            var fd = new FormData();
            fd.append('code', code);
            fd.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

            fetch(URL_VALIDAR, {
                method: 'POST',
                body: fd,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            })
                .then(function (r) { return r.json(); })
                .then(render)
                .catch(function () {
                    $result.innerHTML = '<div class="mv-result err"><h5>Erro de conexão</h5></div>';
                });
        }

        function tratarCode(raw) {
            var c = (raw || '').toString().trim().toUpperCase();
            if (!c) return;
            var agora = Date.now();
            if (c === ultimoCode && (agora - ultimoTs) < 3000) return;
            ultimoCode = c; ultimoTs = agora;
            $code.value = c;
            enviar(c);
        }

        $form.addEventListener('submit', function (e) {
            e.preventDefault();
            tratarCode($code.value);
        });
        $clear.addEventListener('click', function () {
            $code.value = '';
            $result.innerHTML = '';
            ultimoCode = null;
            $code.focus();
        });

        $start.addEventListener('click', function () {
            if (scanning) return;
            qr = new Html5Qrcode('mv-reader');
            Html5Qrcode.getCameras().then(function (devices) {
                if (!devices || !devices.length) {
                    alert('Nenhuma câmera encontrada.');
                    return;
                }
                var camId = devices[devices.length - 1].id;
                qr.start(camId, { fps: 10, qrbox: { width: 250, height: 250 } },
                    function (decoded) { tratarCode(decoded); },
                    function () {}
                ).then(function () {
                    scanning = true;
                    $start.style.display = 'none';
                    $stop.style.display = 'inline-block';
                }).catch(function (err) { alert('Falha ao iniciar câmera: ' + err); });
            }).catch(function (err) { alert('Erro ao listar câmeras: ' + err); });
        });

        $stop.addEventListener('click', function () {
            if (!scanning || !qr) return;
            qr.stop().then(function () {
                scanning = false;
                $start.style.display = 'inline-block';
                $stop.style.display = 'none';
            });
        });
    })();
</script>
<?php echo $this->endSection() ?>
