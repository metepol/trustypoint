<div class="adm-head">
  <h1 class="adm-title"><?= e($msg['subject']) ?></h1>
  <a href="<?= e(url('yonetim/mesajlar')) ?>" class="btn btn-ghost btn-sm">← Mesajlar</a>
</div>
<div class="card">
  <dl class="dl">
    <dt>Gönderen</dt><dd><?= e($msg['name']) ?></dd>
    <dt>E-posta</dt><dd><a href="mailto:<?= e($msg['email']) ?>?subject=<?= rawurlencode('Re: ' . $msg['subject']) ?>"><?= e($msg['email']) ?></a></dd>
    <dt>Telefon</dt><dd><?= e($msg['phone'] ?: '—') ?></dd>
    <dt>Tarih</dt><dd><?= e(tr_date($msg['created_at'])) ?> · IP <?= e($msg['ip']) ?></dd>
  </dl>
  <div class="msg-body"><?= nl2br(e($msg['body'])) ?></div>
  <div class="btn-row mt-2">
    <a class="btn btn-primary btn-sm" href="mailto:<?= e($msg['email']) ?>?subject=<?= rawurlencode('Re: ' . $msg['subject']) ?>">E-posta ile yanıtla</a>
    <form method="post" data-confirm="Mesaj silinsin mi?"><?= csrf_field() ?><button class="btn btn-danger btn-sm">Sil</button></form>
  </div>
</div>
