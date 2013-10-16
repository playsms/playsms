{ERROR}
<div class="modal" style="width: 55%; position: relative; top: auto; left: auto; margin: 0pt auto; z-index: 1;">
  <form action="index.php" method="post">
    <div class="modal-header">
      <h3>{Login}</h3>
    </div>
    <div class="modal-body">
        <input type="hidden" name="app" value="page" />
        <input type="hidden" name="inc" value="login" />
        <input type="hidden" name="op" value="auth_login" />
        <div class="clearfix">
          <label for="username">{Username}</label>
          <div class="input">
            <input type="text" size=30 name="username" id="username" class="medium">
          </div>
        </div>
        <div class="clearfix">
          <label for="password">{Password}</label>
          <div class="input">
            <input type="password" size=30 name="password" id="password" class="medium">
          </div>
        </div>
        <div class="actions">
        <input type="submit" value="{Submit}" class="btn primary">
        <input type="reset" value="{Cancel}" class="btn">
        </div>
    </div>
    <div>
      <ul>
      <if.enable_register>
      <li><a href="index.php?app=page&inc=register">{Register an account}</a></li>
      </if.enable_register>
      <if.enable_forgot>
      <li><a class="small" href="index.php?app=page&inc=forgot">{Forgot password}</a></li>
      </if.enable_forgot>
      </ul>
    </div>
  </form>
</div>
