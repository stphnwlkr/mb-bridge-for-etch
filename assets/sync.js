/* Refresh only saved lists through Etch's public API. Never reload the canvas. */
(() => {
  'use strict';
  const config = window.mbfeConfig;
  if (!config?.changesUrl) return;
  let seen = config.changes || {}, running = false;
  function warn() {
    document.dispatchEvent(new CustomEvent('mbfe:sync-status', { detail: 'Changes saved. Reload Etch to see all changes in Templates, Content Hub, and the preview. Save your Etch layout and Meta Box changes first.' }));
  }
  async function check() {
    if (running || document.hidden) return;
    const navigation = window.etch?.navigation;
    running = true;
    try {
      const response = await fetch(config.changesUrl, {
        credentials: 'same-origin', cache: 'no-store', headers: { 'X-WP-Nonce': config.restNonce },
      });
      if (!response.ok) throw new Error('Change check failed');
      const changes = await response.json();
      for (const [type, token] of Object.entries(changes)) {
        if (seen[type] === token) continue;
        warn();
        if (type === 'schema') {
          // Etch currently keeps these definitions in private stores. Do not patch its bundle.
          // The warning remains visible until the builder is reloaded.
        } else if (type === 'wp_template' || type === 'wp_template_part') {
          if (typeof navigation?.listTemplatesAsync !== 'function') continue;
          await navigation.listTemplatesAsync();
        } else {
          if (typeof navigation?.listPostsAsync !== 'function') continue;
          await navigation.listPostsAsync(type);
        }
        seen[type] = token;
      }
    } catch {
      // Keep unacknowledged tokens so the next check retries a failed refresh.
    } finally {
      running = false;
    }
  }
  window.mbfeSync = { check, warn };
  window.addEventListener('focus', check);
  document.addEventListener('visibilitychange', check);
  setInterval(check, 15000);
})();
