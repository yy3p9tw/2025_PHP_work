console.log('portfolio.js loaded');

const portfolioManager = {
  portfolioList: [],
  PAGE_SIZE: 6,
  currentPage: 1,

  renderPortfolio: function() {
    console.log('Rendering portfolio items:', this.portfolioList);
    const start = (this.currentPage - 1) * this.PAGE_SIZE;
    const end = start + this.PAGE_SIZE;
    const pageItems = this.portfolioList.slice(start, end);
    const container = document.getElementById('portfolio-list-container');
    if (!container) {
      console.error('Portfolio list container not found!');
      return;
    }
    container.innerHTML = pageItems.map(item => `
      <div class="col-12 col-sm-6 col-lg-4">
        <a href="${item.url}" target="_blank" class="portfolio-card-link">
          <div class="portfolio-card position-relative overflow-hidden shadow-sm rounded-4 h-100" style="background-image: url('${item.img || './assets/resume/portfolio/default.jpg'}');">
            <div class="portfolio-content-wrapper">
              <div class="portfolio-title mb-2">${item.title}</div>
              <div class="portfolio-desc">${item.desc}</div>
            </div>
          </div>
        </a>
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
    console.log('Fetching portfolio data...');
    fetch('./assets/portfolio_data.json')
      .then(response => {
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
      })
      .then(data => {
        console.log('Portfolio data loaded:', data);
        this.portfolioList = data;
        this.renderPortfolio();
      })
      .catch(error => console.error('Error loading portfolio data:', error));
  }
};

document.addEventListener('DOMContentLoaded', function() {
  portfolioManager.init();
});