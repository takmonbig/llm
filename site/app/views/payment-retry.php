<section class="section"><div class="wrap form-narrow card">
  <h1>Complete payment</h1>
  <p>Order <?= e($order['order_number']) ?> · <?= money($order['total']) ?></p>
  <button class="btn btn-block" id="rzp-pay" data-testid="razorpay-pay-btn">Pay now</button>
</div></section>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
document.getElementById('rzp-pay').onclick = function () {
  var o = <?= json_encode($rz, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?>;
  o.handler = function (r) {
    fetch('<?= url('payment/razorpay/verify') ?>', {method: 'POST', headers: {'Content-Type': 'application/json', 'X-CSRF-Token': '<?= e(csrf_token()) ?>'}, body: JSON.stringify(r)})
      .then(function (x) { return x.json(); }).then(function (d) { if (d.redirect) location.href = d.redirect; else alert(d.error); });
  };
  new Razorpay(o).open();
};
</script>
