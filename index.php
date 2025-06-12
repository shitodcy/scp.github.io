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

// --- START: PHP Headers for Best Practices ---
// These headers MUST be sent BEFORE any HTML output.
// Place this right after `require_once 'config/database.php';` in your production environment.
// Ensure your server is already configured for HTTPS before enabling HSTS.

// Example: Content Security Policy (CSP) - Mitigates XSS, controls resource loading
// This is a strict example. You might need to adjust 'script-src' and 'style-src'
// for any inline scripts/styles you HAVE to use, or if other domains are involved.
// Start with 'report-uri' to test without blocking.
// header("Content-Security-Policy: default-src 'self'; " .
//        "script-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://static.elfsight.com; " .
//        "style-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; " .
//        "img-src 'self' https://res.cloudinary.com https://placehold.co data:; " .
//        "frame-src https://www.google.com https://www.youtube.com; " . // Adjust frame-src for exact map domain if needed
//        "form-action 'self'; object-src 'none'; base-uri 'self';");

// Example: HTTP Strict Transport Security (HSTS) - Forces HTTPS (requires site already on HTTPS)
// This header should only be uncommented once you are CONFIDENT your site is fully HTTPS.
// header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");

// Example: X-Frame-Options - Mitigates Clickjacking
// header("X-Frame-Options: SAMEORIGIN"); // Prevents framing by other sites, allows same-origin. Use DENY to prevent all framing.

// Example: Cross-Origin-Opener-Policy (COOP) - Isolates your site from others (advanced)
// header("Cross-Origin-Opener-Policy: same-origin"); // Might break some legitimate pop-up/cross-origin interactions. Test thoroughly.

// --- END: PHP Headers for Best Practices ---

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kedai Kopi Kayu - Kopi Nikmat & Suasana Nyaman di Yogyakarta</title>
    <meta name="description" content="Kedai Kopi Kayu adalah destinasi unik bagi pecinta kopi di Yogyakarta. Nikmati kopi berkualitas tinggi, suasana nyaman, dan pelayanan ramah.">
    <link rel="icon" href="https://res.cloudinary.com/dbdmqec1q/image/upload/v1748598314/logokkk_rtchku.ico" type="image/x-icon">
    
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://static.elfsight.com">
    <link rel="preconnect" href="https://res.cloudinary.com">
    <link rel="preconnect" href="https://www.google.com">
    <link rel="preconnect" href="https://maps.google.com">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css">

    <link rel="stylesheet" href="assets/css/main.css">


</head>

<body>
 <nav class="navbar navbar-dark bg-dark" aria-label="Main Navigation">
    <div class="top-row">
      <a class="navbar-brand" href="#home">Kedai Kopi Kayu</a>
      <button class="toggle-btn" aria-label="Toggle navigation menu">
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
  
  <section id="home">
    <div class="content-container">
      <div class="image-box">
        <div id="imageCarousel" class="carousel slide" data-bs-ride="carousel">
          <div class="carousel-indicators">
            <button type="button" data-bs-target="#imageCarousel" data-bs-slide-to="0" class="active" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#imageCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#imageCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
          </div>
          <div class="carousel-inner">
            <div class="carousel-item active">
              <img src="https://res.cloudinary.com/dbdmqec1q/image/upload/f_auto,q_auto/v1748598325/fotolokasi.depan_av71fx.webp"
                   alt="Bagian depan Kedai Kopi Kayu yang cozy"
                   width="1200" height="800" class="d-block w-100">
            </div>
            <div class="carousel-item">
              <img src="https://res.cloudinary.com/dbdmqec1q/image/upload/f_auto,q_auto/v1748598328/fotolokasidaridalam_jdtdyf.webp"
                   alt="Suasana interior Kedai Kopi Kayu"
                   width="1200" height="800" class="d-block w-100">
            </div>
            <div class="carousel-item">
              <img src="https://res.cloudinary.com/dbdmqec1q/image/upload/f_auto,q_auto/v1748598324/foto_dapur_fvvsvn.webp"
                   alt="Dapur bersih di Kedai Kopi Kayu"
                   width="1200" height="800" class="d-block w-100">
            </div>
          </div>
          <button class="carousel-control-prev" type="button" data-bs-target="#imageCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#imageCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
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

 <section id="about" class="py-5">
    <div class="container">
      <div class="row g-4 justify-content-center about-row">
        <h2 class="mb-4 text text-center">History</h2>
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
  
        <div class="col-12 col-md-5 d-flex justify-content-center">
          <div class="card-custom p-4 text-center panel-main slide-up" data-delay="0">
            <div class="text-center mb-3">
              <img src="https://res.cloudinary.com/dbdmqec1q/image/upload/f_auto,q_auto/v1748598324/foto_dapur_fvvsvn.webp"
                   class="img-rounded mx-auto d-block"
                   alt="Interior dapur Kedai Kopi Kayu"
                   width="600" height="400" loading="lazy" />
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

