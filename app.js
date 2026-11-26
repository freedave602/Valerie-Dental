/* Valerie Dental Practice | shared behaviour: navigation, animation, API helper, chat assistant */
const API = 'api/';

async function api(file, data, method) {
  const opts = { method: method || (data ? 'POST' : 'GET'), headers: { 'Content-Type': 'application/json' }, credentials: 'same-origin' };
  if (data && opts.method === 'POST') opts.body = JSON.stringify(data);
  try {
    const res = await fetch(API + file, opts);
    const json = await res.json().catch(() => ({ success: false, error: 'The server sent an unexpected reply.' }));
    json._status = res.status;
    return json;
  } catch (e) {
    return { success: false, error: 'Could not reach the server. Make sure XAMPP Apache and MySQL are running and you opened the site via http://localhost/.' };
  }
}
const $ = (s, r = document) => r.querySelector(s);
const $$ = (s, r = document) => [...r.querySelectorAll(s)];
const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
function notice(el, msg, type) { el.textContent = msg; el.className = 'notice show ' + (type || 'err'); el.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); }
function fmtDate(d) { return new Date(d + 'T00:00:00').toLocaleDateString('en-GB', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' }); }
function todayStr() { const d = new Date(); return new Date(d - d.getTimezoneOffset() * 6e4).toISOString().slice(0, 10); }

/* Mobile menu */
const toggle = $('.nav-toggle'), nav = $('#primary-nav');
if (toggle) toggle.addEventListener('click', () => {
  const open = nav.classList.toggle('open');
  toggle.setAttribute('aria-expanded', open);
  toggle.textContent = open ? 'Close' : 'Menu';
});
document.addEventListener('keydown', e => { if (e.key === 'Escape' && nav && nav.classList.contains('open')) toggle.click(); });

/* Scroll reveal */
const io = 'IntersectionObserver' in window ? new IntersectionObserver(es => es.forEach(e => { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } }), { threshold: .12 }) : null;
$$('.reveal').forEach(el => io ? io.observe(el) : el.classList.add('in'));

/* Animated counters */
$$('[data-count]').forEach(el => {
  const end = +el.dataset.count, suffix = el.dataset.suffix || '';
  if (matchMedia('(prefers-reduced-motion: reduce)').matches) { el.textContent = end + suffix; return; }
  const run = () => { const t0 = performance.now(); (function f(t) { const p = Math.min((t - t0) / 1400, 1); el.textContent = Math.round(end * (1 - Math.pow(1 - p, 3))) + suffix; if (p < 1) requestAnimationFrame(f); })(t0); };
  io ? new IntersectionObserver((es, o) => { if (es[0].isIntersecting) { run(); o.disconnect(); } }).observe(el) : (el.textContent = end + suffix);
});
$$('.year').forEach(el => el.textContent = new Date().getFullYear());

/* Chat assistant: instant answers for common questions, or leave a message that is saved to the database */
const chatBox = $('#chat');
if (chatBox) {
  const fab = $('#chat-fab'), log = $('.chat-log', chatBox), form = $('form', chatBox), input = $('input', form);
  const kb = [
    [/hour|open|close|time/i, 'We are open Mon-Fri 8am-6pm and Saturday 9am-2pm. Closed on Sundays.'],
    [/book|appoint/i, 'You can book online in under a minute: <a href="book.html">Book an appointment</a>. New here? <a href="register.html">Register first</a>.'],
    [/price|cost|fee|how much/i, 'Prices start from GHS 120 for a consultation. See the full list on our <a href="services.html">Services</a> page.'],
    [/where|locat|address|tema/i, 'We are on Harbour Road, Community 1, Tema. Directions are on the <a href="contact.html">Contact</a> page.'],
    [/emergen|pain|urgent|swollen|bleed/i, 'For urgent pain or swelling, call us on 024 400 0111 and we will fit you in the same day where we can.'],
    [/cancel|change|reschedul/i, 'Use <a href="portal.html">My Appointments</a> with your email and date of birth to view or cancel a booking.']
  ];
  const say = (t, who) => { const m = document.createElement('div'); m.className = 'msg ' + who; m.innerHTML = t; log.append(m); log.scrollTop = log.scrollHeight; return m; };
  let stage = 0, lead = {};
  const open = () => { chatBox.classList.toggle('open'); fab.setAttribute('aria-expanded', chatBox.classList.contains('open')); if (chatBox.classList.contains('open')) input.focus(); };
  fab.addEventListener('click', open);
  say('Hello! I\'m the Valerie assistant. Ask about opening hours, prices, booking, or choose below.', 'bot');
  $$('.quick button', chatBox).forEach(b => b.addEventListener('click', () => ask(b.textContent)));
  function ask(q) {
    say(esc(q), 'me');
    if (/message|speak|human|someone|call me/i.test(q)) { stage = 1; return setTimeout(() => say('Happy to pass this on. What is your name?', 'bot'), 400); }
    const hit = kb.find(k => k[0].test(q));
    setTimeout(() => say(hit ? hit[1] : 'I\'m not sure about that one. Type "message" and I\'ll pass your question to the team.', 'bot'), 500);
  }
  form.addEventListener('submit', async e => {
    e.preventDefault(); const v = input.value.trim(); if (!v) return; input.value = '';
    if (!stage) return ask(v);
    say(esc(v), 'me');
    if (stage === 1) { lead.name = v; stage = 2; return setTimeout(() => say('Thanks, ' + esc(v) + '. What is your email address?', 'bot'), 350); }
    if (stage === 2) { if (!/^\S+@\S+\.\S+$/.test(v)) return say('That email does not look right. Try again?', 'bot'); lead.email = v; stage = 3; return setTimeout(() => say('And your message?', 'bot'), 350); }
    if (stage === 3) {
      lead.message = '[Chat] ' + v;
      const r = await api('send_message.php', lead);
      say(r.success ? 'Sent! The team will reply by email soon.' : esc(r.error), 'bot'); stage = 0; lead = {};
    }
  });
}
