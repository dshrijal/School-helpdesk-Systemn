# 🎓 School Helpdesk System (🚧 In Development)

## 📌 Overview

The **School Helpdesk System** is a web-based application built using PHP and MySQL that helps students and administrators manage queries, complaints, and lost & found items efficiently.

This system improves communication within the school by providing a centralized platform for issue tracking and resolution. It is in developing process!!!!!!

---

## 🚀 Features

### 👨‍🎓 Student Panel

* 🔐 Login system
* 📝 Submit queries/issues
* 📦 Report lost items
* 🔍 View lost & found items
* 📊 Track query status
* 📄 View personal submissions

### 👨‍💼 Admin Panel

* 📋 View all queries
* 💬 Reply to student queries
* 🛠 Moderate lost & found items
* 📅 Manage dashboard & calendar
* 📊 System overview

---

## 🛠️ Technologies Used

* **Backend:** PHP
* **Database:** MySQL
* **Frontend:** HTML, CSS, JavaScript
* **Server:** Apache (XAMPP / MAMP / LAMP)

---

## 📂 Project Structure

```
School-helpdesk-Systemn/
│── portfolio/
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   └── images/
│   ├── components/
│   ├── docs/
│   ├── pages/
│   └── projects/
│
│── admin/
│   ├── dashboard.php
│   ├── all_queries.php
│   ├── reply_query.php
│   ├── moderate_lf.php
│   ├── calendar.php
│
│── student/
│   ├── login.php
│   ├── dashboard.php
│   ├── submit_query.php
│   ├── query_detail.php
│   ├── my_queries.php
│   ├── found_item.php
│   ├── lost_item.php
│   ├── lost_found_list.php
│   ├── logout.php
│
│── config/
│   └── db.php
│
│── includes/
│   └── sidebar.php
│
│── css/
│   ├── style.css
│   ├── login.css
│
│── js/
│   └── main.js
│
│── sql/
│   └── schema.sql
│
│── index.php
```

---

## ⚙️ Installation & Setup

### 1️⃣ Clone the Repository

```bash
git clone https://github.com/dshrijal/School-helpdesk-Systemn.git
```

### 2️⃣ Move to Server Directory

* For XAMPP:

```
htdocs/
```

* For MAMP:

```
Applications/MAMP/htdocs/
```

---

### 3️⃣ Import Database

1. Open **phpMyAdmin**
2. Create a database (e.g., `school_helpdesk`)
3. Import:

```
sql/schema.sql
```

---

### 4️⃣ Configure Database

Edit:

```
config/db.php
```

Update:

```php
$host = "localhost";
$user = "root";
$password = "";
$database = "school_helpdesk";
```

---

### 5️⃣ Run Project

Open in browser:

```
http://localhost/School-helpdesk-Systemn/
```

---

## 🔐 Default Roles

* **Admin** → Manages system
* **Student/User** → Submits queries & reports

---

## ⚠️ Project Status

🚧 This project is currently under development.
Some features may be incomplete or subject to change.

---

## 📌 Future Improvements

* 🔔 Email notifications
* 📱 Responsive UI improvements
* 🔒 Enhanced security (JWT / session handling)
* 📊 Analytics dashboard
* 🧾 File/image upload for queries

---

## 🤝 Contributing

Contributions are welcome! Feel free to fork this repository and submit a pull request.

---

## 📄 License

This project is open-source and available under the MIT License.

---
