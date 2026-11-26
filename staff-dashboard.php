<?php require __DIR__ . "/api/config.php"; start_session(); if (empty($_SESSION["staff_id"])) { header("Location: staff-login.html"); exit; } $staffName = htmlspecialchars($_SESSION["staff_name"] ?? "Staff"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Staff dashboard | Valerie Dental Practice, Tema</title>
<meta name="description" content="Staff reports.">
<meta name="theme-color" content="#085041">
<link rel="icon" type="image/svg+xml" href="images/favicon.svg">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,500;0,600;1,500&family=Inter:wght@400;500;600;700&display=swap">
<link rel="stylesheet" href="css/style.css">
<meta name="robots" content="noindex"></head>
<body>
<a class="skip" href="#main">Skip to main content</a>
<header class="site-header"><div class="wrap bar">
<a class="brand" href="index.html" aria-label="Valerie Dental Practice home"><img src="images/logo.svg" alt="" width="38" height="38">Valerie Dental</a>
<button class="nav-toggle" aria-expanded="false" aria-controls="primary-nav">Menu</button>
<nav id="primary-nav" class="primary-nav" aria-label="Main"><ul><li><a href="index.html">Home</a></li><li><a href="services.html">Services</a></li><li><a href="gallery.html">Gallery</a></li><li><a href="register.html">Register</a></li><li><a href="portal.html">My Appointments</a></li><li><a href="contact.html">Contact</a></li><li><a class="cta" href="book.html">Book now</a></li><li><a class="staff" href="staff-dashboard.php" aria-current="page">Dashboard</a></li></ul></nav>
</div></header>
<main id="main">
<div class="page-head"><div class="wrap"><ol class="crumbs" aria-label="Breadcrumb"><li><a href="index.html">Home</a></li><li aria-current="page">Staff dashboard</li></ol><h1>Staff dashboard</h1><p class="lead">Signed in as <?= $staffName ?> &middot; <button class="btn btn-amber btn-sm" id="logout" type="button">Log out</button></p></div></div><section id="dashboard"><div class="wrap">
<div class="stats"><div class="stat"><b id="sPatients">-</b>Registered patients</div><div class="stat"><b id="sToday">-</b>Appointments today</div><div class="stat"><b id="sUpcoming">-</b>Upcoming appointments</div><div class="stat"><b id="sUnread">-</b>Unread messages</div></div>
<div class="panel" style="margin-bottom:2rem"><h3 style="margin-bottom:1rem">Bookings by treatment</h3><div class="bars" id="bars"></div></div>
<div class="tabs" role="group" aria-label="Choose a report"><button type="button" data-type="future" aria-selected="false">Future appointments</button><button type="button" data-type="past" aria-selected="false">Past appointments</button><button type="button" data-type="patients" aria-selected="false">Patient list</button><button type="button" data-type="today" aria-selected="false">Today</button><button type="button" data-type="week" aria-selected="false">This week</button><button type="button" data-type="month" aria-selected="false">This month</button><button type="button" data-type="all" aria-selected="false">All</button><button type="button" data-type="patient" aria-selected="false">By patient</button><button type="button" data-type="messages" aria-selected="false">Messages</button></div>
<div class="tools"><div id="patientPick" style="display:none"><label for="patientSelect">Patient</label><select id="patientSelect"><option value="">Choose a patient...</option></select></div>
<div><label for="search">Search this report</label><input id="search" type="search" placeholder="Name, treatment, status..."></div>
<button class="btn btn-ghost btn-sm" id="csv" type="button">Export CSV</button><button class="btn btn-ghost btn-sm" id="print" type="button">Print</button></div>
<h2 id="reportTitle" style="font-size:1.6rem;margin-bottom:.4rem"></h2><p class="hint" id="count" aria-live="polite" style="margin-bottom:.8rem"></p>
<div class="table-wrap"><table class="data"><thead id="reportHead"></thead><tbody id="reportBody"></tbody></table></div></div></section>
</main>

<footer><div class="wrap"><div class="cols">
<div><a class="brand" href="index.html" style="color:#fff"><img src="images/logo.svg" alt="" width="38" height="38">Valerie Dental</a><p style="margin-top:1rem;max-width:34ch">Gentle, modern dental care for families in Tema and greater Accra.</p></div>
<div><h3>Visit</h3><ul><li><a href="services.html">Services</a></li><li><a href="gallery.html">Gallery</a></li><li><a href="contact.html">Find us</a></li></ul></div>
<div><h3>Patients</h3><ul><li><a href="register.html">Register</a></li><li><a href="book.html">Book online</a></li><li><a href="portal.html">My appointments</a></li><li><a href="privacy.html">Privacy &amp; your data</a></li></ul></div>
<div><h3>Contact</h3><ul><li>Harbour Road, Community 1, Tema</li><li><a href="tel:+233244000111">024 400 0111</a></li><li><a href="mailto:hello@valeriadental.example">hello@valeriadental.example</a></li><li><a href="staff-login.html">Staff login</a></li></ul></div>
</div><div class="legal"><span>&copy; <span class="year">2026</span> Valerie Dental Practice</span><span>Data handled under the Data Protection Act, 2012 (Act 843)</span></div></div></footer>
<script src="js/app.js"></script>
<script src="js/staff.js"></script></body></html>
