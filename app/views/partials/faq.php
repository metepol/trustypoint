<div class="faq">
  <?php foreach ($faqs as $f): ?>
    <details class="faq-item">
      <summary><?= e($f['question']) ?><span class="faq-icon" aria-hidden="true">+</span></summary>
      <div class="faq-a"><?= nl2br(e($f['answer'])) ?></div>
    </details>
  <?php endforeach; ?>
</div>
