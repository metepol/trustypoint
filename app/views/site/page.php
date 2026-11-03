<?php App\View::partial('page-hero', ['title' => $page['title']]); ?>
<section class="section pt-0"><div class="container narrow"><div class="prose"><?= $page['content'] ?></div>
<p class="tiny muted mt-3">Son güncelleme: <?= e(tr_date($page['updated_at'], false)) ?></p></div></section>
