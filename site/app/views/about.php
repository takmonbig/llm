<section class="section"><div class="wrap grid-2">
  <div>
    <div class="eyebrow">Our story</div>
    <h1 data-testid="about-title">About <?= e(setting('store_name')) ?></h1>
    <div class="prose" data-testid="about-text"><?= nl2p(setting('about_text')) ?></div>
    <a class="btn" href="<?= url('shop') ?>" data-testid="about-shop-btn">Shop the combo</a>
  </div>
  <img src="<?= url('assets/images/gallery/global-inspiration.jpg') ?>" alt="Global inspiration, everyday wellness" style="border-radius:16px">
</div></section>
