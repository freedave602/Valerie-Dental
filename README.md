# Valerie Dental Practice: XAMPP setup

## 1. Put the site in XAMPP
1. Copy the whole `valeria-dental` folder into `C:\xampp\htdocs\`
2. Open the XAMPP Control Panel and **Start Apache** and **Start MySQL** (both turn green).

## 2. Create the database
1. Go to http://localhost/phpmyadmin
2. Click **Import** > **Choose file** > `valeria-dental\database\valeria_dental.sql` > **Import**.
   This creates the `valeria_dental` database, the five tables, three report views, and sample data.

## 3. Check the connection
Open http://localhost/valeria-dental/api/test_connection.php
You should see `"success":true` and `"patients_in_database":4`.
If not, check `api/config.php`: user `root`, empty password (XAMPP default).

## 4. Use the site
Open **http://localhost/valeria-dental/** (always through `localhost`, never by double-clicking the .html files).

| Staff login | Password |
|---|---|
| staff@gmail.com | sharp123 |
| reception@clinic.com | frontdesk |

Sample patient for the portal: `ama.mensah@example.com`, born `1994-03-12`.

## Database structure
`patients` 1-to-many `appointments` many-to-1 `treatments`; `staff` (logins); `messages` (contact form + chat).
Views `v_patient_list`, `v_future_appointments`, `v_past_appointments` power the staff reports.
All queries are in `database/queries.sql`.

## Site map / navigation
Top navigation bar (hamburger menu on phones) + breadcrumbs + footer links + "skip to content" link.
index, services, gallery, register, book, portal, contact, privacy, staff-login, staff-dashboard.php

## Folder layout
`css/style.css` style sheet | `js/` behaviour | `api/` PHP endpoints | `images/` + `media/` (SVG art, video, map) | `database/` SQL

## Swap-in notes
- Clinic address, phone and email are placeholders: search and replace `Harbour Road`, `024 400 0111`, `valeriadental.example`.
- Replace the SVG illustrations in `images/` with real photos (keep the file names or update the `<img>` tags).
