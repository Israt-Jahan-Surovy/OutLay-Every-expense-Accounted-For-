(() => {
  const show = el => { if (el) el.hidden = false; };
  const hide = el => { if (el) el.hidden = true; };

  document.querySelectorAll('[data-open-modal]').forEach(btn => {
    btn.addEventListener('click', () => show(document.getElementById(btn.dataset.openModal)));
  });
  document.querySelectorAll('[data-close-modal]').forEach(btn => {
    btn.addEventListener('click', () => hide(btn.closest('.modal-backdrop')));
  });
  document.querySelectorAll('.modal-backdrop').forEach(modal => {
    modal.addEventListener('click', e => { if (e.target === modal && modal.id !== 'logoutModal') hide(modal); });
  });

  const logout = document.getElementById('logoutModal');
  document.querySelectorAll('[data-open-logout]').forEach(btn => btn.addEventListener('click', () => show(logout)));
  document.querySelectorAll('[data-close-logout]').forEach(btn => btn.addEventListener('click', () => hide(logout)));

  document.querySelectorAll('form[data-confirm]').forEach(form => {
    form.addEventListener('submit', e => {
      if (!window.confirm(form.dataset.confirm || 'Are you sure?')) e.preventDefault();
    });
  });

  const toast = document.querySelector('.toast');
  if (toast) setTimeout(() => toast.remove(), 4200);

  // Mobile sidebar toggle (sidebar is hidden off-screen under 720px width)
  const sidebar = document.getElementById('adminSidebar');
  const toggleBtn = document.getElementById('sidebarToggle');
  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener('click', () => sidebar.classList.toggle('open'));
  }
})();