<section id="menu">
    <div class="container-menu">
        <div class="text-center">
            <h2 class="h1-menu text">Menu</h2>
        </div>
        
        <div class="menu-controls">
            <div class="filter-buttons">
                <button class="filter-btn active" data-target="all">Semua</button>
                <button class="filter-btn" data-target="coffee">Kopi</button>
                <button class="filter-btn" data-target="tea">Teh</button>
                <button class="filter-btn" data-target="snack">Snack</button>
            </div>
        </div>
        
        <div class="category-section menu-section" id="coffeeSection">
            <h3 class="category-title" data-target="coffeeContent">
                <span>Kopi</span>
                <span class="toggle-icon">▼</span>
            </h3>
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
                                    // Optimized Cloudinary URL (f_auto, q_auto for format & quality)
                                    $image_src = str_replace("upload/", "upload/f_auto,q_auto/", htmlspecialchars($item['image_url']));
                                } else {
                                    // Otherwise, assume it's a local file path
                                    $image_src = '../public/uploads/menu_images/' . htmlspecialchars($item['image_url']);
                                }
                            }
                            ?>
                            <div class="menu-item" data-category="coffee">
                                <img src="<?php echo $image_src; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="menu-item-image" width="100" height="100" loading="lazy">
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
        
        <div class="category-section menu-section" id="teaSection">
            <h3 class="category-title" data-target="teaContent">
                <span>Teh</span>
                <span class="toggle-icon">▼</span>
            </h3>
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
                                     // Optimized Cloudinary URL (f_auto, q_auto for format & quality)
                                    $image_src = str_replace("upload/", "upload/f_auto,q_auto/", htmlspecialchars($item['image_url']));
                                } else {
                                    // Otherwise, assume it's a local file path
                                    $image_src = '../public/uploads/menu_images/' . htmlspecialchars($item['image_url']);
                                }
                            }
                            ?>
                            <div class="menu-item" data-category="tea">
                                <img src="<?php echo $image_src; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="menu-item-image" width="100" height="100" loading="lazy">
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
        
        <div class="category-section menu-section" id="snackSection">
            <h3 class="category-title" data-target="snackContent">
                <span>Snack</span>
                <span class="toggle-icon">▼</span>
            </h3>
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
                                     // Optimized Cloudinary URL (f_auto, q_auto for format & quality)
                                    $image_src = str_replace("upload/", "upload/f_auto,q_auto/", htmlspecialchars($item['image_url']));
                                } else {
                                    // Otherwise, assume it's a local file path
                                    $image_src = '../public/uploads/menu_images/' . htmlspecialchars($item['image_url']);
                                }
                            }
                            ?>
                            <div class="menu-item" data-category="snack">
                                <img src="<?php echo $image_src; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="menu-item-image" width="100" height="100" loading="lazy">
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
        </div>
