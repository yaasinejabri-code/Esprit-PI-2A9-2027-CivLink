(function () {
  'use strict';
  const windowEl = document.getElementById('civFaqWindow');
  const toggle = document.getElementById('civFaqToggle');
  const close = document.getElementById('civFaqClose');
  const input = document.getElementById('cfaqInput');
  if (!windowEl || !toggle || !close || !input) return;
  function setOpen(open) {
    windowEl.hidden = !open;
    toggle.setAttribute('aria-expanded', String(open));
    toggle.setAttribute('aria-label', open ? 'Masquer l’assistant CIVLINK' : 'Ouvrir l’assistant CIVLINK');
    if (open) input.focus(); else toggle.focus();
  }
  toggle.addEventListener('click', () => setOpen(windowEl.hidden));
  close.addEventListener('click', () => setOpen(false));
  document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && !windowEl.hidden) setOpen(false);
  });
})();
