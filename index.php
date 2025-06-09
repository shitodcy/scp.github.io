<?php
// Include database connection
require_once 'config/database.php';

// Fetch menu items from database
$menu_items_db = [];
try {
    $stmt = $conn->prepare("SELECT id, name, price, category, image_url FROM menu_items WHERE is_active = TRUE ORDER BY category, name ASC");
    $stmt->execute();
    $menu_items_db = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching menu items for frontend: " . $e->getMessage());
    // In a production environment, you might display a user-friendly error message
    // or load default/placeholder menu items.
}

$categorized_menu = [
    'coffee' => [],
    'tea' => [],
    'snack' => [],
    'other' => [] // Include 'other' category if you plan to use it
];

foreach ($menu_items_db as $item) {
    if (isset($categorized_menu[$item['category']])) {
        $categorized_menu[$item['category']][] = $item;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <!-- Favicon -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kedai Kopi Kayu</title>
    <link rel="icon" href="https://res.cloudinary.com/dbdmqec1q/image/upload/v1748598314/logokkk_rtchku.ico" type="image/x-icon">
    

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- SwiperJS CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <!-- public/index.html -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css">


    <!-- CSS Custom -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/scrollup.css">
    <link rel="stylesheet" href="assets/css/menu.css">
    <link rel="stylesheet" href="assets/css/map.css">
    <link rel="stylesheet" href="assets/css/home.css">
    <link rel="stylesheet" href="assets/css/team.css">
    <link rel="stylesheet" href="assets/css/about.css">
    <link rel="stylesheet" href="assets/css/review.css">

    <!-- JavaScript Custom -->
    <script src="assets/js/main.js"></script>
    <script src="assets/js/about.js"></script>
    <script src="assets/js/menu.js"></script>
    <script src="assets/js/home.js"></script>
    <script src="assets/js/team.js"></script>
    <script src="assets/js/navbar.js"></script>
</head>

<body>
 <!-- Navbar dengan struktur yang lebih bersih -->
<nav class="navbar navbar-dark bg-dark">
    <div class="top-row">
      <a class="navbar-brand" href="index.html">Kedai Kopi Kayu</a>
      <button class="toggle-btn" aria-label="Toggle navigation">
        <span>☰</span>
      </button>
    </div>
    <div class="menu-items">
      <a href="#home" class="active">Home</a>
      <a href="#about">About</a>
      <a href="#menu">Menu</a>
      <a href="#team">Team</a>
      <a href="#location">Location</a>
      <a href="#review">Review</a>
    </div>
  </nav>
  
  <!-- Home-->
  <section id="home">
    <div class="content-container">
      <div class="image-box">
        <div id="imageCarousel" class="carousel slide" data-bs-ride="carousel">
          <div class="carousel-indicators">
            <button type="button" data-bs-target="#imageCarousel" data-bs-slide-to="0" class="active"></button>
            <button type="button" data-bs-target="#imageCarousel" data-bs-slide-to="1"></button>
            <button type="button" data-bs-target="#imageCarousel" data-bs-slide-to="2"></button>
          </div>
          <div class="carousel-inner">
            <div class="carousel-item active">
              <img src="https://res.cloudinary.com/dbdmqec1q/image/upload/v1748598325/fotolokasi.depan_av71fx.webp">
            </div>
            <div class="carousel-item">
              <img src="https://res.cloudinary.com/dbdmqec1q/image/upload/v1748598328/fotolokasidaridalam_jdtdyf.webp">
            </div>
            <div class="carousel-item">
              <img src="https://res.cloudinary.com/dbdmqec1q/image/upload/v1748598324/foto_dapur_fvvsvn.webp">
            </div>
          </div>
          <button class="carousel-control-prev" type="button" data-bs-target="#imageCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
            <span class="visually-hidden">Previous</span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#imageCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
            <span class="visually-hidden">Next</span>
          </button>
        </div>
      </div>
      <div class="text-content">
        <div class="highlight-title">
          <h1>Kedai Kopi Kayu</h1>
        </div>
        <p>adalah destinasi unik bagi para pecinta kopi yang ingin merasakan pengalaman menikmati kopi dengan sentuhan tradisional dan modern. Kami menghadirkan suasana yang nyaman, cita rasa khas, serta kehangatan khas Yogyakarta dalam setiap cangkir kopi.</p>
        <p>Nikmati berbagai pilihan kopi single origin dan racikan spesial kami yang diolah dengan penuh dedikasi oleh barista berpengalaman.</p>
      </div>
    </div>
  </section>

 <!-- Section About -->
<section id="about" class="py-5">
    <div class="container">
      <div class="row g-4 justify-content-center about-row">
        <h2 class="mb-4 text text-center">History</h2>
  
        <!-- Panel 1 - Visi -->
        <div class="col-10 col-md-3 d-flex justify-content-center">
          <div class="panel-wrapper">
            <div class="card card-custom p-3 text-center panel-small slide-up" data-delay="100">
              <h3 class="celebration-title mb-4">Visi</h3>
              <div class="celebration-text">
                <p>Kami menghadirkan secangkir kopi dengan cita rasa khas yang membangkitkan semangat dan menyempurnakan harimu.</p>
                <p>Menjadi coffeeshop pilihan dengan kopi terbaik, pelayanan ramah, dan suasana yang mendukung setiap momen berharga.</p>
                <p>Kami menyatukan semua kalangan dalam pengalaman kopi yang tak terlupakan.</p>
              </div>
            </div>
          </div>
        </div>
  
        <!-- Panel 2 - Main -->
        <div class="col-12 col-md-5 d-flex justify-content-center">
          <div class="card-custom p-4 text-center panel-main slide-up" data-delay="0">
            <div class="text-center mb-3">
              <img src="https://res.cloudinary.com/dbdmqec1q/image/upload/v1748598324/foto_dapur_fvvsvn.webp" class="img-rounded mx-auto d-block" alt="Menu Image" loading="lazy" />
            </div>
            <p class="text-custom fs-5 mb-2">SINCE 2024</p>
            <h3 class="celebration-title mb-3">Kedai Kopi Kayu</h3>
            <div class="scrollable-content" id="scrollContainer">
              <div class="scroll-text" id="scrollText">
                <p class="text-custom fs-8">
                  Tempat ini dibangun oleh sekelompok mahasiswa yang memiliki visi untuk menciptakan
                  tempat berkumpul yang nyaman dan menyajikan kopi berkualitas tinggi.
                </p>
                <p class="text-custom fs-8">
                  Dengan menggunakan biji kopi pilihan dari petani lokal, kami berkomitmen untuk
                  memberikan pengalaman kopi autentik. Desain interior kami menggabungkan elemen kayu
                  alami yang menciptakan suasana hangat dan mengundang.
                </p>
                <p class="text-custom fs-8">
                  Selain menyajikan kopi, kami juga menawarkan berbagai pastry dan makanan ringan yang
                  dibuat segar setiap hari. Kami percaya bahwa kopi adalah lebih dari sekedar minuman,
                  tapi juga tentang pengalaman dan komunitas.
                </p>
                <p class="text-custom fs-8">
                  Kedai Kopi Kayu hadir sebagai ruang di mana cerita-cerita baru tercipta, ide-ide
                  mengalir, dan persahabatan terjalin. Bergabunglah dengan kami dan rasakan perbedaannya!
                </p>
              </div>
            </div>
          </div>
        </div>

            <!-- Panel 3 -->
            <div class="col-10 col-md-3 d-flex justify-content-center">
                <div class="panel-wrapper">
                  <div class="card card-custom p-3 text-center panel-small slide-up" data-delay="100">
                    <h3 class="celebration-title mb-4">Misi</h3>
                    <div class="celebration-text">
                            <p>Memberikan pelayanan yang ramah, cepat, dan profesional kepada setiap pelanggan dengan sepenuh hati.</p>
                            <p>Menciptakan suasana yang nyaman, hangat, dan inspiratif sebagai tempat bersantai, bekerja, maupun berkumpul bersama.</p>
                            <p>Menempatkan kepuasan pelanggan sebagai prioritas utama demi menciptakan keberhasilan dan keberlanjutan usaha.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>


<!--menu-->
<div id="menu">
    <div class="container-menu">
        <div class="text-center">
            <h1 class="h1-menu text">Menu</h1>
        </div>
        
        <!-- Filter controls -->
        <div class="menu-controls">
            <div class="filter-buttons">
                <button class="filter-btn active" data-target="all">Semua</button>
                <button class="filter-btn" data-target="coffee">Kopi</button>
                <button class="filter-btn" data-target="tea">Teh</button>
                <button class="filter-btn" data-target="snack">Snack</button>
            </div>
        </div>
        
        <!-- Coffee Section -->
        <div class="category-section menu-section" id="coffeeSection">
            <div class="category-title" data-target="coffeeContent">
                <span>Kopi</span>
                <span class="toggle-icon">▼</span>
            </div>
            <div class="category-content" id="coffeeContent">
                <div class="menu-items-container">
                    <?php if (!empty($categorized_menu['coffee'])): ?>
                        <?php foreach ($categorized_menu['coffee'] as $item): ?>
                            <?php
                            // Determine the correct image source
                            $image_src = 'https://placehold.co/100x100/cccccc/ffffff?text=No+Img'; // Default placeholder
                            if (!empty($item['image_url'])) {
                                // Check if it's a full URL
                                if (filter_var($item['image_url'], FILTER_VALIDATE_URL)) {
                                    $image_src = htmlspecialchars($item['image_url']);
                                } else {
                                    // Otherwise, assume it's a local file path
                                    $image_src = '../public/uploads/menu_images/' . htmlspecialchars($item['image_url']);
                                }
                            }
                            ?>
                            <div class="menu-item" data-category="coffee">
                                <img src="<?php echo $image_src; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="menu-item-image">
                                <div class="menu-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                <div class="menu-price">Rp<?php echo number_format($item['price'], 0, ',', '.'); ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Belum ada menu Kopi yang tersedia.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Tea Section -->
        <div class="category-section menu-section" id="teaSection">
            <div class="category-title" data-target="teaContent">
                <span>Teh</span>
                <span class="toggle-icon">▼</span>
            </div>
            <div class="category-content" id="teaContent">
                <div class="menu-items-container">
                    <?php if (!empty($categorized_menu['tea'])): ?>
                        <?php foreach ($categorized_menu['tea'] as $item): ?>
                            <?php
                            // Determine the correct image source
                            $image_src = 'https://placehold.co/100x100/cccccc/ffffff?text=No+Img'; // Default placeholder
                            if (!empty($item['image_url'])) {
                                // Check if it's a full URL
                                if (filter_var($item['image_url'], FILTER_VALIDATE_URL)) {
                                    $image_src = htmlspecialchars($item['image_url']);
                                } else {
                                    // Otherwise, assume it's a local file path
                                    $image_src = '../public/uploads/menu_images/' . htmlspecialchars($item['image_url']);
                                }
                            }
                            ?>
                            <div class="menu-item" data-category="tea">
                                <img src="<?php echo $image_src; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="menu-item-image">
                                <div class="menu-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                <div class="menu-price">Rp<?php echo number_format($item['price'], 0, ',', '.'); ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Belum ada menu Teh yang tersedia.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Snack Section -->
        <div class="category-section menu-section" id="snackSection">
            <div class="category-title" data-target="snackContent">
                <span>Snack</span>
                <span class="toggle-icon">▼</span>
            </div>
            <div class="category-content" id="snackContent">
                <div class="menu-items-container">
                    <?php if (!empty($categorized_menu['snack'])): ?>
                        <?php foreach ($categorized_menu['snack'] as $item): ?>
                            <?php
                            // Determine the correct image source
                            $image_src = 'https://placehold.co/100x100/cccccc/ffffff?text=No+Img'; // Default placeholder
                            if (!empty($item['image_url'])) {
                                // Check if it's a full URL
                                if (filter_var($item['image_url'], FILTER_VALIDATE_URL)) {
                                    $image_src = htmlspecialchars($item['image_url']);
                                } else {
                                    // Otherwise, assume it's a local file path
                                    $image_src = '../public/uploads/menu_images/' . htmlspecialchars($item['image_url']);
                                }
                            }
                            ?>
                            <div class="menu-item" data-category="snack">
                                <img src="<?php echo $image_src; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="menu-item-image">
                                <div class="menu-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                <div class="menu-price">Rp<?php echo number_format($item['price'], 0, ',', '.'); ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Belum ada menu Snack yang tersedia.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!-- You can add more sections for 'other' categories if needed -->
    </div>
</div>


    
  <!-- Section Team -->
<section id="team" class="team-section text-center">
    <div class="container d-flex flex-column justify-content-center align-items-center" style="min-height: 100vh;">
        <h2 class="mb-4 h2 text">SCP9242 Team</h2>
        <div class="row justify-content-center">
            <div class="col-12 d-flex flex-wrap justify-content-center">

                <!-- Member 1 -->
                <div class="col-sm-6 col-md-4 col-lg mb-4 px-3">
                    <div class="team-member">
                        <img src="https://res.cloudinary.com/dbdmqec1q/image/upload/v1748598238/putra_lasuzf.webp" alt="Arya Putra">
                        <h4>Arya Putra</h4>
                        <p>23.11.5494</p>
                        <div class="job-info">Team Leader</div>
                    </div>
                </div>

                <!-- Member 2 -->
                <div class="col-sm-6 col-md-4 col-lg mb-4 px-3">
                    <div class="team-member">
                        <img src="https://res.cloudinary.com/dbdmqec1q/image/upload/v1748598239/jihan_cudegg.webp" alt="Jihan Humaira">
                        <h4>Jihan Humaira</h4>
                        <p>23.11.5492</p>
                        <div class="job-info">Front end</div>
                    </div>
                </div>

                <!-- Member 3 -->
                <div class="col-sm-6 col-md-4 col-lg mb-4 px-3">
                    <div class="team-member">
                        <img src="https://res.cloudinary.com/dbdmqec1q/image/upload/v1748598241/irul_wieaib.webp" alt="Khairul Fikri">
                        <h4>Khairul Fikri</h4>
                        <p>23.11.5442</p>
                        <div class="job-info">Front end</div>
                    </div>
                </div>

                <!-- Member 4 -->
                <div class="col-sm-6 col-md-6 col-lg mb-4 px-3">
                    <div class="team-member">
                        <img src="https://res.cloudinary.com/dbdmqec1q/image/upload/v1748598239/sobron_viupaj.webp" alt="Alfan Sobron">
                        <h4>Alfan Sobron</h4>
                        <p>23.11.5438</p>
                        <div class="job-info">UI/UX</div>
                    </div>
                </div>

                <!-- Member 5 -->
               <div class="col-sm-6 col-md-6 col-lg mb-4 px-3">
                    <div class="team-member">
                        <img src="https://res.cloudinary.com/dbdmqec1q/image/upload/v1748598239/zaki_iccbj2.webp" alt="Zaky Riefani">
                        <h4>Zaky Riefani</h4>
                        <p>23.11.5446</p>
                        <div class="job-info">UI/UX</div>
                    </div>
               </div>

            </div>
        </div>
    </div>
</section>


     <!-- Section Lokasi Kami -->
    <section id="location" class="section">
        <div class="content">            
            <h2 class="mb-4 h2-menu text">Lokasi Kami</h2>
            <div class="map-container">
                <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d247.04902789701276!2d110.3952731!3d-7.812628!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e7a575ba14d6821%3A0xab8b0d0941110aea!2sKedai%20Kopi%20Kayu!5e0!3m2!1sid!2sid!4v1745582487163!5m2!1sid!2sid" 
                width="600"
                height="450"
                style="border:0;" 
                allowfullscreen="" 
                loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
            </div>
          </div>
       </section>

    <!-- Section Review -->
    <section id="review" class="review">
        <div class="text-center">
            <h1 class="mb-4 h1-menu text">Customer Review</h1>
        </div>
        <!-- Reviews Section -->
            <script src="https://static.elfsight.com/platform/platform.js" async></script>
            <div class="elfsight-app-a42bc0b6-9aab-4160-b16e-98ab29e6e21a" data-elfsight-app-lazy></div>
        </div>

        <!-- Contact Section -->
        <div class="row justify-content-center">
            <div class="col-12 text-center contact-section">
                <h5>Contact Us</h5>
                <div class="social-icons d-flex justify-content-center gap-4">
                    <a href="https://api.whatsapp.com/send/?phone=622133759908" target="_blank">
                        <i class="fa-brands fa-whatsapp fa-2x"></i>
                    </a>
                    <a href="https://www.instagram.com/kedaikopikayu" target="_blank">
                        <i class="fa-brands fa-instagram fa-2x"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Scroll Top -->
    <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
    
  <!-- Footer -->
<footer class="footer">
    <div class="container text-center">
        <div class="row justify-content-center">
            <!-- Logo dan Hak Cipta -->
            <div class="row">
                <div class="col-12 footer-logo">
                    <a href="/auth/login.php">
                        <img src="https://res.cloudinary.com/dbdmqec1q/image/upload/v1748598314/logof_xww7ju.png" alt="SCP9242 Logo" width="80">
                    </a>
                    <p>©SCP9242. All rights reserved.</p>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap 5 JS (Moved to end of body for performance) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE JS (If still needed for some reason, though less likely for frontend) -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

</body>
</html>
