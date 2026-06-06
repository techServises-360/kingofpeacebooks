# KingOfPeace Books - Online Bookstore Platform

A modern, secure, and scalable online bookstore platform built with PHP and PostgreSQL, deployed on Render with Neon database.

## 🚀 **Features**

### 📚 **Core Functionality**
- **User Management** - Registration, email verification, roles (admin, author, user)
- **Book Management** - Upload, edit, approve books with pricing system
- **Payment Processing** - Secure Paystack integration
- **Review System** - User ratings and reviews
- **Admin Dashboard** - Complete admin control panel
- **Author Portal** - Dedicated author dashboard
- **Responsive Design** - Works on all devices

### 🛡️ **Security Features**
- **SQL Injection Protection** - Prepared statements throughout
- **XSS Protection** - Output encoding with htmlspecialchars()
- **CSRF Protection** - Tokens on all forms
- **Secure Passwords** - Strong hashing with PASSWORD_DEFAULT
- **Email Verification** - Required for registration
- **Role-Based Access** - Admin, author, user permissions
- **Session Security** - Secure session management

### 💰 **Payment System**
- **Paystack Integration** - Secure payment processing
- **Multiple Currencies** - Support for GHS, NGN, etc.
- **Order Management** - Complete order tracking
- **Payment Verification** - Secure webhook handling
- **Download Protection** - Secure file delivery

## 🏗️ **Technical Stack**

### 🌐 **Frontend**
- **PHP 8.x** - Modern PHP features
- **TailwindCSS** - Responsive, utility-first CSS
- **Vanilla JavaScript** - Lightweight, fast
- **Progressive Web App** - Mobile-optimized

### 🗄️ **Backend**
- **PostgreSQL** - Modern database (Neon)
- **Render** - Cloud hosting platform
- **Prepared Statements** - SQL injection protection
- **RESTful Architecture** - Clean API design

### 🚀 **Deployment**
- **Render** - Modern cloud platform
- **Neon PostgreSQL** - Serverless database
- **Supabase Storage** - Persistent file storage for covers and PDFs
- **Automatic HTTPS** - Free SSL certificates
- **Git Deployment** - CI/CD pipeline

## 📋 **Installation & Setup**

### 🚀 **Quick Deploy to Render**

#### **1. Clone Repository**
```bash
git clone https://github.com/IsraelTech-Pro/kingofpeacebooks.git
cd kingofpeacebooks
```

