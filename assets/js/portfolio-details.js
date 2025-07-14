// Portfolio Details Handler
document.addEventListener("DOMContentLoaded", function () {
  // Get project ID from URL parameter
  const urlParams = new URLSearchParams(window.location.search);
  const projectId = urlParams.get("id");

  if (!projectId) {
    // If no project ID, redirect to portfolio page or show error
    window.location.href = "index.html#portfolio";
    return;
  }

  // Load project data
  const projectData = getProjectData(projectId);

  if (!projectData) {
    // If project not found, redirect to portfolio page
    window.location.href = "index.html#portfolio";
    return;
  }

  // Update page title
  document.title = `${projectData.title} - Portfolio Details - Bilal Rehman`;

  // Update header name
  const headerName = document.querySelector(".sitename");
  if (headerName) {
    headerName.textContent = "Bilal Rehman";
  }

  // Update social links
  updateSocialLinks();

  // Update page title section
  updatePageTitle(projectData);

  // Update portfolio details content
  updatePortfolioDetails(projectData);

  // Initialize AOS animations
  if (typeof AOS !== "undefined") {
    AOS.init();
  }
});

function updateSocialLinks() {
  const socialLinks = document.querySelector(".social-links");
  if (socialLinks) {
    socialLinks.innerHTML = `
      <a href="https://github.com/SardarBilal421" class="github"><i class="bi bi-github"></i></a>
      <a href="https://www.linkedin.com/in/bilal142/" class="linkedin"><i class="bi bi-linkedin"></i></a>
    `;
  }
}

function updatePageTitle(projectData) {
  const pageTitle = document.querySelector(".page-title h1");
  if (pageTitle) {
    pageTitle.textContent = projectData.title;
  }

  const breadcrumbs = document.querySelector(".breadcrumbs ol");
  if (breadcrumbs) {
    breadcrumbs.innerHTML = `
      <li><a href="index.html">Home</a></li>
      <li><a href="index.html#portfolio">Portfolio</a></li>
      <li class="current">${projectData.title}</li>
    `;
  }
}

function updatePortfolioDetails(projectData) {
  // Update portfolio slider images
  updatePortfolioSlider(projectData);

  // Update portfolio info
  updatePortfolioInfo(projectData);

  // Update portfolio description
  updatePortfolioDescription(projectData);
}

function updatePortfolioSlider(projectData) {
  const swiperWrapper = document.querySelector(
    ".portfolio-details-slider .swiper-wrapper"
  );
  if (swiperWrapper) {
    let sliderHTML = "";

    // Use available images or fallback to main image
    const images =
      projectData.images && projectData.images.length > 0
        ? projectData.images
        : [projectData.images[0] || "assets/img/portfolio/default.png"];

    images.forEach((image) => {
      sliderHTML += `
        <div class="swiper-slide">
          <img src="${image}" alt="${projectData.title}">
        </div>
      `;
    });

    swiperWrapper.innerHTML = sliderHTML;

    // Reinitialize swiper if it exists
    if (typeof Swiper !== "undefined") {
      const swiper = new Swiper(".portfolio-details-slider", {
        loop: true,
        speed: 600,
        autoplay: {
          delay: 5000,
        },
        slidesPerView: "auto",
        pagination: {
          el: ".swiper-pagination",
          type: "bullets",
          clickable: true,
        },
      });
    }
  }
}

function updatePortfolioInfo(projectData) {
  const portfolioInfo = document.querySelector(".portfolio-info");
  if (portfolioInfo) {
    portfolioInfo.innerHTML = `
      <h3>Project information</h3>
      <ul>
        <li><strong>Category</strong>: ${projectData.category}</li>
        <li><strong>Client</strong>: ${projectData.client}</li>
        <li><strong>Project date</strong>: ${projectData.projectDate}</li>
        <li><strong>Project URL</strong>: <a href="${projectData.projectUrl}" target="_blank">${projectData.projectUrl}</a></li>
        <li><strong>Role</strong>: ${projectData.role}</li>
      </ul>
    `;
  }
}

function updatePortfolioDescription(projectData) {
  const portfolioDescription = document.querySelector(".portfolio-description");
  if (portfolioDescription) {
    let descriptionHTML = `
      <h2>${projectData.title}</h2>
      <p>${projectData.description}</p>
    `;

    // Add features section
    if (projectData.features && projectData.features.length > 0) {
      descriptionHTML += `
        <h3>Key Features</h3>
        <ul>
          ${projectData.features
            .map((feature) => `<li>${feature}</li>`)
            .join("")}
        </ul>
      `;
    }

    // Add technologies section
    if (projectData.technologies && projectData.technologies.length > 0) {
      descriptionHTML += `
        <h3>Technologies Used</h3>
        <div class="tech-tags">
          ${projectData.technologies
            .map(
              (tech) =>
                `<span class="badge bg-primary me-2 mb-2">${tech}</span>`
            )
            .join("")}
        </div>
      `;
    }

    // Add responsibilities section
    if (
      projectData.responsibilities &&
      projectData.responsibilities.length > 0
    ) {
      descriptionHTML += `
        <h3>My Role & Responsibilities</h3>
        <ul>
          ${projectData.responsibilities
            .map((responsibility) => `<li>${responsibility}</li>`)
            .join("")}
        </ul>
      `;
    }

    // Add outcomes section
    if (projectData.outcomes && projectData.outcomes.length > 0) {
      descriptionHTML += `
        <h3>Outcomes & Results</h3>
        <ul>
          ${projectData.outcomes
            .map((outcome) => `<li>${outcome}</li>`)
            .join("")}
        </ul>
      `;
    }

    portfolioDescription.innerHTML = descriptionHTML;
  }
}

// Add some CSS for tech tags
const style = document.createElement("style");
style.textContent = `
  .tech-tags {
    margin: 20px 0;
  }
  
  .tech-tags .badge {
    font-size: 0.9em;
    padding: 8px 12px;
  }
  
  .portfolio-description h3 {
    margin-top: 30px;
    margin-bottom: 15px;
    color: #173b6c;
  }
  
  .portfolio-description ul {
    margin-bottom: 20px;
  }
  
  .portfolio-description li {
    margin-bottom: 8px;
  }
`;
document.head.appendChild(style);
