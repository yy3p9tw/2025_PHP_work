const portfolioManager = {
  portfolioList: [],
  PAGE_SIZE: 6,
  currentPage: 1,

  renderPortfolio: function() {
    const start = (this.currentPage - 1) * this.PAGE_SIZE;
    const end = start + this.PAGE_SIZE;
    const pageItems = this.portfolioList.slice(start, end);
    const container = document.getElementById('portfolio-list-container');
    container.innerHTML = pageItems.map(item => `
      <div class="col-12 col-sm-6 col-lg-4">
        <div class="portfolio-card position-relative overflow-hidden shadow-sm rounded-4 h-100">
          <img src="${item.img || './resume/portfolio/default.jpg'}" class="portfolio-img w-100" alt="${item.title}">
          <div class="portfolio-overlay d-flex flex-column justify-content-center align-items-center text-center">
            <div class="portfolio-title mb-2">${item.title}</div>
            <div class="portfolio-desc mb-3">${item.desc}</div>
            <a href="${item.url}" class="btn btn-primary" target="_blank">${item.btn}</a>
          </div>
        </div>
      </div>
    `).join('');
    this.renderPagination();
  },

  renderPagination: function() {
    const totalPages = Math.ceil(this.portfolioList.length / this.PAGE_SIZE);
    const pagination = document.getElementById('portfolio-pagination');
    if (!pagination) return;
    if (totalPages <= 1) { pagination.innerHTML = ''; return; }
    let btns = '';
    for (let i = 1; i <= totalPages; i++) {
      if (i === 1 || i === totalPages || (i >= this.currentPage - 1 && i <= this.currentPage + 1)) {
        btns += `<button class="${i === this.currentPage ? 'active' : ''}" onclick="portfolioManager.gotoPortfolioPage(${i})">${i}</button>`;
      } else if (i === this.currentPage - 2 || i === this.currentPage + 2) {
        btns += `<span class="pagination-ellipsis">...</span>`;
      }
    }
    pagination.innerHTML = btns;
  },

  gotoPortfolioPage: function(page) {
    this.currentPage = page;
    this.renderPortfolio();
  },

  init: function() {
    fetch('./portfolio_data.json')
      .then(response => response.json())
      .then(data => {
        this.portfolioList = data;
        this.renderPortfolio();
      })
      .catch(error => console.error('Error loading portfolio data:', error));
  }
};

document.addEventListener('DOMContentLoaded', function() {
  portfolioManager.init();
});