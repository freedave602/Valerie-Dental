/* Valerie Dental Practice | staff area: login + dashboard reports */

const loginForm = $('#staffLoginForm');
if (loginForm) {
  const msg = $('#formMsg');
  loginForm.addEventListener('submit', async e => {
    e.preventDefault();
    const btn = $('button', loginForm); btn.disabled = true;
    const r = await api('staff_login.php', { email: $('#staffEmail').value, password: $('#staffPassword').value });
    btn.disabled = false;
    if (r.success) location.href = 'staff-dashboard.php'; else notice(msg, r.error);
  });
}

const dash = $('#dashboard');
if (dash) {
  const head = $('#reportHead'), body = $('#reportBody'), search = $('#search'), count = $('#count');
  let current = { type: 'future', rows: [], cols: [] };
  const aptCols = [['appointment_date', 'Date', fmtDate], ['appointment_time', 'Time'], ['patient', 'Patient'], ['phone', 'Phone'], ['treatment', 'Treatment'], ['status', 'Status', v => `<span class="badge ${v}">${v}</span>`, true], ['notes', 'Notes']];
  const reports = {
    future: { label: 'Future appointments', cols: aptCols, actions: true },
    past: { label: 'Past appointments', cols: aptCols, actions: true },
    patients: { label: 'Patient list', cols: [['first_name', 'First name'], ['last_name', 'Last name'], ['email', 'Email'], ['phone', 'Phone'], ['dob', 'Date of birth', fmtDate], ['address', 'Address'], ['total_appointments', 'Visits']] },
    all: { label: 'All appointments', cols: aptCols, actions: true },
    today: { label: "Today's appointments", cols: aptCols, actions: true },
    week: { label: 'This week', cols: aptCols, actions: true },
    month: { label: 'This month', cols: aptCols, actions: true },
    patient: { label: 'By patient', cols: aptCols, actions: true },
    messages: { label: 'Messages', cols: [['sent_at', 'Received'], ['name', 'Name'], ['email', 'Email'], ['message', 'Message'], ['is_read', 'Read', v => (+v ? 'Yes' : '<strong>New</strong>'), true]], read: true }
  };

  function kickOut(r) { if (r._status === 401) location.href = 'staff-login.html'; }

  async function stats() {
    const s = await api('reports.php?type=summary'); kickOut(s); if (!s.success) return;
    $('#sPatients').textContent = s.patients; $('#sToday').textContent = s.today; $('#sUpcoming').textContent = s.upcoming; $('#sUnread').textContent = s.unread;
    const max = Math.max(1, ...s.byTreatment.map(t => +t.total));
    $('#bars').innerHTML = s.byTreatment.map(t => `<div class="bar-row"><span>${esc(t.name)}</span><i data-w="${(t.total / max) * 100}"></i><b>${t.total}</b></div>`).join('') || '<p class="hint">No bookings yet.</p>';
    requestAnimationFrame(() => $$('#bars i').forEach(i => i.style.width = i.dataset.w + '%'));
  }

  async function load(type) {
    const def = reports[type]; current.type = type;
    $$('.tabs button').forEach(b => b.setAttribute('aria-selected', b.dataset.type === type));
    $('#patientPick').style.display = type === 'patient' ? 'block' : 'none';
    $('#reportTitle').textContent = def.label;
    let url = 'reports.php?type=' + type;
    if (type === 'patient') { const id = $('#patientSelect').value; if (!id) { body.innerHTML = '<tr><td colspan="9">Choose a patient above.</td></tr>'; head.innerHTML = ''; count.textContent = ''; return; } url += '&patient_id=' + id; }
    body.innerHTML = '<tr><td colspan="9">Loading...</td></tr>';
    const r = await api(url); kickOut(r);
    if (!r.success) { body.innerHTML = `<tr><td colspan="9">${esc(r.error)}</td></tr>`; return; }
    current.rows = r.rows; current.cols = def.cols; current.def = def; render();
  }

  function render() {
    const { cols, rows, def } = current, q = search.value.toLowerCase();
    const shown = rows.filter(r => !q || Object.values(r).join(' ').toLowerCase().includes(q));
    head.innerHTML = '<tr>' + cols.map(c => `<th>${c[1]}</th>`).join('') + (def.actions || def.read ? '<th class="no-print">Action</th>' : '') + '</tr>';
    body.innerHTML = shown.length ? shown.map(r => '<tr>' + cols.map(c => `<td>${c[3] ? (c[2] ? c[2](r[c[0]]) : r[c[0]]) : (c[2] ? esc(c[2](r[c[0]])) : esc(r[c[0]] ?? ''))}</td>`).join('') +
      (def.actions ? `<td class="no-print">${r.status === 'Scheduled' ? `<button class="btn btn-ghost btn-sm" data-s="Completed" data-id="${r.appointment_id}">Done</button> <button class="btn btn-ghost btn-sm" data-s="Cancelled" data-id="${r.appointment_id}">Cancel</button>` : r.status === 'Cancelled' ? `<button class="btn btn-ghost btn-sm" data-s="Scheduled" data-id="${r.appointment_id}">Restore</button>` : ''}</td>` : '') +
      (def.read ? `<td class="no-print">${+r.is_read ? '' : `<button class="btn btn-ghost btn-sm" data-read="${r.message_id}">Mark read</button>`}</td>` : '') + '</tr>').join('')
      : `<tr><td colspan="${cols.length + 1}">Nothing to show for this report.</td></tr>`;
    count.textContent = shown.length + ' record' + (shown.length === 1 ? '' : 's');
  }

  $('.tabs').addEventListener('click', e => { if (e.target.dataset.type) load(e.target.dataset.type); });
  $('#patientSelect').addEventListener('change', () => load('patient'));
  search.addEventListener('input', render);
  body.addEventListener('click', async e => {
    const b = e.target; let r;
    if (b.dataset.s) r = await api('update_status.php', { appointmentId: +b.dataset.id, status: b.dataset.s });
    else if (b.dataset.read) r = await api('mark_message_read.php', { messageId: +b.dataset.read });
    else return;
    if (!r.success) alert(r.error); load(current.type); stats();
  });
  $('#csv').addEventListener('click', () => {
    const { cols, rows } = current; if (!rows.length) return;
    const lines = [cols.map(c => c[1]).join(',')].concat(rows.map(r => cols.map(c => '"' + String(r[c[0]] ?? '').replace(/"/g, '""') + '"').join(',')));
    const a = document.createElement('a'); a.href = URL.createObjectURL(new Blob([lines.join('\n')], { type: 'text/csv' }));
    a.download = 'valerie-' + current.type + '-' + todayStr() + '.csv'; a.click();
  });
  $('#print').addEventListener('click', () => print());
  $('#logout').addEventListener('click', async () => { await api('staff_logout.php', {}, 'POST'); location.href = 'staff-login.html'; });

  (async () => {
    const r = await api('reports.php?type=patients'); kickOut(r);
    if (r.success) $('#patientSelect').innerHTML += r.rows.map(p => `<option value="${p.patient_id}">${esc(p.first_name + ' ' + p.last_name)}</option>`).join('');
    stats(); load('future');
  })();
}
