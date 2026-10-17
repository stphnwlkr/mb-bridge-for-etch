(() => {
  'use strict';
  const config = window.mbfeConfig;
  if (!config || window.mbfePanel) return;
  const logo = '<svg viewBox="227 227 46 46" width="22" height="22" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><rect x="227" y="227" width="46" height="46" rx="4" ry="4" fill="#111"></rect><path d="M 256.873 255.508 L 257.283 246.088 L 257.193 246.078 L 251.483 261.868 L 248.613 261.868 L 242.933 246.138 L 242.843 246.148 L 243.253 255.508 L 243.253 258.868 L 245.533 259.308 L 245.533 261.868 L 236.703 261.868 L 236.703 259.308 L 238.973 258.868 L 238.973 243.558 L 236.703 243.118 L 236.703 240.538 L 238.973 240.538 L 244.553 240.538 L 249.993 256.258 L 250.073 256.258 L 255.553 240.538 L 263.433 240.538 L 263.433 243.118 L 261.153 243.558 L 261.153 258.868 L 263.433 259.308 L 263.433 261.868 L 254.603 261.868 L 254.603 259.308 L 256.873 258.868 L 256.873 255.508 Z" fill="#fff"></path></svg>';
  const paths = {
    page: '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM14 2v6h6M8 13h8M8 17h5"/>',
    fields: '<rect x="3" y="3" width="18" height="18" rx="3"/><path d="M7 8h10M7 12h10M7 16h6"/>',
    posts: '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 7h8M8 11h8M8 15h4"/>',
    taxonomy: '<path d="M20 13l-7 7a2 2 0 0 1-3 0L2 12V3h9l9 7a2 2 0 0 1 0 3Z"/><circle cx="7" cy="8" r="1"/>',
    relationships: '<path d="m10 13 4-4M8 16l-1 1a4 4 0 0 1-6-6l4-4a4 4 0 0 1 6 0m2 10a4 4 0 0 0 6 0l4-4a4 4 0 0 0-6-6l-1 1"/>',
    settings: '<path d="M4 7h16M4 17h16"/><circle cx="9" cy="7" r="3"/><circle cx="15" cy="17" r="3"/>',
    models: '<ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v14c0 4 16 4 16 0V5M4 12c0 4 16 4 16 0"/>',
    views: '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="M2 9h20M8 9v11"/>',
  };
  function icon(name) {
    return '<svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + (paths[name] || paths.fields) + '</svg>';
  }
  function el(tag, className, text) {
    const node = document.createElement(tag);
    if (className) node.className = className;
    if (text) node.textContent = text;
    return node;
  }
  const panel = el('dialog', 'mbfe-panel');
  panel.id = 'mbfe-panel'; panel.hidden = true;
  panel.setAttribute('aria-label', 'MB Bridge');
  const aside = el('aside', 'mbfe-sidebar');
  const header = el('header', 'mbfe-header');
  const back = el('button', 'mbfe-back');
  back.type = 'button'; back.title = 'Return to canvas'; back.setAttribute('aria-label', 'Return to canvas');
  back.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="m12 5-7 7 7 7M5 12h15"/></svg>';
  const mark = el('span', 'mbfe-logo'); mark.innerHTML = logo;
  header.append(back, mark, el('h1', '', 'MB Bridge'));
  const nav = el('nav', 'mbfe-nav'); nav.setAttribute('aria-label', 'MB Bridge tools');
  const footer = el('div', 'mbfe-sidebar-footer');
  footer.append(el('span', 'mbfe-connected', config.available ? 'Meta Box connected' : 'Meta Box unavailable'), el('p', '', 'Your fields. Inside Etch.'));
  aside.append(header, nav, footer);
  const syncStatus = el('div', 'mbfe-refresh-warning'); syncStatus.hidden = true;
  const syncMessage = el('p'); syncMessage.setAttribute('role', 'status');
  const reloadEtch = el('button', 'mbfe-refresh', 'Reload Etch'); reloadEtch.type = 'button';
  syncStatus.append(syncMessage, reloadEtch);
  document.addEventListener('mbfe:sync-status', event => { if (syncMessage.textContent !== event.detail) syncMessage.textContent = event.detail; syncStatus.hidden = false; });
  reloadEtch.addEventListener('click', () => {
    if (panel.querySelector('.mbfe-confirm')) return;
    const dialog = el('dialog', 'mbfe-confirm');
    dialog.setAttribute('aria-labelledby', 'mbfe-reload-title');
    dialog.setAttribute('aria-describedby', 'mbfe-reload-description');
    const heading = el('h2', '', 'Reload Etch?'); heading.id = 'mbfe-reload-title';
    const message = el('p', '', 'Save your Etch layout and Meta Box changes before continuing. Reloading will discard any unsaved changes. This does not save your work.');
    message.id = 'mbfe-reload-description';
    const cancel = el('button', 'mbfe-refresh', 'Keep editing'); cancel.type = 'button';
    const confirm = el('button', 'mbfe-refresh', 'Reload Etch'); confirm.type = 'button';
    const actions = el('div', 'mbfe-actions'); actions.append(cancel, confirm);
    dialog.append(heading, message, actions); panel.append(dialog);
    cancel.addEventListener('click', () => dialog.close());
    // Retain native beforeunload protection, including warnings from embedded editors.
    confirm.addEventListener('click', () => { dialog.close(); window.location.reload(); });
    dialog.addEventListener('close', () => { dialog.remove(); reloadEtch.focus(); });
    dialog.addEventListener('keydown', event => event.stopPropagation());
    dialog.showModal(); cancel.focus();
  });
  const main = el('div', 'mbfe-main');
  const toolbar = el('header', 'mbfe-toolbar');
  const heading = el('div');
  const title = el('h2'); title.tabIndex = -1;
  const description = el('p'); heading.append(title, description);
  const actions = el('div', 'mbfe-actions');
  const listBack = el('button', 'mbfe-list-back', '← Back to list'); listBack.type = 'button'; listBack.hidden = true;
  const refresh = el('button', 'mbfe-refresh', 'Refresh'); refresh.type = 'button';
  refresh.title = 'Reload this Meta Box screen';
  const external = el('a', 'mbfe-external', 'Open in WordPress ↗'); external.target = '_blank'; external.rel = 'noopener';
  actions.append(listBack, refresh, external); toolbar.append(heading, actions);
  const content = el('div', 'mbfe-content');
  const notice = el('p', 'mbfe-loading', 'Loading MB Bridge…'); notice.setAttribute('role', 'status'); notice.hidden = true;
  content.append(notice); main.append(toolbar, syncStatus, content); panel.append(aside, main); document.body.append(panel);
  let button = null, active = null;
  const frames = new Map();
  const sections = [{ id: 'page', label: 'Page fields', description: 'Edit the Meta Box values for the current page.', icon: 'page' }, ...config.sections];
  const buttons = new Map();
  for (const section of sections) {
    if (section.id === 'page') nav.append(el('p', 'mbfe-nav-label', 'Current content'));
    if (section === sections[1]) nav.append(el('p', 'mbfe-nav-label', 'Manage'));
    const item = el('button', 'mbfe-nav-item'); item.type = 'button';
    item.innerHTML = icon(section.icon); item.append(el('span', '', section.label));
    item.addEventListener('click', () => {
      if (active?.section.id === section.id && isDetail(active)) returnToList();
      else select(section);
    });
    buttons.set(section.id, item); nav.append(item);
  }
  function setExpanded(open) {
    button?.setAttribute('aria-expanded', String(open));
    button?.toggleAttribute('selected', open);
  }
  function isDetail(entry) {
    if (!entry?.section.url) return false;
    const current = new URL(entry.currentUrl);
    const list = new URL(entry.section.url);
    return current.pathname !== list.pathname || current.searchParams.get('post_type') !== list.searchParams.get('post_type');
  }
  function updateActions() {
    listBack.hidden = !isDetail(active);
    if (active) external.href = active.currentUrl;
  }
  function returnToList() {
    if (!isDetail(active)) return;
    const entry = active;
    // Keep the current document visible until navigation actually happens. Native
    // editor unload protection can still cancel navigation without blanking it.
    const navigate = () => { entry.frame.src = entry.listUrl || entry.section.url; };
    if (entry.dirty) askDiscard(navigate); else navigate();
  }
  function askDiscard(proceed, switching = false) {
    if (panel.querySelector('.mbfe-confirm')) return;
    const dialog = el('dialog', 'mbfe-confirm');
    dialog.setAttribute('aria-labelledby', 'mbfe-confirm-title');
    const prompt = el('h2', '', switching ? 'Unsaved Meta Box changes' : 'Discard unsaved changes?'); prompt.id = 'mbfe-confirm-title';
    const keep = el('button', 'mbfe-refresh', 'Keep editing'); keep.type = 'button';
    const discard = el('button', 'mbfe-refresh', switching ? 'Continue without saving' : 'Discard changes'); discard.type = 'button';
    const row = el('div', 'mbfe-actions'); row.append(keep, discard);
    dialog.append(prompt, el('p', '', switching ? 'Your changes are not saved. They will stay in the MB Bridge panel while you use other tools. Return here to save them before leaving or refreshing Etch.' : 'Changes on this screen have not been saved.'), row);
    panel.append(dialog);
    keep.addEventListener('click', () => dialog.close());
    discard.addEventListener('click', () => { dialog.close(); proceed(); });
    dialog.addEventListener('close', () => dialog.remove());
    dialog.addEventListener('keydown', event => event.stopPropagation());
    dialog.showModal(); keep.focus();
  }
  listBack.addEventListener('click', returnToList);
  function hide(returnFocus = true) {
    window.mbfeSync?.check();
    panel.close();
    panel.hidden = true; setExpanded(false);
    if (returnFocus) button?.focus();
  }
  function leave(proceed) {
    if ([...frames.values()].some(entry => entry.dirty)) askDiscard(proceed, true);
    else proceed();
  }
  function close(returnFocus = true) { leave(() => hide(returnFocus)); }
  function open() {
    panel.hidden = false; setExpanded(true);
    panel.show();
    // Reopening Page fields follows Etch's current URL without discarding a prior page's frame.
    select(active?.section || sections[0]);
  }
  window.mbfePanel = { open, close };
  back.addEventListener('click', () => leave(() => {
    window.etch?.navigation?.goTo('builder');
    hide();
  }));
  panel.addEventListener('cancel', event => { event.preventDefault(); close(); });
  document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && !event.defaultPrevented && !panel.hidden) { event.preventDefault(); close(); }
  });
  window.addEventListener('beforeunload', event => {
    if ([...frames.values()].some(entry => entry.dirty)) { event.preventDefault(); event.returnValue = ''; }
  });
  function attachFrame(entry) {
    window.mbfeSync?.check();
    clearTimeout(entry.timeout);
    entry.loaded = false; entry.dirty = false;
    entry.frame.classList.remove('is-ready');
    try {
      const doc = entry.frame.contentDocument;
      if (!doc?.body) throw new Error('Unavailable');
      if (!doc.getElementById('wpbody-content')) throw new Error('Session or screen unavailable');
      doc.documentElement.classList.add('mbfe-embedded');
      const css = doc.createElement('link'); css.rel = 'stylesheet'; css.href = config.frameCss;
      css.addEventListener('load', () => {
        // Apply the embedded layout before exposing the document.
        requestAnimationFrame(() => {
          entry.loaded = true; entry.frame.classList.add('is-ready');
          if (entry === active) notice.hidden = true;
        });
      });
      css.addEventListener('error', () => {
        if (entry === active) { notice.hidden = false; notice.textContent = 'The screen styles could not load. Refresh or open this screen in WordPress.'; }
      });
      doc.head.append(css);
      if (doc.querySelector('.notice-success, .updated, .mbfe-saved')) window.mbfeSync?.warn();
      // pagehide only fires on a real navigation, so cancelled unload prompts keep the editor visible.
      entry.frame.contentWindow.addEventListener('pagehide', () => {
        entry.loaded = false; entry.frame.classList.remove('is-ready');
        if (entry === active) { notice.hidden = false; notice.textContent = 'Loading MB Bridge…'; }
      });
      const changed = event => {
        if (event.target.closest('form') && !event.target.closest('#posts-filter')) entry.dirty = true;
      };
      doc.addEventListener('input', changed); doc.addEventListener('change', changed);
      doc.addEventListener('mbfe:dirty', () => { entry.dirty = true; });
      doc.addEventListener('submit', event => queueMicrotask(() => { if (!event.defaultPrevented) entry.dirty = false; }));
      // Meta Box's React editors save without navigating. A native success notice marks that save complete.
      new MutationObserver(records => {
        for (const record of records) {
          for (const node of record.addedNodes) {
            if (node.nodeType === 1 && (node.matches('.notice-success, .components-notice.is-success') || node.querySelector('.notice-success, .components-notice.is-success'))) {
              entry.dirty = false;
              window.mbfeSync?.warn();
              window.mbfeSync?.check();
            }
          }
        }
      }).observe(doc.body, { childList: true, subtree: true });
      doc.addEventListener('keydown', event => {
        if (event.key === 'Escape' && !doc.querySelector('.media-modal, [role="dialog"]')) { event.preventDefault(); close(); }
      });
      entry.currentUrl = entry.frame.contentWindow.location.href;
      if (!isDetail(entry)) entry.listUrl = entry.currentUrl;
      if (entry === active) updateActions();
    } catch {
      if (entry === active) { notice.hidden = false; notice.textContent = 'This screen could not be embedded. Open it in WordPress to continue, then refresh here.'; }
    }
  }
  function select(section) {
    buttons.forEach((node, id) => {
      node.classList.toggle('is-active', id === section.id);
      if (id === section.id) node.setAttribute('aria-current', 'page'); else node.removeAttribute('aria-current');
    });
    title.textContent = section.label; description.textContent = section.description;
    title.focus();
    frames.forEach(entry => { entry.frame.hidden = true; });
    content.querySelector('.mbfe-empty-state')?.remove();
    notice.hidden = true;
    let url = section.url;
    if (section.id === 'page') {
      const params = new URL(location.href).searchParams;
      const postId = params.get('original_post_id') || params.get('post_id');
      if (postId && /^\d+$/.test(postId)) {
        const target = new URL(config.fieldsUrl); target.searchParams.set('post', postId); url = target.href;
      }
    }
    actions.hidden = !config.available || !url;
    if (!config.available || !url) {
      active = null; updateActions();
      const empty = el('div', 'mbfe-empty-state');
      empty.append(el('h3', '', config.available ? 'Open a page to edit its fields' : 'Meta Box is not active'), el('p', '', config.available ? 'Select a page or post in Etch, then open this panel.' : 'Activate Meta Box to use your fields here. Management tools appear when their Meta Box extensions are active.'));
      if (!config.available && config.pluginsUrl) {
        const link = el('a', 'mbfe-external', 'Open plugins ↗'); link.href = config.pluginsUrl; link.target = '_blank'; link.rel = 'noopener'; empty.append(link);
      }
      content.append(empty); return;
    }
    let entry = frames.get(url);
    if (entry && section.id === 'page' && !entry.dirty && entry.loaded) {
      entry.frame.classList.remove('is-ready'); entry.loaded = false; entry.frame.src = url;
    }
    if (!entry) {
      const frame = el('iframe', 'mbfe-frame'); frame.title = 'MB Bridge — ' + section.label;
      entry = { frame, section, url, currentUrl: url, loaded: false, dirty: false };
      frames.set(url, entry);
      frame.addEventListener('load', () => attachFrame(entry));
      frame.src = url; content.append(frame);
      entry.timeout = setTimeout(() => {
        if (!entry.loaded && entry === active) notice.textContent = 'This screen is taking longer than expected. You can open it in WordPress or refresh.';
      }, 15000);
    }
    active = entry; entry.frame.hidden = false; updateActions();
    notice.hidden = entry.loaded; if (!entry.loaded) notice.textContent = 'Loading MB Bridge…';
  }
  refresh.addEventListener('click', () => {
    if (!active) return;
    const entry = active;
    const reload = () => { entry.frame.src = entry.currentUrl; };
    if (entry.dirty) askDiscard(reload); else reload();
  });
  // Use Etch's documented bottom-section API; no dependency on Etch Toolkit.
  let attempts = 0;
  const timer = setInterval(() => {
    const bar = window.etchControls?.builder?.settingsBar?.bottom;
    const container = document.querySelector('.settings-bar__section.bottom');
    if (!bar || !container) { if (++attempts > 240) clearInterval(timer); return; }
    clearInterval(timer);
    const existing = new Set(container.querySelectorAll('button'));
    const observer = new MutationObserver(() => {
      const candidates = [...container.querySelectorAll('button')].filter(node => !existing.has(node));
      button = candidates.find(node => node.querySelector('[data-icon="mdi:alpha-m-box"]')) || candidates.find(node => !node.hasAttribute('aria-label'));
      if (!button) return;
      observer.disconnect(); button.classList.add('mbfe-toolbar-button');
      button.setAttribute('aria-label', 'MB Bridge'); button.setAttribute('aria-controls', panel.id); button.title = 'MB Bridge'; setExpanded(false);
      const stamp = el('span', 'mbfe-toolbar-logo'); stamp.innerHTML = logo; button.append(stamp);
    });
    observer.observe(container, { childList: true, subtree: true });
    bar.addBefore({ id: 'mbfe-meta-box', icon: 'mdi:alpha-m-box', tooltip: 'MB Bridge', callback: () => panel.hidden ? open() : close() });
    document.querySelector('.settings-bar').addEventListener('click', event => {
      const clicked = event.target.closest('button, a');
      if (panel.hidden || !clicked || clicked === button) return;
      if ([...frames.values()].some(entry => entry.dirty)) {
        event.preventDefault();
        event.stopImmediatePropagation();
        leave(() => { hide(false); clicked.click(); });
      } else hide(false);
    }, true);
  }, 250);
})();