</section>

    
  <section id="team" class="team-section text-center">
    <div class="container d-flex flex-column justify-content-center align-items-center" style="min-height: 100vh;">
        <h2 class="mb-4 h2 text">SCP9242 Team</h2>
        <div class="row justify-content-center">
            <div class="col-12 d-flex flex-wrap justify-content-center">

                <div class="col-sm-6 col-md-4 col-lg mb-4 px-3">
                    <div class="team-member">
                        <img src="https://res.cloudinary.com/dbdmqec1q/image/upload/f_auto,q_auto/v1748598238/putra_lasuzf.webp"
                             alt="Arya Putra, Team Leader"
                             width="200" height="200" loading="lazy">
                        <h4>Arya Putra</h4>
                        <p>23.11.5494</p>
                        <div class="job-info">Team Leader</div>
                    </div>
                </div>

                <div class="col-sm-6 col-md-4 col-lg mb-4 px-3">
                    <div class="team-member">
                        <img src="https://res.cloudinary.com/dbdmqec1q/image/upload/f_auto,q_auto/v1748598239/jihan_cudegg.webp"
                             alt="Jihan Humaira, Front-end Developer"
                             width="200" height="200" loading="lazy">
                        <h4>Jihan Humaira</h4>
                        <p>23.11.5492</p>
                        <div class="job-info">Front end</div>
                    </div>
                </div>

                <div class="col-sm-6 col-md-4 col-lg mb-4 px-3">
                    <div class="team-member">
                        <img src="https://res.cloudinary.com/dbdmqec1q/image/upload/f_auto,q_auto/v1748598241/irul_wieaib.webp"
                             alt="Khairul Fikri, Front-end Developer"
                             width="200" height="200" loading="lazy">
                        <h4>Khairul Fikri</h4>
                        <p>23.11.5442</p>
                        <div class="job-info">Front end</div>
                    </div>
                </div>

                <div class="col-sm-6 col-md-6 col-lg mb-4 px-3">
                    <div class="team-member">
                        <img src="https://res.cloudinary.com/dbdmqec1q/image/upload/f_auto,q_auto/v1748598239/sobron_viupaj.webp"
                             alt="Alfan Sobron, UI/UX Designer"
                             width="200" height="200" loading="lazy">
                        <h4>Alfan Sobron</h4>
                        <p>23.11.5438</p>
                        <div class="job-info">UI/UX</div>
                    </div>
                </div>

                <div class="col-sm-6 col-md-6 col-lg mb-4 px-3">
                    <div class="team-member">
                        <img src="https://res.cloudinary.com/dbdmqec1q/image/upload/f_auto,q_auto/v1748598239/zaki_iccbj2.webp"
                             alt="Zaky Riefani, UI/UX Designer"
                             width="200" height="200" loading="lazy">
                        <h4>Zaky Riefani</h4>
                        <p>23.11.5446</p>
                        <div class="job-info"></div>
                    </div>
               </div>

            </div>
        </div>
    </div>
</section>


     <section id="location" class="section">
        <div class="content">            
            <h2 class="mb-4 h2-menu text">Lokasi Kami</h2>
            <div class="map-container">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3953.078440784013!2d110.37039017417537!3d-7.794697992225381!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e7a5789f9e5c7a3%3A0xe5a2d0c2e9b0b4a4!2sTugu%20Pal%20Putih!5e0!3m2!1sid!2sid!4v1719216000000!5m2!1sid!2sid"
                width="600"
                height="450"
                style="border:0;"
                allowfullscreen=""
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                title="Lokasi Kedai Kopi Kayu di Google Maps">
                </iframe>
            </div>
          </div>
       </section>

    <section id="review" class="review">
        <div class="text-center">
            <h2 class="mb-4 h1-menu text">Customer Review</h2>
        </div>
        <script src="https://static.elfsight.com/platform/platform.js" data-use-service-core defer></script>
            <div class="elfsight-app-a42bc0b6-9aab-4160-b16e-98ab29e6e21a" data-elfsight-app-lazy></div>
        </div>

        <div class="row justify-content-center">
            <div class="col-12 text-center contact-section">
                <h5>Contact Us</h5>
                <div class="social-icons d-flex justify-content-center gap-4">
                    <a href="https://api.whatsapp.com/send/?phone=622133759908" target="_blank" rel="noopener noreferrer" aria-label="Hubungi kami via WhatsApp">
                        <i class="fa-brands fa-whatsapp fa-2x"></i>
                    </a>
                    <a href="https://www.instagram.com/kedaikopikayu" target="_blank" rel="noopener noreferrer" aria-label="Ikuti kami di Instagram">
                        <i class="fa-brands fa-instagram fa-2x"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center" aria-label="Scroll ke atas"><i class="bi bi-arrow-up-short"></i></a>
<footer class="footer">
    <div class="container text-center">
        <div class="row justify-content-center">
            <div class="row">
                <div class="col-12 footer-logo">
                    <a href="/auth/login.php" aria-label="Halaman Login Admin">
                        <img src="https://res.cloudinary.com/dbdmqec1q/image/upload/f_auto,q_auto/v1748598314/logof_xww7ju.png" alt="SCP9242 Logo" width="80" height="80">
                    </a>
                    <p>©SCP9242. All rights reserved.</p>
                </div>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<script src="assets/js/about.js" defer></script>
<script src="assets/js/menu.js" defer></script>
<script src="assets/js/home.js" defer></script>
<script src="assets/js/team.js" defer></script>
<script src="assets/js/navbar.js" defer></script>

</body>
</html>