<style>
    /* Modern Student Page Styles - Keep Background */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    html {
        scroll-behavior: smooth;
    }

    /* Modern Header */
    .modern-header {
        background: linear-gradient(135deg, #1a2b4a 0%, #2c5364 100%);
        backdrop-filter: blur(10px);
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.3);
        position: sticky;
        top: 0;
        z-index: 1000;
        border-bottom: 4px solid #d4af37;
    }

    .header-top {
        padding: 1.5rem 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 2rem;
    }

    .header-top .container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 2rem;
    }

    .university-info {
        display: flex;
        align-items: center;
        gap: 1.5rem;
    }

    .university-logo {
        width: 70px;
        height: 70px;
        object-fit: contain;
        filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.1));
    }

    .university-name h1 {
        font-size: 1.5rem;
        font-weight: 900;
        color: #ffffff;
        margin: 0;
        line-height: 1.2;
        text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        letter-spacing: -0.5px;
    }

    .university-name p {
        font-size: 1rem;
        color: #ffd700;
        font-weight: 700;
        margin: 0.25rem 0 0 0;
        text-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
        letter-spacing: 0.3px;
    }

    /* User Dropdown */
    .user-dropdown {
        position: relative;
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1.25rem;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50px;
        cursor: pointer;
        transition: all 0.3s ease;
        color: white;
    }

    .user-info:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #d4af37 0%, #f4d03f 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
        font-size: 1.25rem;
        color: #1a2b4a;
    }

    .dropdown-menu {
        position: absolute;
        top: calc(100% + 0.5rem);
        right: 0;
        background: white;
        border-radius: 15px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        min-width: 220px;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: all 0.3s ease;
        z-index: 1000;
    }

    .user-dropdown.active .dropdown-menu {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .dropdown-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1rem 1.25rem;
        color: #1a2b4a;
        text-decoration: none;
        transition: all 0.3s ease;
        font-weight: 600;
    }

    .dropdown-item:hover {
        background: rgba(212, 175, 55, 0.1);
    }

    .dropdown-item.logout {
        color: #dc3545;
    }

    .dropdown-item.logout:hover {
        background: rgba(220, 53, 69, 0.1);
    }

    .dropdown-divider {
        height: 1px;
        background: rgba(0, 0, 0, 0.1);
        margin: 0.5rem 0;
    }

    /* Navigation */
    .main-nav {
        background: #1a2b4a;
    }

    .nav-menu {
        list-style: none;
        display: flex;
        gap: 0;
        margin: 0;
        padding: 0;
    }

    .nav-menu li a {
        display: block;
        padding: 1rem 1.5rem;
        color: white;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        position: relative;
    }

    .nav-menu li a::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        width: 0;
        height: 3px;
        background: #d4af37;
        transition: all 0.3s ease;
        transform: translateX(-50%);
    }

    .nav-menu li a:hover,
    .nav-menu li a.active {
        background: rgba(212, 175, 55, 0.1);
        color: #d4af37;
    }

    .nav-menu li a:hover::after,
    .nav-menu li a.active::after {
        width: 80%;
    }

    /* Main Content */
    .main-content {
        flex: 1;
        position: relative;
        z-index: 100;
        padding: 2rem 0;
        min-height: calc(100vh - 300px);
    }

    /* Content Wrapper */
    .content-wrapper {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(20px);
        border-radius: 25px;
        padding: 2.5rem;
        box-shadow: 0 15px 50px rgba(0, 0, 0, 0.4);
        border: 2px solid rgba(212, 175, 55, 0.3);
        margin-bottom: 2rem;
        animation: fadeInUp 0.8s ease;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .content-wrapper > h1 {
        font-size: 2.5rem;
        font-weight: 900;
        color: #1a2b4a;
        margin: 0 0 1rem 0;
    }

    .text-secondary {
        color: #6c757d;
        font-size: 1.15rem;
        margin-bottom: 2rem;
    }

    /* Cards */
    .card {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(20px);
        border-radius: 20px;
        padding: 0;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        border: 2px solid rgba(212, 175, 55, 0.3);
        overflow: hidden;
        margin-bottom: 1.5rem;
    }

    .card-header {
        background: linear-gradient(135deg, #1a2b4a 0%, #2c5364 100%);
        padding: 1.5rem 2rem;
        border-bottom: 3px solid #d4af37;
    }

    .card-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: white;
        margin: 0;
    }

    /* Info List */
    .info-list {
        padding: 2rem;
    }

    .info-item {
        display: flex;
        justify-content: space-between;
        padding: 1rem 0;
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    }

    .info-item:last-child {
        border-bottom: none;
    }

    .info-label {
        font-weight: 700;
        color: #1a2b4a;
    }

    .info-value {
        color: #6c757d;
        font-weight: 600;
    }

    /* Buttons */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 1rem 2rem;
        font-size: 1.125rem;
        font-weight: 700;
        text-decoration: none;
        border-radius: 50px;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        font-family: 'Poppins', sans-serif;
    }

    .btn-primary {
        background: linear-gradient(135deg, #1a2b4a 0%, #2c5364 100%);
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(26, 43, 74, 0.4);
    }

    .btn-secondary {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        color: white;
    }

    .btn-secondary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4);
    }

    .btn-success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
    }

    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
    }

    /* Grid */
    .grid {
        display: grid;
        gap: 2rem;
    }

    .grid-2 {
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    }

    .mt-4 {
        margin-top: 2rem;
    }

    /* Status Badges */
    .status-badge {
        display: inline-block;
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-weight: 700;
        font-size: 0.9rem;
    }

    .status-active {
        background: rgba(40, 167, 69, 0.1);
        color: #28a745;
        border: 2px solid #28a745;
    }

    .status-inactive {
        background: rgba(220, 53, 69, 0.1);
        color: #dc3545;
        border: 2px solid #dc3545;
    }

    /* Alert */
    .alert {
        padding: 1.25rem 1.5rem;
        border-radius: 15px;
        margin-bottom: 1.5rem;
        border-left: 4px solid;
    }

    .alert-info {
        background: rgba(23, 162, 184, 0.1);
        border-color: #17a2b8;
        color: #0c5460;
    }

    .alert-danger {
        background: rgba(220, 53, 69, 0.1);
        border-color: #dc3545;
        color: #721c24;
    }

    /* Footer */
    .modern-footer {
        background: rgba(26, 43, 74, 0.98);
        backdrop-filter: blur(10px);
        color: white;
        padding: 1.5rem 0;
        margin-top: auto;
        border-top: 3px solid #d4af37;
        position: relative;
        z-index: 1000;
    }

    .footer-content {
        text-align: center;
    }

    .footer-content p {
        margin: 0;
        font-size: 1rem;
        color: rgba(255, 255, 255, 0.8);
    }

    /* Profile */
    .profile-form {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(20px);
        border-radius: 25px;
        padding: 3rem 2.5rem;
        box-shadow: 0 15px 50px rgba(0, 0, 0, 0.4);
        border: 2px solid rgba(212, 175, 55, 0.3);
        animation: fadeInUp 0.8s ease;
    }

    .profile-header {
        text-align: center;
        margin-bottom: 3rem;
        padding-bottom: 2rem;
        border-bottom: 3px solid #d4af37;
    }

    .profile-avatar-large {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: linear-gradient(135deg, #1a2b4a 0%, #2c5364 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
        font-size: 3rem;
        color: white;
        margin: 0 auto 1.5rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    }

    .profile-header h1 {
        font-size: 2.5rem;
        font-weight: 900;
        color: #1a2b4a;
        margin: 0 0 0.5rem 0;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .university-name h1 {
            font-size: 1.1rem;
        }

        .university-name p {
            font-size: 0.85rem;
        }

        .nav-menu {
            flex-wrap: wrap;
        }

        .nav-menu li a {
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
        }

        .content-wrapper > h1 {
            font-size: 2rem;
        }

        .grid-2 {
            grid-template-columns: 1fr;
        }
    }
</style>
