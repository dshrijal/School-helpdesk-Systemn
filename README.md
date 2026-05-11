# 🎓 School Helpdesk System

A web-based helpdesk platform built with **PHP** and **MySQL** that lets students submit queries and administrators manage, reply to, and track them — all from a clean, responsive interface.

---

## ✨ Features

### 👨‍🎓 Student Panel
- Register and log in securely (bcrypt-hashed passwords)
- Submit help queries with a title, category, and description
- View all personal queries with **reply status badges** (✅ Replied / ⏳ Pending)
- Track query status: `Open`, `In Progress`, `Resolved`
- View full query details including admin replies
- Report and browse **Lost & Found** items

### 👨‍💼 Admin Panel
- View and filter all student queries by status (`Open`, `In Progress`, `Resolved`)
- **Search queries** by ID, title, or student name
- Reply to queries and update their status in one step
- Moderate Lost & Found submissions (approve / reject)
- Calendar view for scheduling
- System overview dashboard with query statistics

### 🔔 UX / System
- **Toast notifications** for all key actions (login, logout, registration, query submission, reply sent)
- **Client-side form validation** with real-time inline field errors on all forms
- Shared sidebar navigation and responsive layout
- Clean single-file CSS design system (`style.css`)

---

## 🛠️ Tech Stack

| Layer     | Technology                       |
|-----------|----------------------------------|
| Backend   | PHP 8+                           |
| Database  | MySQL 5.7+ / MariaDB             |
| Frontend  | HTML5, CSS3, Vanilla JavaScript  |
| Server    | Apache (XAMPP / MAMP / LAMP)     |

---

## 📂 Project Structure

```
helpdesk/
├── login.php               # Login page
├── register.php            # Student registration
├── logout.php              # Session teardown + redirect
├── dashboard_admin.php     # Admin home dashboard
├── dashboard_student.php   # Student home dashboard
├── admin_queries.php       # Admin: view, filter & search all queries
├── reply_query.php         # Admin: reply to a query
├── submit_query.php        # Student: submit a new query
├── my_queries.php          # Student: view own queries with reply status
├── query_detail.php        # View single query + admin reply
├── db.php                  # Database connection
├── style.css               # Global stylesheet
├── school_helpdesk.sql     # Database schema + seed data
├── includes/
│   ├── sidebar.php         # Shared navigation sidebar
│   └── toast.php           # Shared toast notification renderer
└── admin/
    ├── calendar.php        # Admin calendar
    └── moderate_lf.php     # Lost & Found moderation
```

---

## ⚙️ Installation & Setup

### 1. Clone the Repository

```bash
git clone https://github.com/your-username/school-helpdesk.git
```

### 2. Move to Your Server's Web Root

| Server | Path                            |
|--------|---------------------------------|
| XAMPP  | `C:/xampp/htdocs/school-helpdesk/` |
| MAMP   | `/Applications/MAMP/htdocs/school-helpdesk/` |
| LAMP   | `/var/www/html/school-helpdesk/` |

### 3. Import the Database

1. Open **phpMyAdmin** (usually at `http://localhost/phpmyadmin`)
2. Click **New** and create a database named `school_helpdesk`
3. Select that database → go to the **Import** tab
4. Choose `school_helpdesk.sql` and click **Go**

Or via the command line:

```bash
mysql -u root -p school_helpdesk < school_helpdesk.sql
```

### 4. Configure the Database Connection

Edit `db.php` and update the credentials if needed:

```php
$servername = "localhost";
$username   = "root";
$password   = "";          // your MySQL password
$dbname     = "school_helpdesk";
```

### 5. Open in Browser

```
http://localhost/school-helpdesk/login.php
```

---

## 🗄️ Database Schema

Three tables power the system:

**`users`** — stores both students and admins
```
id | name | email | password (bcrypt) | role (student|admin) | created_at
```

**`queries`** — help requests submitted by students
```
id | user_id | title | description | category (academic|technical|administrative)
   | status (open|in_progress|resolved) | reply | created_at | updated_at
```

**`lost_found`** — lost & found posts moderated by admins
```
id | user_id | type (lost|found) | status (pending|approved|rejected) | ...
```

---

## 🔐 Default Roles

| Role    | Access                                                    |
|---------|-----------------------------------------------------------|
| `admin` | Full access — view all queries, reply, moderate, calendar |
| `student` | Personal queries, submit, view replies, lost & found    |

Role is selected during registration. To manually create an admin, update the role column directly in the `users` table:

```sql
UPDATE users SET role = 'admin' WHERE email = 'admin@school.com';
```

---

## 📸 Key Pages

| Page                  | Who Sees It | Description                             |
|-----------------------|-------------|-----------------------------------------|
| `login.php`           | Everyone    | Login with email + password             |
| `register.php`        | New users   | Create a student account                |
| `dashboard_student.php` | Student   | Overview of submitted queries           |
| `my_queries.php`      | Student     | Full query list with reply/status badges|
| `query_detail.php`    | Student     | Single query view + admin reply         |
| `submit_query.php`    | Student     | Submit a new help query                 |
| `dashboard_admin.php` | Admin       | Stats + recent queries overview         |
| `admin_queries.php`   | Admin       | Filter + search all queries             |
| `reply_query.php`     | Admin       | Reply to a query and update its status  |

---

## 🚀 Roadmap

- [ ] Email notifications on reply
- [ ] File / image attachments for queries
- [ ] Pagination for large query lists
- [ ] Admin analytics dashboard (charts)
- [ ] Password reset via email
- [ ] JWT or improved session security

---

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/your-feature`
3. Commit your changes: `git commit -m "feat: add your feature"`
4. Push and open a Pull Request

---

## 📄 License

This project is open-source and available under the [MIT License](LICENSE).