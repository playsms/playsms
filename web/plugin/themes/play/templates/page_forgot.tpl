{ERROR}
<div class="modal" style="width: 55%; position: relative; top: auto; left: auto; margin: 0pt auto; z-index: 1;">
  <form action="index.php" method="post">
    <div class="modal-header">
      <h3>{Forgot password}</h3>
    </div>
    <div class="modal-body">
        <input type="hidden" name="app" value="page" />
        <input type="hidden" name="inc" value="forgot" />
        <input type="hidden" name="op" value="auth_forgot" />
        <div class="clearfix">
          <label for="username">{Username}</label>
          <div class="input">
            <input type="text" size=30 name="username" id="username" class="medium">
          </div>
        </div>
        <div class="clearfix">
          <label for="email">{Email}</label>
          <div class="input">
            <input type="text" size=30 name="email" id="email" class="medium">
          </div>
        </div>
        <div class="actions">
        <input type="submit" value="{Recover password}" class="btn primary">
        <input type="reset" value="Cancel" class="btn">
        </div>
    </div>
    <div>
      <ul>
      <li><a href="index.php?app=page&inc=login">{Login}</a></li>
      <if.enable_register>
      <li><a class="small" href="index.php?app=page&inc=register">{Register an account}</a></li>
      </if.enable_register>
      </ul>
    </div>
  </form>
</div>
