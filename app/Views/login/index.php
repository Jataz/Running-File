<style>
  .login-wrap{display:flex;justify-content:center;align-items:center;min-height:60vh;padding:12px}
  .login-card{display:grid;gap:12px;width:380px;background:var(--panel);border:1px solid var(--border);border-radius:14px;padding:18px;box-shadow:var(--shadow)}
  .login-title{display:flex;align-items:center;gap:10px;font-weight:800;font-size:20px;color:var(--text)}
  .login-sub{color:var(--muted);font-size:13px;margin-top:-6px}
  .field{display:grid;gap:6px}
  .field label{color:#134e75;font-weight:700}
  .field input{border:1px solid var(--border);border-radius:10px;padding:10px;background:#fff;color:#0b1b2b}
  .actions{display:flex;align-items:center;gap:8px}
  .actions .btn{flex:1}
  .muted-link{color:#3b82f6;text-decoration:none}
  .msg{display:none}
  .msg.error{display:inline-block;background:#fee2e2;border-color:#fecaca;color:#7f1d1d}
  .msg.ok{display:inline-block}
</style>

<div class="login-wrap">
  <form id="loginForm" class="login-card" autocomplete="on">
    <div class="login-title">🔐 Sign in</div>
    <div class="login-sub">Access your department’s running files and documents.</div>
    <div class="field">
      <label for="username">Username</label>
      <input id="username" name="username" required placeholder="e.g. admin" />
    </div>
    <div class="field">
      <label for="password">Password</label>
      <div style="display:flex;gap:8px;align-items:center">
        <input id="password" type="password" name="password" required placeholder="••••••" style="flex:1" />
        <button type="button" class="btn" id="togglePwd" aria-label="Show password">Show</button>
      </div>
    </div>
    <div id="loginMsg" class="badge msg" aria-live="polite"></div>
    <div class="actions">
      <button class="btn primary" id="loginBtn" type="submit">Login</button>
    </div>
  </form>
  
</div>
<script>
  const form = document.getElementById('loginForm');
  const msg = document.getElementById('loginMsg');
  const btn = document.getElementById('loginBtn');
  const pwd = document.getElementById('password');
  const toggle = document.getElementById('togglePwd');

  toggle.addEventListener('click', () => {
    const isPw = pwd.type === 'password';
    pwd.type = isPw ? 'text' : 'password';
    toggle.textContent = isPw ? 'Hide' : 'Show';
  });

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    msg.className = 'badge msg';
    msg.style.display = 'none';
    btn.disabled = true; btn.textContent = 'Signing in…';
    const fd = new FormData(form);
    let data = null;
    try {
      const res = await fetch('/auth.php?action=login', { method: 'POST', body: fd });
      data = await res.json();
    } catch (err) {
      msg.textContent = 'Server error'; msg.classList.add('error'); msg.style.display = 'inline-block';
      btn.disabled = false; btn.textContent = 'Login';
      return;
    }
    if (data && data.ok) {
      msg.textContent = 'Welcome! Redirecting…'; msg.classList.add('ok'); msg.style.display = 'inline-block';
      window.location.href = '/';
    } else {
      const code = data && data.error ? data.error : 'login_failed';
      const map = {
        invalid_credentials: 'Invalid username or password',
        inactive: 'Your account is disabled. Contact admin.',
        class_mismatch: 'Selected class does not match your profile',
        department_restricted: 'Department access restricted',
        bad_request: 'Please provide username and password',
        db: 'Database error. Try again later',
        login_failed: 'Login failed',
      };
      msg.textContent = map[code] || 'Login failed';
      msg.classList.add('error');
      msg.style.display = 'inline-block';
      btn.disabled = false; btn.textContent = 'Login';
    }
  });
  
  // Focus username on load
  document.getElementById('username').focus();
</script>