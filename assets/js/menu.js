document.addEventListener("DOMContentLoaded", () => {
  const filterButtons = document.querySelectorAll(".filter-btn");
  const categorySections = document.querySelectorAll(".category-section");
  const categoryTitles = document.querySelectorAll(".category-title");
  const categoryContents = document.querySelectorAll(".category-content");

  // Fungsi untuk menampilkan semua section
  const showAllSections = () => {
    categoryContents.forEach(content => {
      content.classList.add("active");
      content.parentElement.querySelector('.category-title').setAttribute('aria-expanded', true);
    });
  };

  // Default: tampilkan semua saat halaman dimuat
  showAllSections();

  // Filter tombol
  filterButtons.forEach(btn => {
    btn.addEventListener("click", () => {
      // Aktifkan tombol yang diklik
      filterButtons.forEach(b => b.classList.remove("active"));
      btn.classList.add("active");

      const target = btn.dataset.target;

      categorySections.forEach(section => {
        const items = section.querySelectorAll(".menu-item");
        let visible = false;

        items.forEach((item, index) => {
          const itemCategory = item.dataset.category;
          if (target === "all" || itemCategory === target) {
            item.style.display = "block";
            item.style.setProperty("--item-index", index);
            visible = true;
          } else {
            item.style.display = "none";
          }
        });

        const content = section.querySelector(".category-content");
        const title = section.querySelector(".category-title");

        // Expand section kalau masih ada itemnya
        if (visible) {
          content.classList.add("active");
          title.setAttribute("aria-expanded", true);
        } else {
          content.classList.remove("active");
          title.setAttribute("aria-expanded", false);
        }
      });
    });
  });

  // Toggle untuk expand/collapse tiap kategori
  categoryTitles.forEach(title => {
    title.addEventListener("click", () => {
      const targetId = title.dataset.target;
      const content = document.getElementById(targetId);
      const isActive = content.classList.contains("active");

      // Toggle expand/collapse
      content.classList.toggle("active", !isActive);
      title.setAttribute("aria-expanded", !isActive);
    });
  });
});

fetch('api/menu.php')
  .then(response => response.json())
  .then(data => {
    console.log(data); // nanti bisa dipakai untuk render otomatis menu
  })
  .catch(error => {
    console.error("Gagal mengambil data:", error);
  });
