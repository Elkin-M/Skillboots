<php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>SKILLBOOTS - Meet Our Expert Instructors</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Online Courses, E-learning, Education" name="keywords">
    <meta content="Discover SKILLBOOTS' world-class instructors who will guide you on your learning journey" name="description">

    <!-- Favicon -->
    <link href="../assets/img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="../lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="../assets/css/style.css" rel="stylesheet">

    <style>
        :root {
            --primary: #17a2b8;
            --secondary: #343a40;
            --light: #f8f9fa;
            --dark: #343a40;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            scroll-behavior: smooth;
        }
        
        .page-header {
            position: relative;
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('../assets/img/header-bg.jpg');
            background-position: center;
            background-size: cover;
            background-attachment: fixed;
        }
        
        .page-header::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 5px;
            bottom: 0;
            left: 0;
            background: linear-gradient(90deg, var(--primary), #6c757d, var(--primary));
        }
        
        .breadcrumb-item a {
            position: relative;
            text-decoration: none;
        }
        
        .breadcrumb-item a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -2px;
            left: 0;
            background-color: var(--primary);
            transition: width 0.3s;
        }
        
        .breadcrumb-item a:hover::after {
            width: 100%;
        }
        
        .section-title {
            position: relative;
            display: inline-block;
            letter-spacing: 1px;
            font-weight: bold;
            color: var(--primary);
        }
        
        .section-title::before {
            position: absolute;
            content: "";
            top: -10px;
            left: 0;
            width: 80px;
            height: 2px;
            background: var(--primary);
        }
        
        .section-title::after {
            position: absolute;
            content: "";
            bottom: -10px;
            left: 0;
            width: 120px;
            height: 2px;
            background: var(--primary);
        }
        
        .team-item {
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.1);
            transition: all 0.4s;
            overflow: hidden;
            border-radius: 10px;
        }
        
        .team-item:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }
        
        .team-img {
            overflow: hidden;
        }
        
        .team-img img {
            transition: transform 0.7s;
        }
        
        .team-item:hover .team-img img {
            transform: scale(1.1);
        }
        
        .team-text {
            position: relative;
            padding: 1.5rem;
            background-color: white;
            border-radius: 0 0 10px 10px;
            transition: all 0.4s;
        }
        
        .team-item:hover .team-text {
            background-color: var(--primary);
            color: white;
        }
        
        .team-item:hover .team-text h5,
        .team-item:hover .team-text p {
            color: white !important;
        }
        
        .team-social {
            position: absolute;
            width: 100%;
            height: 100%;
            top: -100%;
            left: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(0, 0, 0, 0.7);
            transition: 0.5s;
        }
        
        .team-item:hover .team-social {
            top: 0;
        }
        
        .team-social a {
            margin: 0 3px;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--light);
            border-radius: 50%;
            transition: 0.3s;
            transform: translateY(20px);
            opacity: 0;
        }
        
        .team-item:hover .team-social a {
            transform: translateY(0);
            opacity: 1;
        }
        
        .team-item:hover .team-social a:nth-child(1) {
            transition-delay: 0.1s;
        }
        
        .team-item:hover .team-social a:nth-child(2) {
            transition-delay: 0.2s;
        }
        
        .team-item:hover .team-social a:nth-child(3) {
            transition-delay: 0.3s;
        }
        
        .team-item:hover .team-social a:hover {
            background: var(--primary);
            border-color: var(--primary);
        }
        
        .team-specialty {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            margin-top: 0.5rem;
            border-radius: 20px;
            font-size: 0.875rem;
            background-color: rgba(23, 162, 184, 0.1);
            color: var(--primary);
            transition: all 0.4s;
        }
        
        .team-item:hover .team-specialty {
            background-color: white;
            color: var(--primary);
        }
        
        .team-review {
            font-size: 0.875rem;
            color: #ffc107;
            margin-bottom: 0.5rem;
        }
        
        .category-filter {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 30px;
        }
        
        .category-filter button {
            border: none;
            background: transparent;
            padding: 8px 20px;
            margin: 5px;
            border-radius: 30px;
            font-weight: 500;
            color: var(--dark);
            position: relative;
            transition: all 0.3s;
            overflow: hidden;
            z-index: 1;
        }
        
        .category-filter button::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 0;
            height: 100%;
            background-color: var(--primary);
            transition: 0.3s;
            z-index: -1;
        }
        
        .category-filter button:hover::before,
        .category-filter button.active::before {
            width: 100%;
        }
        
        .category-filter button:hover,
        .category-filter button.active {
            color: white;
        }
        
        .search-box {
            position: relative;
            margin-bottom: 30px;
        }
        
        .search-box input {
            width: 100%;
            height: 50px;
            border: 1px solid #ced4da;
            border-radius: 50px;
            padding: 0 20px 0 60px;
            font-size: 15px;
            color: var(--dark);
            transition: all 0.3s;
        }
        
        .search-box input:focus {
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            border-color: var(--primary);
            outline: none;
        }
        
        .search-box i {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 18px;
            color: var(--primary);
        }
        
        .stats-container {
            padding: 3rem 0;
            background: linear-gradient(rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.8)), url('../assets/img/stats-bg.jpg');
            background-attachment: fixed;
            background-size: cover;
            color: white;
            margin: 5rem 0;
        }
        
        .stat-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            text-align: center;
            border-right: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .stat-item:last-child {
            border-right: none;
        }
        
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--primary);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .stat-text {
            font-size: 1rem;
            opacity: 0.8;
        }
        
        @media (max-width: 768px) {
            .stat-item {
                border-right: none;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
                padding: 1.5rem 1rem;
            }
            
            .stat-item:last-child {
                border-bottom: none;
            }
        }
        
        .cta-section {
            background: linear-gradient(to right, var(--primary), #0c7f90);
            padding: 3rem 0;
            margin: 4rem 0 5rem;
            color: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .cta-section h2 {
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .cta-section p {
            opacity: 0.9;
            margin-bottom: 1.5rem;
        }
        
        .cta-btn {
            background-color: white;
            color: var(--primary);
            font-weight: 600;
            padding: 12px 30px;
            border-radius: 50px;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s;
            border: 2px solid white;
        }
        
        .cta-btn:hover {
            background-color: transparent;
            color: white;
        }
        
        .testimonial {
            position: relative;
            background-color: white;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        }
        
        .testimonial-quote {
            position: absolute;
            top: -20px;
            left: 20px;
            width: 40px;
            height: 40px;
            background-color: var(--primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
        
        .testimonial-text {
            font-style: italic;
            margin-bottom: 1.5rem;
            color: #6c757d;
        }
        
        .testimonial-info {
            display: flex;
            align-items: center;
        }
        
        .testimonial-info img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin-right: 1rem;
            object-fit: cover;
        }
        
        .testimonial-name {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .testimonial-position {
            font-size: 0.875rem;
            color: var(--primary);
        }
        
        /* Animation classes */
        .fadeInUp {
            animation: fadeInUp 0.5s ease-out forwards;
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
        
        /* Footer improvements */
        .footer-logo {
            max-width: 200px;
            margin-bottom: 1.5rem;
        }
        
        .footer-contact li {
            margin-bottom: 1rem;
            padding-left: 30px;
            position: relative;
        }
        
        .footer-contact li i {
            position: absolute;
            left: 0;
            top: 4px;
            color: var(--primary);
        }
        
        .footer-link {
            position: relative;
            display: block;
            padding: 5px 0;
            color: white;
            text-decoration: none;
        }
        
        .footer-link:hover {
            color: var(--primary);
            padding-left: 10px;
        }
        
        .footer-link::before {
            content: "\f105";
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            margin-right: 10px;
            color: var(--primary);
        }
        
        .newsletter-form input {
            height: 50px;
            border-radius: 5px 0 0 5px;
            border: none;
            padding: 10px 20px;
        }
        
        .newsletter-form button {
            height: 50px;
            border-radius: 0 5px 5px 0;
            border: none;
            background-color: var(--primary);
            color: white;
            font-weight: 600;
        }
        
        .social-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s;
            margin-right: 10px;
        }
        
        .social-link:hover {
            background-color: var(--primary);
            border-color: var(--primary);
            color: white;
        }
        
        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 20px;
            margin-top: 60px;
        }
        
        @media (max-width: 768px) {
            .footer-widget {
                margin-bottom: 2rem;
            }
        }
    </style>
</head>

<body>
<?php 
require_once '../auth/auth.php';

// Optimizar las llamadas a `Auth::isAuthenticated()`
$isLoggedIn = Auth::isAuthenticated();
$userRole = $isLoggedIn ? Auth::getUserRole() : 'visitante';
$userName = $isLoggedIn && isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';

// Definir los datos de la página
$pageData = [
    'isLoggedIn' => $isLoggedIn,
    'userRole' => $userRole,
    'userName' => $userName
];

// Incluir la navbar según el rol del usuario
if ($isLoggedIn && $userRole === 'estudiante') {
    include 'navbar-estu.php'; // Navbar para estudiantes
} else {
    include '../includes/navbar.php'; // Navbar para visitantes
}
?>

    <!-- Header Start -->
    <div class="container-fluid page-header" style="margin-bottom: 90px;">
        <div class="container">
            <div class="d-flex flex-column justify-content-center animate__animated animate__fadeIn" style="min-height: 300px">
                <h1 class="display-3 text-white text-uppercase">Our Expert Instructors</h1>
                <div class="d-inline-flex text-white my-3">
                    <p class="m-0 text-uppercase"><a class="text-white" href="../index.php">Home</a></p>
                    <i class="fa fa-angle-double-right pt-1 px-3"></i>
                    <p class="m-0 text-uppercase">Instructors</p>
                </div>
                <p class="text-white lead mb-4">Learn from industry professionals and expert educators</p>
                <a href="#instructors" class="btn btn-primary py-2 px-4 mt-3 animate__animated animate__fadeInUp animate__delay-1s">
                    <i class="fa fa-users mr-2"></i>Meet Our Team
                </a>
            </div>
        </div>
    </div>
    <!-- Header End -->

    <!-- Search and Filter Start -->
    <div class="container">
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="search-box">
                    <input type="text" id="instructor-search" placeholder="Search instructors...">
                    <i class="fas fa-search"></i>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="category-filter">
                    <button class="active" data-filter="all">All</button>
                    <button data-filter="web-design">Web Design</button>
                    <button data-filter="programming">Programming</button>
                    <button data-filter="marketing">Marketing</button>
                    <button data-filter="data-science">Data Science</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Search and Filter End -->

    <!-- Team Start -->
    <div class="container-fluid py-5" id="instructors">
        <div class="container pt-5 pb-3">
            <div class="text-center mb-5 wow fadeInUp" data-wow-delay="0.1s">
                <h5 class="section-title px-3">Our Instructors</h5>
                <h1 class="mb-3">Meet The Professionals Who Will Guide You</h1>
                <p class="text-muted">Our instructors are industry professionals with years of experience in their fields. They are passionate about teaching and dedicated to your success.</p>
            </div>

            <div class="row">
                <div class="col-md-6 col-lg-3 fadeInUp team mb-4" data-category="web-design">
                    <div class="team-item rounded overflow-hidden mb-2">
                        <div class="team-img position-relative">
                            <img class="img-fluid" src="../assets/img/team-1.jpg" alt="John Davis">
                            <div class="team-social">
                                <a class="btn btn-outline-light btn-square mx-1" href="#"><i class="fab fa-twitter"></i></a>
                                <a class="btn btn-outline-light btn-square mx-1" href="#"><i class="fab fa-facebook-f"></i></a>
                                <a class="btn btn-outline-light btn-square mx-1" href="#"><i class="fab fa-linkedin-in"></i></a>
                            </div>
                        </div>
                        <div class="team-text bg-white p-4">
                            <h5 class="text-dark mb-0">John Davis</h5>
                            <div class="team-review">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                                <span class="ml-1">(124)</span>
                            </div>
                            <p class="text-muted mb-2">Web Design Lead</p>
                            <div class="team-specialty">UX/UI Design</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 fadeInUp team mb-4" data-category="programming" data-wow-delay="0.2s">
                    <div class="team-item rounded overflow-hidden mb-2">
                        <div class="team-img position-relative">
                            <img class="img-fluid" src="../assets/img/team-2.jpg" alt="Sarah Johnson">
                            <div class="team-social">
                                <a class="btn btn-outline-light btn-square mx-1" href="#"><i class="fab fa-twitter"></i></a>
                                <a class="btn btn-outline-light btn-square mx-1" href="#"><i class="fab fa-facebook-f"></i></a>
                                <a class="btn btn-outline-light btn-square mx-1" href="#"><i class="fab fa-linkedin-in"></i></a>
                            </div>
                        </div>
                        <div class="team-text bg-white p-4">
                            <h5 class="text-dark mb-0">Sarah Johnson</h5>
                            <div class="team-review">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <span class="ml-1">(186)</span>
                            </div>
                            <p class="text-muted mb-2">Full Stack Developer</p>
                            <div class="team-specialty">React & Node.js</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 fadeInUp team mb-4" data-category="marketing" data-wow-delay="0.3s">
                    <div class="team-item rounded overflow-hidden mb-2">
                        <div class="team-img position-relative">
                            <img class="img-fluid" src="../assets/img/team-3.jpg" alt="Michael Brown">
                            <div class="team-social">
                                <a class="btn btn-outline-light btn-square mx-1" href="#"><i class="fab fa-twitter"></i></a>
                                <a class="btn btn-outline-light btn-square mx-1" href="#"><i class="fab fa-facebook-f"></i></a>
                                <a class="btn btn-outline-light btn-square mx-1" href="#"><i class="fab fa-linkedin-in"></i></a>
                            </div>
                        </div>
                        <div class="team-text bg-white p-4">
                            <h5 class="text-dark mb-0">Michael Brown</h5>
                            <div class="team-review">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="far fa-star"></i>
                                <span class="ml-1">(97)</span>
                            </div>
                            <p class="text-muted mb-2">Digital Marketing Expert</p>
                            <div class="team-specialty">SEO & PPC</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 fadeInUp team mb-4" data-category="data-science" data-wow-delay="0.4s">
                    <div class="team-item rounded overflow-hidden mb-2">
                        <div class="team-img position-relative">
                            <img class="img-fluid" src="../assets/img/team-4.jpg" alt="Emily Wilson">
                            <div class="team-social">
                                <a class="btn btn-outline-light btn-square mx-1" href="#"><i class="fab fa-twitter"></i></a>
                                <a class="btn btn-outline-light btn-square mx-1" href="#"><i class="fab fa-facebook-f"></i></a>
                                <a class="btn btn-outline-light btn-square mx-1" href="#"><i class="fab fa-linkedin-in"></i></a>
                            </div>
                        </div>
                        <div class="team-text bg-white p-4">
                            <h5 class="text-dark mb-0">Emily Wilson</h5>
                            <div class="team-review">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                                <span class="ml-1">(142)</span>
                            </div>
                            <p class="text-muted mb-2">Data Scientist</p>
                            <div class="team-specialty">Python & Machine Learning</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 fadeInUp team mb-4" data-category="web-design" data-wow-delay="0.5s">
                    <div class="team-item rounded overflow-hidden mb-2">
                        <div class="team-img position-relative">
                            <img class="img-fluid" src="../assets/img/team-1.jpg" alt="Robert Chen">
                            <div class="team-social">
                                <a class="btn btn-outline-light btn-square mx-1" href="#"><i class="fab fa-twitter"></i></a>
                                <a class="btn btn-outline-light btn-square mx-1" href="#"><i class="fab fa-facebook-f"></i></a>
                                <a class="btn btn-outline-light btn-square mx-1" href="#"><i class="fab fa-linkedin-in"></i></a>
                            </div>
                        </div>
                        <div class="team-text bg-white p-4">
                            <h5 class="text-dark mb-0">Robert Chen</h5>
                            <div class="team-review">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="far fa-star"></i>
                                <span class="ml-1">(78)</span>
                            </div>
                            <p class="text-muted mb-2">UI/UX Designer</p>
                            <div class="team-specialty">Adobe Creative Suite</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 fadeInUp team mb-4" data-category="programming" data-wow-delay="0.6s">
                    <div class="team-item rounded overflow-hidden mb-2">
                        <div class="team-img position-relative">
                            <img class="img-fluid" src="../assets/img/team-2.jpg" alt="Jennifer Lopez">
                            <div class="team-social">
                                <a class="btn btn-outline-light btn-square mx-1" href="#"><i class="fab fa-twitter"></i></a>
                                <a class="btn btn-outline-light btn-square mx-1" href="#"><i class="fab fa-facebook-f"></i></a>
                                <a class="btn btn-outline-light btn-square mx-1" href="#"><i class="fab fa-linkedin-in"></i></a>
                            </div>
                        </div>
                        <div class="team-text bg-white p-4">
                            <h5 class="text-dark mb-0">Jennifer Lopez</h5>
                            <div class="team-review">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <span class="ml-1">(201)</span>
                            </div>
                            <p class="text-muted mb-2">Mobile Developer</p>
                            <div class="team-specialty">iOS & Android</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 fadeInUp team mb-4" data-category="marketing" data-wow-delay="0.7s">
                    <div class="team-item rounded overflow-hidden mb-2">
                        <div class="team-img position-relative">
                            <img class="img-fluid" src="../assets/img/team-3.jpg" alt="David Kim">
                            <div class="team-social">
                                <a class="btn btn-outline-light btn-square mx-1" href="#"><i class="fab fa-twitter"></i></a>
                                <a class="btn btn-outline-light btn-square mx-1" href="#"><i class="fab fa-facebook-f"></i></a>
                                <a class="btn btn-outline-light btn-square mx-1" href="#"><i class="fab fa-linkedin-in"></i></a>
                            </div>
                        </div>
                        <div class="team-text bg-white p-4">
                            <h5 class="text-dark mb-0">David Kim</h5>
                            <div class="team-review">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                                <span class="ml-1">(115)</span>
                            </div>
                            <p class="text-muted mb-2">Content Marketing Specialist</p>
                            <div class="team-specialty">Social Media</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 fadeInUp team mb-4" data-category="data-science" data-wow-delay="0.8s">
                    <div class="team-item rounded overflow-hidden mb-2">
                        <div class="team-img position-relative">
                            <img class="img-fluid" src="../assets/img/team-4.jpg" alt="Laura Smith">
                            <div class="team-social">
                                <a class="btn btn-outline-light btn-square mx-1" href="#"><i class="fab fa-twitter"></i></a>
                                <a class="btn btn-outline-light btn-square mx-1" href="#"><i class="fab fa-facebook-f"></i></a>
                                <a class="btn btn-outline-light btn-square mx-1" href="#"><i class="fab fa-linkedin-in"></i></a>
                            </div>
                        </div>
                        <div class="team-text bg-white p-4">
                            <h5 class="text-dark mb-0">Laura Smith</h5>
                            <div class="team-review">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <span class="ml-1">(172)</span>
                            </div>
                            <p class="text-muted mb-2">Data Analyst</p>
                            <div class="team-specialty">SQL & Tableau</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Team End -->

    <!-- Stats Start -->
    <div class="container-fluid stats-container">
        <div class="container">
            <div class="row">
                <div class="col-md-3 col-sm-6">
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div class="stat-number" data-count="15000">0</div>
                        <div class="stat-text">Happy Students</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <div class="stat-number" data-count="120">0</div>
                        <div class="stat-text">Total Courses</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="stat-number" data-count="35">0</div>
                        <div class="stat-text">Expert Instructors</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-award"></i>
                        </div>
                        <div class="stat-number" data-count="25">0</div>
                        <div class="stat-text">Awards Won</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Stats End -->

    <!-- Student Testimonials Start -->
    <div class="container-fluid py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h5 class="section-title px-3">Testimonials</h5>
                <h2 class="mb-4">What Our Students Say About Our Instructors</h2>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <div class="testimonial">
                        <div class="testimonial-quote">
                            <i class="fas fa-quote-left"></i>
                        </div>
                        <p class="testimonial-text">Sarah is an amazing instructor! Her knowledge of full-stack development is impressive, and she explains complex concepts in a way that's easy to understand. Thanks to her course, I was able to land my first developer job.</p>
                        <div class="testimonial-info">
                            <img src="../assets/img/testimonial-1.jpg" alt="Student">
                            <div>
                                <h5 class="testimonial-name">Alex Parker</h5>
                                <p class="testimonial-position">Web Developer Student</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="testimonial">
                        <div class="testimonial-quote">
                            <i class="fas fa-quote-left"></i>
                        </div>
                        <p class="testimonial-text">Emily's data science course was transformative for my career. She has a unique ability to make complex statistical concepts accessible and engaging. Her practical approach to teaching has given me skills I use every day.</p>
                        <div class="testimonial-info">
                            <img src="../assets/img/testimonial-2.jpg" alt="Student">
                            <div>
                                <h5 class="testimonial-name">Jessica Wong</h5>
                                <p class="testimonial-position">Data Analysis Student</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Student Testimonials End -->

    <!-- Call to Action Start -->
    <div class="container">
        <div class="cta-section text-center">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h2>Ready to Start Learning with Our Expert Instructors?</h2>
                    <p>Join thousands of students who have already accelerated their careers with SKILLBOOTS courses. Get started today!</p>
                    <a href="/skillboots/templates/course.php" class="cta-btn">Browse Courses</a>
                </div>
            </div>
        </div>
    </div>
    <!-- Call to Action End -->

    <!-- Footer Start -->
    <div class="container-fluid bg-dark text-white py-5 px-sm-3 px-lg-5">
        <div class="row pt-5">
            <div class="col-lg-4 col-md-6 mb-5">
                <h5 class="text-primary text-uppercase mb-4" style="letter-spacing: 5px;">About Us</h5>
                <p>SKILLBOOTS is a leading online learning platform that helps anyone learn business, software, technology and creative skills to achieve personal and professional goals.</p>
                <div class="d-flex justify-content-start mt-4">
                    <a class="social-link" href="#"><i class="fab fa-twitter"></i></a>
                    <a class="social-link" href="#"><i class="fab fa-facebook-f"></i></a>
                    <a class="social-link" href="#"><i class="fab fa-linkedin-in"></i></a>
                    <a class="social-link" href="#"><i class="fab fa-instagram"></i></a>
                    <a class="social-link" href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            <div class="col-lg-2 col-md-6 mb-5">
                <h5 class="text-primary text-uppercase mb-4" style="letter-spacing: 5px;">Quick Links</h5>
                <div class="d-flex flex-column justify-content-start">
                    <a class="footer-link mb-2" href="#">About Us</a>
                    <a class="footer-link mb-2" href="#">Our Courses</a>
                    <a class="footer-link mb-2" href="#">Our Instructors</a>
                    <a class="footer-link mb-2" href="#">Contact Us</a>
                    <a class="footer-link" href="#">FAQs</a>
                </div>
            </div>
            <div class="col-lg-2 col-md-6 mb-5">
                <h5 class="text-primary text-uppercase mb-4" style="letter-spacing: 5px;">Popular Categories</h5>
                <div class="d-flex flex-column justify-content-start">
                    <a class="footer-link mb-2" href="#">Web Design</a>
                    <a class="footer-link mb-2" href="#">App Development</a>
                    <a class="footer-link mb-2" href="#">Digital Marketing</a>
                    <a class="footer-link mb-2" href="#">Data Science</a>
                    <a class="footer-link" href="#">Business</a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-5">
                <h5 class="text-primary text-uppercase mb-4" style="letter-spacing: 5px;">Contact Us</h5>
                <ul class="list-unstyled footer-contact">
                    <li><i class="fa fa-map-marker-alt"></i> 123 Street, New York, USA</li>
                    <li><i class="fa fa-phone-alt"></i> +012 345 67890</li>
                    <li><i class="fa fa-envelope"></i> info@skillboots.com</li>
                </ul>
                <h6 class="text-primary text-uppercase mt-4 mb-3">Newsletter</h6>
                <div class="w-100">
                    <div class="newsletter-form input-group">
                        <input type="text" class="form-control" placeholder="Your Email Address">
                        <div class="input-group-append">
                            <button class="btn btn-primary px-4">Sign Up</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid bg-dark text-white border-top py-4 px-sm-3 px-md-5" style="border-color: rgba(256, 256, 256, .1) !important;">
        <div class="row footer-bottom">
            <div class="col-lg-6 text-center text-md-left mb-3 mb-md-0">
                <p class="m-0 text-white">&copy; <a href="#">SKILLBOOTS</a>. All Rights Reserved. Designed by <a href="https://htmlcodex.com">HTML Codex</a></p>
            </div>
            <div class="col-lg-6 text-center text-md-right">
                <ul class="nav d-inline-flex">
                    <li class="nav-item">
                        <a class="nav-link text-white py-0" href="#">Privacy</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white py-0" href="#">Terms</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white py-0" href="#">FAQs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white py-0" href="#">Help</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <!-- Footer End -->

    <!-- Back to Top -->
    <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="fa fa-angle-double-up"></i></a>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../lib/easing/easing.min.js"></script>
    <script src="../lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/waypoints/4.0.1/jquery.waypoints.min.js"></script>

    <!-- Custom Javascript -->
    <script>
        $(document).ready(function() {
            // Animation on scroll
            function animateOnScroll() {
                $('.fadeInUp').each(function() {
                    var position = $(this).offset().top;
                    var scroll = $(window).scrollTop();
                    var windowHeight = $(window).height();
                    
                    if (scroll + windowHeight > position) {
                        $(this).addClass('animate__animated animate__fadeInUp');
                    }
                });
            }
            
            animateOnScroll();
            $(window).scroll(function() {
                animateOnScroll();
            });
            
            // Counter animation
            $('.stat-number').each(function() {
                var $this = $(this);
                var countTo = $this.attr('data-count');
                
                $({ countNum: 0 }).animate({
                    countNum: countTo
                }, {
                    duration: 2000,
                    easing: 'linear',
                    step: function() {
                        $this.text(Math.floor(this.countNum));
                    },
                    complete: function() {
                        $this.text(this.countNum);
                    }
                });
            });
            
            // Category filtering
            $('.category-filter button').click(function() {
                $('.category-filter button').removeClass('active');
                $(this).addClass('active');
                
                var filter = $(this).attr('data-filter');
                
                if (filter === 'all') {
                    $('.team').show(300);
                } else {
                    $('.team').hide(300);
                    $('.team[data-category="' + filter + '"]').show(300);
                }
            });
            
            // Search functionality
            $('#instructor-search').on('keyup', function() {
                var value = $(this).val().toLowerCase();
                $('.team').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
            });
            
            // Smooth scrolling for anchor links
            $('a[href^="#"]').on('click', function(e) {
                e.preventDefault();
                
                var target = this.hash;
                var $target = $(target);
                
                $('html, body').animate({
                    'scrollTop': $target.offset().top - 70
                }, 900, 'swing');
            });
            
            // Back to top button
            $(window).scroll(function() {
                if ($(this).scrollTop() > 100) {
                    $('.back-to-top').fadeIn('slow');
                } else {
                    $('.back-to-top').fadeOut('slow');
                }
            });
            
            $('.back-to-top').click(function() {
                $('html, body').animate({scrollTop: 0}, 1000, 'easeInOutExpo');
                return false;
            });
        });
    </script>
</body>

</html>