<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>دکتر ابوالفضل صفرعلیزاده | متخصص کودکان و نوزادان</title>
    <!-- Bootstrap 5 CSS -->
     <link href="{{ asset('matabgaleb/assets/css/bootstrap.min.css') }}" rel="stylesheet"
          integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Vazir Font -->
    <link href="{{ asset('matabgaleb/profile.css') }}" rel="stylesheet" />

     <link rel="icon" type="image/png" href="/matabgaleb/assets/fav/favicon-48x48.png" sizes="48x48"/>
    <link rel="icon" type="image/svg+xml" href="/matabgaleb/assets/fav/favicon.svg"/>
    <link rel="shortcut icon" href="/matabgaleb/assets/fav/favicon.ico"/>
    <link rel="apple-touch-icon" sizes="180x180" href="/matabgaleb/assets/fav/apple-touch-icon.png"/>
    <link rel="manifest" href="/matabgaleb/assets/fav/site.webmanifest"/>
  
    <style>
        :root {
            --primary-color: #137c72;
            --secondary-color: #f5f8f6;
            --accent-color: #0c5e56;
            --text-color: #233d39;
            --light-text: #70847f;
        }
        
        body {
            font-family: iran, Tahoma, sans-serif;
            background-color: var(--secondary-color);
            color: var(--text-color);
            line-height: 1.6;
        }
        
        .profile-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fc 100%);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        
        .profile-card:hover {
            transform: translateY(-5px);
        }
        
        .profile-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .profile-img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid white;
            object-fit: cover;
            margin: 0 auto;
            display: block;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .social-section {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }
        
        .social-section:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .social-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            display: inline-block;
            transition: transform 0.3s ease;
        }
        
        .social-icon:hover {
            transform: scale(1.1);
        }
        
        .instagram {
            background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .btn-instagram {
            background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888);
            border: none;
            color: white;
            font-weight: 500;
        }
        
        .btn-instagram:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 39, 67, 0.4);
        }
        
        .contact-info {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .contact-icon {
            color: var(--primary-color);
            font-size: 1.2rem;
            margin-left: 0.5rem;
        }
        
        .address-icon {
            color: var(--primary-color);
            font-size: 1.5rem;
            vertical-align: middle;
            margin-left: 0.5rem;
        }
        
        .section-title {
            position: relative;
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
            color: var(--primary-color);
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            width: 50px;
            height: 3px;
            background: linear-gradient(to right, var(--primary-color), var(--accent-color));
            border-radius: 3px;
        }
        
        .btn-call {
            background-color: #28a745;
            color: white;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-call:hover {
            background-color: #218838;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
        }
        
        .map-link {
            color: var(--primary-color);
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .map-link:hover {
            color: var(--accent-color);
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .profile-img {
                width: 100px;
                height: 100px;
            }
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="profile-card">
                    <div class="profile-header">
                        <img src="https://kodakteb.ir/wp-content/uploads/2025/03/Screenshot_20250304_201725_Instagram.jpg" alt="دکتر ابوالفضل صفرعلیزاده" class="profile-img mb-3">
                        <h1 class="h3 mb-2">دکتر ابوالفضل صفرعلیزاده</h1>
                        <h2 class="h5 mb-0">متخصص کودکان و نوزادان</h2>
                    </div>
                    
                    <div class="p-4">
                        <!-- اینستاگرام -->
                        <div class="social-section text-center">
                            <h3 class="section-title text-center">پیج اینستاگرام</h3>
                            <i class="fab fa-instagram social-icon instagram"></i>
                            <p class="mb-3">@doctor.safaralizade.khoy</p>
                            <a href="instagram://user?username=doctor.safaralizade.khoy" 
                               class="btn btn-instagram btn-lg w-75">
                                <i class="fab fa-instagram me-2"></i> اینستاگرام
                            </a>
                        </div>
                        
                        <!-- اطلاعات تماس -->
                        <div class="contact-info">
                            <h3 class="section-title">اطلاعات تماس</h3>
                            <div class="mb-4">
                                <h5 class="d-flex align-items-center">
                                    <i class="fas fa-phone-alt contact-icon"></i>
                                    <span>شماره تماس مطب:</span>
                                </h5>
                                <a href="tel:04436368185" class="btn btn-call btn-lg mt-2">
                                    <i class="fas fa-phone-alt me-2"></i>
                                    044-3636-8185
                                </a>
                            </div>
                            
                            <div class="mb-3">
                                <h5 class="d-flex align-items-center">
                                    <i class="fas fa-map-marker-alt contact-icon"></i>
                                    <span>آدرس مطب:</span>
                                </h5>
                                <p class="mb-2">
                                    آذربایجان غربی - خوی - فلکه دلفین
                                    <br>
                                    مجتمع آنا آتا، طبقه ۴، واحد ۴۰۹
                                </p>
                                <a href="https://maps.google.com/?q=مجتمع+آنا+آتا+خوی" 
                                   class="map-link" 
                                   target="_blank">
                                    <i class="fas fa-map-marked-alt address-icon"></i>
                                    مشاهده روی نقشه
                                </a>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
   <script type="module" src="{{asset('matabgaleb/assets/js/all.min.js')}}"></script>
</body>
</html>
