// panels.js — Универсальное управление боковыми панелями FWE
// desktop: drag-resize + localStorage, mobile: чекбоксы и spp_nav

(function() {
  const MOBILE_WIDTH = 999;
  const LEFT_PANEL_SEL  = '.side-panel1, .panel.left';
  const RIGHT_PANEL_SEL = '.side-panel2, .panel.right';
  const leftPanel     = document.querySelector(LEFT_PANEL_SEL);
  const rightPanel    = document.querySelector(RIGHT_PANEL_SEL);
  const btnOpenLeft   = document.getElementById('btn-left');
  const btnOpenRight  = document.getElementById('btn-right');
  const closeLeft     = document.getElementById('close-left');
  const closeRight    = document.getElementById('close-right');
  const cbLeft        = document.getElementById('side-checkbox1');
  const cbRight       = document.getElementById('side-checkbox2');

  // --- Ресайзер (desktop only)
  function ensureResizer(panel, side) {
    let resizer = panel?.querySelector('.panel-resizer');
    if (!resizer) {
      resizer = document.createElement('div');
      resizer.className = `panel-resizer panel-resizer-${side}`;
      resizer.style[side === 'left' ? 'right' : 'left'] = '-3px';
      resizer.style.position = 'absolute';
      resizer.style.top = '0';
      resizer.style.bottom = '0';
      resizer.style.width = '7px';
      resizer.style.zIndex = 2205;
      resizer.style.background = '#253042cc';
      resizer.style.cursor = 'col-resize';
      resizer.style.transition = 'background .15s';
      resizer.style.opacity = '0.74';
      // Графическая "ручка" в центре
      resizer.innerHTML = '<span style="display:block;position:absolute;top:50%;left:50%;width:4px;height:28px;margin:-14px 0 0 -2px;background:#5a6a7a;border-radius:2px;opacity:0.6;"></span>';
      panel.appendChild(resizer);
      resizer.onmouseenter = () => { resizer.style.background = '#41c7ff'; resizer.style.opacity = '1'; };
      resizer.onmouseleave = () => { resizer.style.background = '#253042cc'; resizer.style.opacity = '0.74'; };
    }
    return resizer;
  }

  // --- Применить ширину панели из localStorage (desktop only)
  function applyPanelWidths() {
    if (window.innerWidth > MOBILE_WIDTH) {
      const lw = +localStorage.getItem('panel-left-width') || 250;
      const rw = +localStorage.getItem('panel-right-width') || 250;
      if (leftPanel)  { leftPanel.style.flexBasis = lw + 'px'; leftPanel.style.width = ''; }
      if (rightPanel) { rightPanel.style.flexBasis = rw + 'px'; rightPanel.style.width = ''; }
    } else {
      if (leftPanel)  { leftPanel.style.flexBasis = ''; leftPanel.style.width = ''; }
      if (rightPanel) { rightPanel.style.flexBasis = ''; rightPanel.style.width = ''; }
    }
  }

  // --- Drag&Drop для изменения ширины панели (desktop only)
  function initDrag(panel, side) {
    const resizer = ensureResizer(panel, side);
    if (!resizer) return;
    resizer.onmousedown = function(e) {
      if (window.innerWidth <= MOBILE_WIDTH) return;
      document.body.style.userSelect = 'none';
      document.body.style.cursor = 'col-resize';
      const startX = e.clientX;
      const startW = panel.offsetWidth;
      function onMove(ev) {
        const dx = ev.clientX - startX;
        let newW = (side === 'left') ? startW + dx : startW - dx;
        newW = Math.max(110, Math.min(newW, 700));
        panel.style.flexBasis = newW + 'px';
      }
      function onUp() {
        const w = panel.offsetWidth;
        localStorage.setItem(`panel-${side}-width`, w);
        document.body.style.cursor = '';
        document.body.style.userSelect = '';
        window.removeEventListener('mousemove', onMove);
        window.removeEventListener('mouseup', onUp);
      }
      window.addEventListener('mousemove', onMove);
      window.addEventListener('mouseup', onUp);
    };
  }

  // --- Мобильный reset (сброс ручек, сброс скрытия)
  function mobileModeReset() {
    if (leftPanel)  { leftPanel.style.flexBasis = ''; leftPanel.style.width = ''; }
    if (rightPanel) { rightPanel.style.flexBasis = ''; rightPanel.style.width = ''; }
    if (leftPanel)  { const r = leftPanel.querySelector('.panel-resizer');  if (r) r.style.display = 'none'; }
    if (rightPanel) { const r = rightPanel.querySelector('.panel-resizer'); if (r) r.style.display = 'none'; }
    document.querySelectorAll('.panel-hide-btn, .panel-restore-btn').forEach(e => e.remove());
    leftPanel?.classList.remove('hidden');
    rightPanel?.classList.remove('hidden');
  }

  // --- Кнопки сворачивания панелей (desktop only)
  function createHideBtn(panel, side) {
    let btn = panel.querySelector('.panel-hide-btn');
    if (!btn) {
      btn = document.createElement('button');
      btn.type = 'button';
      btn.className = `panel-hide-btn panel-hide-btn-${side}`;
      btn.innerHTML = side === 'left' ? '‹' : '›';
      btn.title = 'Свернуть панель';
      btn.style.position = 'absolute';
      btn.style.top = '50%';
      btn.style.transform = 'translateY(-50%)';
      btn.style.zIndex = '2210';
      btn.style.width = '24px';
      btn.style.height = '48px';
      btn.style.display = 'flex';
      btn.style.alignItems = 'center';
      btn.style.justifyContent = 'center';
      btn.style.background = '#2c2f34';
      btn.style.color = '#8bcfff';
      btn.style.border = 'none';
      btn.style.cursor = 'pointer';
      btn.style.opacity = '0.7';
      btn.style.transition = 'background 0.2s, opacity 0.2s, color 0.2s';
      btn.onmouseenter = () => { btn.style.background = '#33c9ff'; btn.style.color = '#fff'; btn.style.opacity = '1'; };
      btn.onmouseleave = () => { btn.style.background = '#2c2f34'; btn.style.color = '#8bcfff'; btn.style.opacity = '0.7'; };
      if (side === 'left') btn.style.right = '-12px';
      if (side === 'right') btn.style.left = '-12px';
      btn.onclick = function(e) {
        e.stopPropagation();
        hidePanel(panel, side);
      };
      panel.appendChild(btn);
    }
  }

  function hidePanel(panel, side) {
    panel.classList.add('hidden');
    localStorage.setItem(`${side}-panel-hidden`, '1');
    showRestoreBtn(side, true);
  }

  function showRestoreBtn(side, show) {
    const btnId = side === 'left' ? 'restore-left-btn' : 'restore-right-btn';
    document.getElementById(btnId)?.remove();
    if (show) {
      const btn = document.createElement('button');
      btn.id = btnId;
      btn.className = `panel-restore-btn panel-restore-btn-${side}`;
      btn.innerHTML = side === 'left' ? '›' : '‹';
      btn.title = 'Показать панель';
      btn.style.position = 'fixed';
      btn.style.top = '50%';
      btn.style.transform = 'translateY(-50%)';
      btn.style.zIndex = '2211';
      btn.style.width = '24px';
      btn.style.height = '48px';
      btn.style.display = 'flex';
      btn.style.alignItems = 'center';
      btn.style.justifyContent = 'center';
      btn.style.background = '#2c2f34';
      btn.style.color = '#8bcfff';
      btn.style.border = 'none';
      btn.style.cursor = 'pointer';
      btn.style.opacity = '0.7';
      btn.style.transition = 'background 0.2s, opacity 0.2s, color 0.2s';
      btn.onmouseenter = () => { btn.style.background = '#33c9ff'; btn.style.color = '#fff'; btn.style.opacity = '1'; };
      btn.onmouseleave = () => { btn.style.background = '#2c2f34'; btn.style.color = '#8bcfff'; btn.style.opacity = '0.7'; };
      if (side === 'left') btn.style.left = '0';
      if (side === 'right') btn.style.right = '0';
      btn.onclick = function() {
        const panel = document.getElementById(side === 'left' ? 'panel-left' : 'panel-right');
        panel?.classList.remove('hidden');
        localStorage.removeItem(`${side}-panel-hidden`);
        btn.remove();
      };
      document.body.appendChild(btn);
    }
  }

  function applySavedPanelState() {
    if (window.innerWidth <= MOBILE_WIDTH) return;
    if (localStorage.getItem('left-panel-hidden')) {
      leftPanel?.classList.add('hidden');
      showRestoreBtn('left', true);
    }
    if (localStorage.getItem('right-panel-hidden')) {
      rightPanel?.classList.add('hidden');
      showRestoreBtn('right', true);
    }
  }

  // --- UI (mobile/desktop переключение)
  const mq = window.matchMedia(`(max-width:${MOBILE_WIDTH}px)`);
  const isMobile = () => mq.matches;

  function updateUI() {
    if (!isMobile()) {
      btnOpenLeft?.classList.add('hidden');
      btnOpenRight?.classList.add('hidden');
      closeLeft?.classList.add('hidden');
      closeRight?.classList.add('hidden');
      // Органы управления только на desktop
      if (leftPanel && !leftPanel.classList.contains('hidden'))  createHideBtn(leftPanel, 'left');
      if (rightPanel && !rightPanel.classList.contains('hidden')) createHideBtn(rightPanel, 'right');
      const r1 = leftPanel?.querySelector('.panel-resizer'); if (r1) r1.style.display = 'block';
      const r2 = rightPanel?.querySelector('.panel-resizer'); if (r2) r2.style.display = 'block';
    } else {
      btnOpenLeft?.classList.remove('hidden');
      btnOpenRight?.classList.remove('hidden');
      // spp_nav/close-btn только для мобильных
      const leftOpen  = leftPanel?.classList.contains('open');
      const rightOpen = rightPanel?.classList.contains('open');
      closeLeft?.classList.toggle('hidden',  !leftOpen);
      closeRight?.classList.toggle('hidden', !rightOpen);
      mobileModeReset();
    }
  }

  // --- Открытие/закрытие панелей на мобиле
  function openLeft() {
    rightPanel?.classList.remove('open');
    leftPanel?.classList.toggle('open');
    if (cbLeft)  cbLeft.checked  = !!leftPanel?.classList.contains('open');
    if (cbRight) cbRight.checked = false;
    updateUI();
  }
  function openRight() {
    leftPanel?.classList.remove('open');
    rightPanel?.classList.toggle('open');
    if (cbRight) cbRight.checked = !!rightPanel?.classList.contains('open');
    if (cbLeft)  cbLeft.checked  = false;
    updateUI();
  }
  function hidePanels() {
    leftPanel?.classList.remove('open');
    rightPanel?.classList.remove('open');
    if (cbLeft)  cbLeft.checked  = false;
    if (cbRight) cbRight.checked = false;
    updateUI();
  }

  // --- Инициализация
  if (btnOpenLeft)  btnOpenLeft.addEventListener('click', openLeft);
  if (btnOpenRight) btnOpenRight.addEventListener('click', openRight);
  if (closeLeft)    closeLeft.addEventListener('click', hidePanels);
  if (closeRight)   closeRight.addEventListener('click', hidePanels);

  mq.addEventListener('change', (e) => {
    if (e.matches) {
      mobileModeReset();
      hidePanels();
    } else {
      applyPanelWidths();
      applySavedPanelState();
    }
    updateUI();
  });

  function initialSetup() {
    applyPanelWidths();
    initDrag(leftPanel, 'left');
    initDrag(rightPanel, 'right');
    if (isMobile()) mobileModeReset();
    updateUI();
    applySavedPanelState();
    if (window.innerWidth > MOBILE_WIDTH) {
      if (leftPanel)  createHideBtn(leftPanel, 'left');
      if (rightPanel) createHideBtn(rightPanel, 'right');
    }
  }
  window.addEventListener('resize', () => {
    applyPanelWidths();
    updateUI();
  });
  document.addEventListener('DOMContentLoaded', initialSetup);

})();









document.getElementById('panel-left').style.flexBasis = '250px';
document.getElementById('panel-left').classList.remove('hidden');
