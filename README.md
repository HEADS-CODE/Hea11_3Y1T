# Hea11_3Y1T 

# TECHNICAL FORMATIVE ASSESSMENT: FROM ZERO TO FOUR PAGES: YOUR FIRST CODEIGNITER APPLICATION

A basic four-page Point-of-Sale website created using CodeIgniter 4. It demonstrates routing, controllers, views, and  temporary array data to views.

## Requirements

- PHP
- Composer
- XAMPP with Apache
- CodeIgniter 4

## Hosted Application

The application is available at:

https://hea11-3y1t-myproject.onrender.com/

## Setup

1. Place the repository inside the XAMPP `htdocs` folder.
2. Open a terminal inside the `myproject` folder.
3. Install the required packages:

From powershell: composer install

4. Copy the `env` file and rename the copy to `.env`.
5. Configure the default settings of the `.env` into the following:

CI_ENVIRONMENT = development
app.baseURL = 'http://localhost/Hea11_3Y1T/myproject/public/'

6. Start Apache using the XAMPP Control Panel.
7. Open the application in a browser:
http://localhost/Hea11_3Y1T/myproject/public/

## Pages

- `/` - Home/Landing page
- `/about` - About page
- `/customers` - Customer Accounts page
- `/users` - User Accounts page

## Data Source

- This version does not use a database. 
- Customer and user records are stored in temporary PHP arrays inside their respective controllers.
- The Customer Accounts page displays each customer's full name, email address, and phone number. 
- The User Accounts page displays each user's username, full name, and role.