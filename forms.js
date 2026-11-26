/* Valerie Dental Practice | patient-facing forms: register, book, portal, contact */

/* Client-side validation (the server re-checks everything) */
function validate(form) {
  let ok = true;
  $$('input,select,textarea', form).forEach(f => {
    const bad = !f.checkValidity();
    f.classList.toggle('bad', bad);
    f.setAttribute('aria-invalid', bad);
    if (bad) ok = false;
  });
  if (!ok) { const first = $('.bad', form); first.focus(); }
  return ok;
}
function busy(btn, on, label) { btn.disabled = on; if (label) btn.textContent = on ? 'Please wait...' : label; }

/* ---- Registration ---- */
const reg = $('#registrationForm');
if (reg) {
  const msg = $('#formMsg'), btn = $('button[type=submit]', reg);
  $('#dob').max = todayStr();
  reg.addEventListener('submit', async e => {
    e.preventDefault(); if (!validate(reg)) return notice(msg, 'Please fix the highlighted fields.');
    busy(btn, true, 'Register');
    const r = await api('register_patient.php', {
      firstName: $('#firstName').value, lastName: $('#lastName').value, email: $('#email').value, phone: $('#phone').value,
      dob: $('#dob').value, address: $('#address').value, medicalHistory: $('#medicalHistory').value, consent: $('#consent').checked
    });
    busy(btn, false, 'Register');
    if (r.success) { notice(msg, 'You are registered. Welcome to Valerie Dental! You can now book your first visit.', 'ok'); reg.reset(); setTimeout(() => location.href = 'book.html', 2200); }
    else notice(msg, r.error);
  });
}

/* ---- Booking ---- */
const book = $('#bookingForm');
if (book) {
  const msg = $('#formMsg'), btn = $('button[type=submit]', book), dateEl = $('#appointmentDate'), box = $('#slots');
  dateEl.min = todayStr();
  async function loadSlots() {
    if (!dateEl.value) return;
    box.innerHTML = '<p class="hint">Checking availability...</p>';
    const r = await api('slots.php?date=' + dateEl.value);
    if (!r.success) { box.innerHTML = ''; return notice(msg, r.error); }
    if (r.closed) { box.innerHTML = '<p class="hint">We are closed on Sundays. Please choose another day.</p>'; return; }
    const now = new Date(), isToday = dateEl.value === todayStr();
    box.innerHTML = r.slots.map((s, i) => {
      const [h, m] = s.time.split(':'), past = isToday && (+h * 60 + +m) <= now.getHours() * 60 + now.getMinutes();
      const label = ((+h % 12) || 12) + ':' + m + (+h < 12 ? ' AM' : ' PM');
      return `<div class="slot"><input type="radio" name="appointmentTime" id="t${i}" value="${s.time}" required ${s.free && !past ? '' : 'disabled'}><label for="t${i}">${label}</label></div>`;
    }).join('');
  }
  dateEl.addEventListener('change', loadSlots);
  const qEmail = new URLSearchParams(location.search).get('email'); if (qEmail) $('#email').value = qEmail;
  book.addEventListener('submit', async e => {
    e.preventDefault();
    if (!validate(book) || !$('input[name=appointmentTime]:checked')) return notice(msg, 'Please complete the form and pick a time slot.');
    busy(btn, true, 'Book appointment');
    const r = await api('book_appointment.php', {
      email: $('#email').value, appointmentDate: dateEl.value, appointmentTime: $('input[name=appointmentTime]:checked').value,
      treatmentType: $('#treatmentType').value, notes: $('#notes').value
    });
    busy(btn, false, 'Book appointment');
    if (r.success) { notice(msg, `All set, ${r.firstName}! Your ${r.treatment} is booked for ${fmtDate(dateEl.value)} at ${$('input[name=appointmentTime]:checked').nextElementSibling.textContent}.`, 'ok'); book.reset(); box.innerHTML = '<p class="hint">Pick a date to see available times.</p>'; }
    else { notice(msg, r.error); if (r._status === 409) loadSlots(); if (r.needsRegistration) msg.innerHTML += ' <a href="register.html">Register here</a>.'; }
  });
}

/* ---- Patient portal ---- */
const portal = $('#portalForm');
if (portal) {
  const msg = $('#formMsg'), out = $('#portalOut');
  let creds = {};
  async function show() {
    const r = await api('my_appointments.php', creds);
    if (!r.success) { out.innerHTML = ''; return notice(msg, r.error); }
    msg.className = 'notice';
    const today = todayStr();
    out.innerHTML = `<h2>Hello, ${esc(r.firstName)}</h2><div class="table-wrap" style="margin-top:1rem"><table class="data"><thead><tr><th>Date</th><th>Time</th><th>Treatment</th><th>Status</th><th></th></tr></thead><tbody>` +
      (r.rows.length ? r.rows.map(a => `<tr><td>${fmtDate(a.appointment_date)}</td><td>${a.appointment_time}</td><td>${esc(a.treatment)}</td><td><span class="badge ${a.status}">${a.status}</span></td>
      <td>${a.status === 'Scheduled' && a.appointment_date >= today ? `<button class="btn btn-ghost btn-sm" data-cancel="${a.appointment_id}">Cancel</button>` : ''}</td></tr>`).join('') : '<tr><td colspan="5">No appointments yet.</td></tr>') + '</tbody></table></div>';
  }
  portal.addEventListener('submit', async e => { e.preventDefault(); if (!validate(portal)) return; creds = { email: $('#email').value, dob: $('#dob').value }; show(); });
  out.addEventListener('click', async e => {
    const id = e.target.dataset.cancel; if (!id || !confirm('Cancel this appointment?')) return;
    const r = await api('cancel_appointment.php', { ...creds, appointmentId: +id });
    if (r.success) { notice(msg, 'Appointment cancelled.', 'ok'); show(); } else notice(msg, r.error);
  });
}

/* ---- Contact ---- */
const contact = $('#contactForm');
if (contact) {
  const msg = $('#formMsg'), btn = $('button[type=submit]', contact);
  contact.addEventListener('submit', async e => {
    e.preventDefault(); if (!validate(contact)) return;
    busy(btn, true, 'Send message');
    const r = await api('send_message.php', { name: $('#contactName').value, email: $('#contactEmail').value, message: $('#contactMessage').value });
    busy(btn, false, 'Send message');
    if (r.success) { notice(msg, 'Thank you! Your message has reached the team and we will reply by email.', 'ok'); contact.reset(); } else notice(msg, r.error);
  });
}
