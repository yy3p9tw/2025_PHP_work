// 平滑滾動與導覽高亮
const navLinks = document.querySelectorAll('.main-nav a');
const sections = Array.from(navLinks).map(link => document.querySelector(link.getAttribute('href')));

function onScroll() {
  const scrollPos = window.scrollY + 120;
  sections.forEach((section, idx) => {
    if (section && section.offsetTop <= scrollPos && section.offsetTop + section.offsetHeight > scrollPos) {
      navLinks.forEach(l => l.classList.remove('active'));
      navLinks[idx].classList.add('active');
    }
  });
}
window.addEventListener('scroll', onScroll);

// 平滑滾動
navLinks.forEach(link => {
  link.addEventListener('click', function(e) {
    const target = document.querySelector(this.getAttribute('href'));
    if (target) {
      e.preventDefault();
      window.scrollTo({
        top: target.offsetTop - 60,
        behavior: 'smooth'
      });
    }
  });
});
