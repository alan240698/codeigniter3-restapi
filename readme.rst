## Project Description

---

**Project Name:** CodeIgniter 3 CRUD Application

**Production (using domain IP):** [http://72.60.194.250/](http://72.60.194.250/)

**Overview:**  
This is a simple CRUD (Create, Read, Update, Delete) web application built with **CodeIgniter 3** and **MySQL**. The application allows users to manage a collection of items with two main fields: **title** and **description**. It demonstrates basic functionality of a typical database-driven web application using the MVC (Model-View-Controller) and the Service Layer and Repository Pattern for the architecture of CodeIgniter.

**Features:**

*   **Create** – Users can add new entries by providing a title and description.
*   **Read** – Users can view a list of all entries stored in the database.
*   **Update** – Users can edit the title and description of existing entries.
*   **Delete** – Users can remove entries from the database.

**Validation Rules:**

*   **Title**: Required, minimum 2 characters, maximum 255 characters.
*   **Description**: Required, minimum 10 characters, maximum 65,000 characters.

---

### Technology Stack:

*   **Backend Framework**: CodeIgniter 3 (PHP 7.4)
*   **Database**: MySQL 8.0
*   **Web Server**: Apache
*   **Front-end**:  
    **Basic HTML, CSS, Bootstrap 5.3.0**, **Font Awesome 6.4.0**
*   **Libraries & Tools**:
    *   **Bootstrap 5.3.0**: For responsive front-end design
    *   **Font Awesome 6.4.0**: For icons
    *   **jQuery 3.7.0**: JavaScript library for easier DOM manipulation and AJAX
    *   **Bootstrap JS 5.3.0**: For Bootstrap's interactive components
    *   **SweetAlert2 11**: For beautiful and customizable alert boxes
    *   **Lodash 4.17.21**: For utility functions in JavaScript

---

### **Project Purpose:**

**This project is designed to demonstrate:**

*   Practical use of CodeIgniter 3 for CRUD operations.
*   Form validation and data sanitization.
*   Interaction with a MySQL database using CodeIgniter’s database library.
*   Basic MVC structure and routing in CodeIgniter.
*   Implement the Service Layer and Repository Pattern.

**Potential Enhancements:**

*   Implement pagination for listing entries.
*   Use AJAX for a smoother user experience.
*   Implement the Service Layer and Repository Pattern.

---

### **Deployment Instructions**

#### 1\. **Clone the Repository**

First, clone the repository to your local machine or server:

`git clone` [`https://github.com/alan240698/codeigniter3-restapi.git`](https://github.com/alan240698/codeigniter3-restapi.git)

`cd codeigniter3-restapi`

#### 2\. **Build and Start Containers with Docker Compose**

Build and start the containers using Docker Compose:

`docker-compose up --build -d`

This will build the Docker images and start the necessary containers for the application (including web, database, and phpMyAdmin services).

#### 3\. **Install Dependencies Using Composer**

Once the containers are up and running, install the required PHP dependencies using Composer:

`docker-compose exec web composer install`

#### 4\. **Run Migrations**

To apply database migrations, run the following command. Replace `<number>` with the version number of the migration you want to apply:

`docker-compose exec web php index.php migrate version <number>`

#### 5\. **Reset Migrations**

If you need to reset the migrations (e.g., to reapply all migrations from the start), you can use this command:

`docker-compose exec web php index.php migrate reset`

#### 6\. **Access the Application**

After successfully deploying the application, you can access it through the following links:

*   **Localhost (for development)**:  
    [http://localhost:8080/](http://localhost:8080/)
*   **phpMyAdmin (for database management)**:  
    [http://localhost:8081/](http://localhost:8081/)  
    **Login credentials**:
    *   **Username**: `ci_user`
    *   **Password**: `ci_password`
*   **Production (using domain IP)**:  
    [http://72.60.194.250/](http://72.60.194.250/)

---

### Notes:

*   Ensure that Docker and Docker Compose are installed on your system.
*   The application will run locally on port `8080` for the frontend and port `8081` for phpMyAdmin.
*   If you're deploying to a live server, make sure the necessary ports (8080, 8081) are open in the firewall.