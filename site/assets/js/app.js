// Stand-in script for preview — original script was not included in the zip.
document.addEventListener('DOMContentLoaded', function () {
  var toggle = document.querySelector('[data-menu-toggle]');
  var nav = document.querySelector('[data-nav]');
  if (toggle && nav) toggle.addEventListener('click', function () { nav.classList.toggle('open'); });

  var slides = document.querySelectorAll('.hero-slide');
  var dots = document.querySelectorAll('.hero-dots button');
  if (slides.length > 1) {
    var i = 0;
    var show = function (n) {
      slides[i].classList.remove('active'); dots[i] && dots[i].classList.remove('active');
      i = (n + slides.length) % slides.length;
      slides[i].classList.add('active'); dots[i] && dots[i].classList.add('active');
    };
    dots.forEach(function (d, n) { d.addEventListener('click', function () { show(n); }); });
    setInterval(function () { show(i + 1); }, 5000);
  }

  var main = document.querySelector('[data-gallery-main]');
  document.querySelectorAll('[data-thumb]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      document.querySelectorAll('[data-thumb]').forEach(function (b) { b.classList.remove('active'); });
      btn.classList.add('active');
      var src = btn.getAttribute('data-src');
      main.innerHTML = btn.getAttribute('data-type') === 'video'
        ? '<video src="' + src + '" controls autoplay muted playsinline></video>'
        : '<img src="' + src + '" alt="' + (btn.getAttribute('data-alt') || '') + '">';
    });
  });
});
