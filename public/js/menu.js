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

      // Example filter logic: show all sections (replace with your own logic)
      showAllSections();
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