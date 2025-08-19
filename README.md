#  PlanIt

A simple web-based Task Manager application that allows users to register, log in, create and manage projects, and organize tasks effectively.

## 🚀 Features

* User registration and authentication (email verification with OTP)
* Create, edit, and delete projects
* Add, update, and manage tasks within projects
* Assign due dates, priorities, and statuses to tasks
* Track project progress
* Responsive UI for easy access

## 🛠 Tech Stack

* **Frontend:** HTML, CSS, JavaScript
* **Backend:** PHP (Core PHP)
* **Database:** MySQL / MariaDB
* **Server:** XAMPP / Apache

## 📂 Project Structure

```
project-root/
│── db.php              # Database connection  
│── register.php        # User registration  
│── verify_otp.php      # OTP verification  
│── login.php           # User login  
│── dashboard.php       # User dashboard  
│── projects.php        # Manage projects  
│── tasks.php           # Manage tasks  
│── logout.php          # Logout user  
│── assets/             # CSS, JS, Images  
│── README.md           # Project documentation  
```

## ⚙️ Installation & Setup

1. Clone the repository or download the source code:

   ```bash
   git clone https://github.com/yourusername/task-manager.git
   cd task-manager
   ```
2. Import the SQL file into your MySQL/MariaDB:

   ```sql
   CREATE DATABASE task_manager;
   USE task_manager;
   SOURCE database/task_manager.sql;
   ```
3. Update database credentials in `db.php`:

   ```php
   $servername = "localhost";
   $username   = "root";
   $password   = "";
   $dbname     = "task_manager";
   ```
4. Run the project in XAMPP by placing it inside the `htdocs` folder and visiting:

   ```
   http://localhost/task-manager
   ```

## 👤 User Flow

1. **Register** with username, email, and password.
2. **Verify OTP** sent via email.
3. **Login** to access the dashboard.
4. **Create projects** and add tasks under them.
5. **Update task status** (Pending, In Progress, Completed).

## 🔑 Database Schema (Simplified)

### `users` table

| id | username | email | password | email\_verified | created\_at |
| -- | -------- | ----- | -------- | --------------- | ----------- |

### `projects` table

\| id | name | created\_by | created\_at |

### `tasks` table

\| id | project\_id | title | description | status | due\_date | created\_at |

## 📌 Future Enhancements

* User roles (Admin, Member)
* Task assignment to specific users
* Notifications & reminders
* Dark mode UI

---