#### **2. Set Up Neon Database**
1. Create account at [neon.tech](https://neon.tech)
2. Create new PostgreSQL database
3. Copy connection details

#### **3. Deploy to Render**
1. Create account at [render.com](https://render.com)
2. Connect your GitHub repository
3. Set environment variables:
   ```bash
   NEON_DB_HOST=your-neon-host.neon.tech
   NEON_DB_PORT=5432
   NEON_DB_NAME=bookstore
   NEON_DB_USER=postgres
   NEON_DB_PASSWORD=your_password
   PAYSTACK_PUBLIC_KEY=pk_live_your_key
   PAYSTACK_SECRET_KEY=sk_live_your_key
   # Supabase Storage (persistent uploads)
   SUPABASE_URL=https://YOUR_PROJECT.supabase.co
   SUPABASE_SERVICE_ROLE_KEY=YOUR_SERVICE_ROLE_KEY
   SUPABASE_ANON_KEY=YOUR_ANON_KEY
   SUPABASE_BUCKET_COVERS=covers
   SUPABASE_BUCKET_BOOKS=books
   ```
4. Deploy automatically

#### **4. Run Database Migrations**
```bash
# Access your deployed app
# The migration script will create all tables automatically
```

### 🔧 **Local Development**

#### **Prerequisites**
- PHP 7.4+ with PDO and PostgreSQL extension
- PostgreSQL 12+ (or use Neon cloud)
- Composer (for dependency management)

#### **Setup**
```bash
# Clone repository
git clone https://github.com/IsraelTech-Pro/kingofpeacebooks.git
cd kingofpeacebooks

# Install dependencies
composer install

# Configure environment (Neon)
# Set these in your shell (or configure them in Render Environment Variables)
NEON_DB_HOST=your-neon-host.neon.tech
NEON_DB_PORT=5432
NEON_DB_NAME=bookstore
NEON_DB_USER=postgres
NEON_DB_PASSWORD=your_password

# Run migrations
php migrations/migrate.php

# Start development server
php -S localhost:8000 -t public
```

### ☁️ **Render (Docker) build/start**

This project is deployed on Render as a **Docker** service.

- **Build** is handled by the `Dockerfile`
- **Start** is handled by `start.sh` (installs dependencies, runs migrations, starts PHP server via `router.php`)

## 📁 **Project Structure**

```
bookstore/
├── 📄 README.md                 # This file
├── 📄 render.yaml              # Render deployment config
├── 📄 composer.json            # PHP dependencies
├── 📁 public/                  # Web root
│   ├── index.php               # Front controller
│   ├── register.php            # User registration
│   ├── login.php              # User login
│   ├── book.php               # Book details
│   ├── profile.php            # User profile
│   └── ...                   # Other public files
├── 📁 admin/                   # Admin panel
│   ├── dashboard.php          # Admin dashboard
│   ├── manage-books.php       # Book management
│   ├── manage-users.php       # User management
│   └── ...                   # Other admin files
├── 📁 author/                  # Author portal
│   ├── dashboard.php          # Author dashboard
│   └── upload-book.php       # Book upload
├── 📁 app/                     # Application core
│   ├── config/               # Configuration files
│   ├── controllers/          # Business logic
│   ├── models/               # Database models
│   ├── helpers/              # Utility functions
│   └── services/             # External services
├── 📁 migrations/               # Database migrations
│   ├── migrate.php            # Migration runner
│   ├── 001_create_users_table.sql
│   ├── 002_create_books_table.sql
│   ├── 003_create_orders_table.sql
│   └── 004_create_reviews_table.sql
├── 📁 assets/                   # Static assets
│   ├── images/               # Book covers
│   ├── css/                  # Stylesheets
│   └── js/                   # JavaScript files
├── 📁 includes/                # Shared components
│   ├── header.php             # Site header
│   ├── footer.php             # Site footer
│   └── navbar.php             # Navigation
└── 📁 .git/                    # Version control
```

## 🗄️ **Database Schema**

### 📊 **Tables Overview**

#### **Users Table**
- User management with roles and permissions
- Email verification system
- Author request workflow
- Account suspension management

#### **Books Table**
- Book information and metadata
- Pricing system (base + discount)
- File management (covers + PDFs)
- Approval workflow

#### **Orders Table**
- Payment processing with Paystack
- Order tracking and management
- User purchase history

#### **Reviews Table**
- Rating system (1-5 stars)
- User comments and feedback
- One review per user per book

## 🛡️ **Security**

### 🔒 **Implemented Measures**
- **SQL Injection Prevention** - All queries use prepared statements
- **XSS Protection** - All outputs escaped with htmlspecialchars()
- **CSRF Protection** - Tokens on all forms
- **Input Validation** - All user inputs sanitized
- **Password Security** - Strong hashing with PASSWORD_DEFAULT
- **Session Security** - Secure session configuration
- **File Upload Security** - Type validation and secure storage
- **HTTPS Enforcement** - Automatic SSL redirection

## 🗂️ **Supabase Storage (persistent uploads)**

Render's filesystem is ephemeral, so uploaded files must be stored externally to persist across deploys.

### Buckets

- **`covers` (public)**
  - Stores cover images.
  - App stores `books.cover_image` as a full public URL.

- **`books` (private)**
  - Stores PDF files.
  - App stores `books.file_path` as the **object key** (e.g. `book_...pdf`).
  - Downloads are served via **short-lived signed URLs** after verifying payment.

### Required Environment Variables

Set these in Render → Web Service → **Environment**:

```bash
SUPABASE_URL=https://YOUR_PROJECT.supabase.co
SUPABASE_SERVICE_ROLE_KEY=YOUR_SERVICE_ROLE_KEY
SUPABASE_ANON_KEY=YOUR_ANON_KEY
SUPABASE_BUCKET_COVERS=covers
SUPABASE_BUCKET_BOOKS=books
```

### Notes

- The app uses the **Service Role Key** server-side for uploads and signed URL generation. Keep it secret.
- If Supabase is not configured, the app falls back to local file storage (not recommended on Render).

### 🔐 **Authentication & Authorization**
- **Multi-Role System** - Admin, Author, User
- **Email Verification** - Required for registration
- **Account Suspension** - Admin control over user access
- **Author Approval** - Admin approval for author status
- **Secure Sessions** - Proper session management

## 💳 **Payment Integration**

### 💰 **Paystack Features**
- **Secure Payments** - Industry-standard encryption
- **Multiple Currencies** - Support for GHS, NGN, USD
- **Webhook Handling** - Secure payment verification
- **Order Tracking** - Complete purchase history
- **Refund Support** - Automated refund processing
- **Download Protection** - Secure file delivery after payment

## 🚀 **Deployment**

### ☁️ **Render + Neon Architecture**
- **Modern Cloud Stack** - Serverless + container-based
- **Automatic Scaling** - Handle traffic spikes
- **Global CDN** - Fast worldwide access
- **Free SSL** - Automatic HTTPS certificates
- **Git Deployment** - CI/CD pipeline
- **Environment Variables** - Secure configuration

### � **Performance**
- **PostgreSQL Optimization** - Efficient queries and indexes
- **Connection Pooling** - Database performance
- **CDN Integration** - Fast asset delivery
- **Caching Strategy** - Improved load times
- **Responsive Design** - Mobile optimization

## 📱 **Features**

### 📚 **User Experience**
- **Responsive Design** - Works on all devices
- **Progressive Web App** - Mobile-optimized experience
- **Book Search** - Full-text search capabilities
- **User Reviews** - Rating and review system
- **Wishlist** - Save books for later
- **Purchase History** - Order tracking

### 👥 **Admin Features**
- **Dashboard Analytics** - Sales and user statistics
- **Book Management** - Approve/edit/delete books
- **User Management** - Roles and permissions
- **Order Management** - Track all purchases
- **Review Moderation** - Manage user reviews

### ✍️ **Author Features**
- **Book Upload** - Easy book submission
- **Sales Dashboard** - Track book performance
- **Author Profile** - Manage author information
- **Review Responses** - Engage with readers

## 🧪 **Testing**

### 🧪 **Quality Assurance**
- **Security Testing** - SQL injection, XSS, CSRF protection
- **Performance Testing** - Load and stress testing
- **Cross-Browser Testing** - Chrome, Firefox, Safari, Edge
- **Mobile Testing** - iOS, Android responsive design
- **Payment Testing** - Paystack integration verification

## 📞 **Support**

### 📋 **Documentation**
- **[NEON-SETUP.md](NEON-SETUP.md)** - Complete Neon setup guide
- **[RENDER-SETUP.md](RENDER-SETUP.md)** - Render deployment guide
- **Inline Comments** - Code documentation throughout

### 🐛 **Getting Help**
1. **Check Documentation** - Review setup guides
2. **Test Locally** - Verify functionality before deployment
3. **Check Logs** - Review error logs for issues
4. **Community Support** - GitHub issues for bug reports

## 📄 **License**

This project is licensed under the MIT License - see LICENSE file for details.

## 🤝 **Contributing**

Contributions are welcome! Please:
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

## 🎉 **Acknowledgments**

- **Render** - Cloud hosting platform
- **Neon** - PostgreSQL database service
- **Paystack** - Payment processing
- **TailwindCSS** - CSS framework
- **Google Apps Script** - Email verification service

---

**🚀 KingOfPeace Books - Modern, Secure, Scalable Online Bookstore Platform**

Built with ❤️ using PHP, PostgreSQL, and modern web technologies.
