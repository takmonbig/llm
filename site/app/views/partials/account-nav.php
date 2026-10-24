<div class="account-nav">
  <a class="btn btn-sm btn-ghost" href="<?= url('account') ?>" data-testid="account-nav-overview">Overview</a>
  <a class="btn btn-sm btn-ghost" href="<?= url('account/orders') ?>" data-testid="account-nav-orders">Orders</a>
  <a class="btn btn-sm btn-ghost" href="<?= url('account/addresses') ?>" data-testid="account-nav-addresses">Addresses</a>
  <form method="post" action="<?= url('logout') ?>"><?= csrf_field() ?><button class="btn btn-sm btn-dark" data-testid="logout-btn">Log out</button></form>
</div>
