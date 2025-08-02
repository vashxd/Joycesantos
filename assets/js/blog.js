// Blog page specific JavaScript
document.addEventListener('DOMContentLoaded', function() {
    initializeBlogPage();
});

// Global variables for blog
let currentPage = 1;
let currentCategory = 'all';
let currentSearch = '';
let totalPages = 1;
let isLoading = false;

function initializeBlogPage() {
    setupBlogEventListeners();
    loadBlogPosts();
    loadFeaturedPost();
    loadCategoriesCount();
}

function setupBlogEventListeners() {
    // Category filter tabs
    const filterTabs = document.querySelectorAll('.filter-tab');
    filterTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Update active tab
            filterTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            // Update current category and reload
            currentCategory = this.dataset.category;
            currentPage = 1;
            loadBlogPosts();
        });
    });
    
    // Search functionality
    const searchInput = document.getElementById('search-posts');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                currentSearch = this.value.trim();
                currentPage = 1;
                loadBlogPosts();
            }, 500);
        });
    }
    
    // Pagination
    const prevBtn = document.getElementById('prev-page');
    const nextBtn = document.getElementById('next-page');
    
    if (prevBtn) {
        prevBtn.addEventListener('click', function() {
            if (currentPage > 1) {
                currentPage--;
                loadBlogPosts();
            }
        });
    }
    
    if (nextBtn) {
        nextBtn.addEventListener('click', function() {
            if (currentPage < totalPages) {
                currentPage++;
                loadBlogPosts();
            }
        });
    }
    
    // Newsletter form
    const newsletterForm = document.getElementById('newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', handleNewsletterSubmission);
    }
    
    // Category links in sidebar
    const categoryLinks = document.querySelectorAll('.categories-list a');
    categoryLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const category = this.dataset.category;
            
            // Update filter tab
            const filterTab = document.querySelector(`[data-category="${category}"]`);
            if (filterTab) {
                document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
                filterTab.classList.add('active');
            }
            
            currentCategory = category;
            currentPage = 1;
            loadBlogPosts();
        });
    });
}

async function loadBlogPosts() {
    if (isLoading) return;
    
    const container = document.getElementById('blog-posts-container');
    if (!container) return;
    
    isLoading = true;
    
    // Show loading state
    container.innerHTML = `
        <div class="blog-loading">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Carregando artigos...</p>
        </div>
    `;
    
    try {
        const params = new URLSearchParams({
            limit: '9',
            offset: (currentPage - 1) * 9
        });
        
        if (currentCategory && currentCategory !== 'all') {
            params.append('category', currentCategory);
        }
        
        if (currentSearch) {
            params.append('search', currentSearch);
        }
        
        const response = await fetch(`api/blog.php?${params}`);
        const result = await response.json();
        
        if (result.success) {
            displayBlogPosts(result.posts);
            updatePagination(result);
        } else {
            displayBlogError();
        }
        
    } catch (error) {
        console.error('Blog loading error:', error);
        displayBlogError();
    } finally {
        isLoading = false;
    }
}

function displayBlogPosts(posts) {
    const container = document.getElementById('blog-posts-container');
    
    if (posts.length === 0) {
        container.innerHTML = `
            <div class="blog-empty">
                <i class="fas fa-search"></i>
                <h3>Nenhum artigo encontrado</h3>
                <p>Tente ajustar os filtros ou buscar por outros termos.</p>
            </div>
        `;
        return;
    }
    
    const postsHTML = posts.map(post => `
        <article class="blog-card fade-in">
            <div class="blog-image">
                <img src="${post.image}" alt="${post.title}" loading="lazy">
                <div class="blog-category">${post.category}</div>
            </div>
            <div class="blog-content">
                <div class="blog-meta">
                    <span><i class="fas fa-calendar"></i> ${formatDate(post.date)}</span>
                    <span><i class="fas fa-eye"></i> ${post.views} visualizações</span>
                </div>
                <h3 class="blog-title">
                    <a href="blog-post.html?id=${post.id}">${post.title}</a>
                </h3>
                <p class="blog-excerpt">${post.excerpt}</p>
                <div class="blog-footer">
                    <div class="blog-tags">
                        ${post.tags.map(tag => `<span class="blog-tag">${tag}</span>`).join('')}
                    </div>
                    <a href="blog-post.html?id=${post.id}" class="blog-read-more">
                        Ler mais <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </article>
    `).join('');
    
    container.innerHTML = postsHTML;
    
    // Trigger animations
    setTimeout(() => {
        const blogCards = document.querySelectorAll('.blog-card');
        blogCards.forEach((card, index) => {
            setTimeout(() => {
                card.classList.add('visible');
            }, index * 100);
        });
    }, 100);
}

function displayBlogError() {
    const container = document.getElementById('blog-posts-container');
    container.innerHTML = `
        <div class="blog-error">
            <i class="fas fa-exclamation-triangle"></i>
            <h3>Erro ao Carregar</h3>
            <p>Não foi possível carregar os artigos no momento. Tente novamente mais tarde.</p>
            <button onclick="loadBlogPosts()" class="btn btn-primary">
                <i class="fas fa-redo"></i>
                Tentar Novamente
            </button>
        </div>
    `;
}

function updatePagination(result) {
    const pagination = document.getElementById('blog-pagination');
    const prevBtn = document.getElementById('prev-page');
    const nextBtn = document.getElementById('next-page');
    const pageInfo = document.getElementById('page-info');
    
    if (!pagination) return;
    
    totalPages = Math.ceil(result.total / 9);
    
    if (totalPages <= 1) {
        pagination.style.display = 'none';
        return;
    }
    
    pagination.style.display = 'flex';
    
    // Update buttons state
    prevBtn.disabled = currentPage <= 1;
    nextBtn.disabled = currentPage >= totalPages;
    
    // Update page info
    pageInfo.textContent = `Página ${currentPage} de ${totalPages}`;
}

async function loadFeaturedPost() {
    const container = document.getElementById('featured-post');
    if (!container) return;
    
    try {
        const response = await fetch('api/blog.php?featured=1&limit=1');
        const result = await response.json();
        
        if (result.success && result.posts.length > 0) {
            const post = result.posts[0];
            container.innerHTML = `
                <div class="featured-image">
                    <img src="${post.image}" alt="${post.title}">
                </div>
                <div class="featured-content">
                    <div class="featured-category">${post.category}</div>
                    <h4><a href="blog-post.html?id=${post.id}">${post.title}</a></h4>
                    <p>${post.excerpt}</p>
                    <div class="featured-meta">
                        <span><i class="fas fa-calendar"></i> ${formatDate(post.date)}</span>
                    </div>
                </div>
            `;
        } else {
            container.innerHTML = `
                <div class="featured-empty">
                    <i class="fas fa-star"></i>
                    <p>Nenhum post em destaque</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Featured post error:', error);
        container.innerHTML = `
            <div class="featured-error">
                <i class="fas fa-exclamation-circle"></i>
                <p>Erro ao carregar</p>
            </div>
        `;
    }
}

async function loadCategoriesCount() {
    try {
        const response = await fetch('api/blog.php?stats=1');
        const result = await response.json();
        
        if (result.success && result.categories) {
            const categoryLinks = document.querySelectorAll('.categories-list a');
            categoryLinks.forEach(link => {
                const category = link.dataset.category;
                const span = link.querySelector('span');
                if (span && result.categories[category]) {
                    span.textContent = `(${result.categories[category]})`;
                }
            });
        }
    } catch (error) {
        console.error('Categories count error:', error);
    }
}

async function handleNewsletterSubmission(e) {
    e.preventDefault();
    
    const form = e.target;
    const email = form.querySelector('input[type="email"]').value;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;
    
    if (!email || !isValidEmail(email)) {
        showMessage('Por favor, insira um email válido.', 'error');
        return;
    }
    
    try {
        submitBtn.innerHTML = '<div class="spinner"></div> Inscrevendo...';
        submitBtn.disabled = true;
        
        const formData = new FormData();
        formData.append('email', email);
        formData.append('action', 'newsletter');
        
        const response = await fetch('api/newsletter.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showMessage('Inscrição realizada com sucesso! Obrigada por se inscrever.', 'success');
            form.reset();
        } else {
            throw new Error(result.message || 'Erro ao realizar inscrição');
        }
        
    } catch (error) {
        console.error('Newsletter error:', error);
        showMessage('Erro ao realizar inscrição. Tente novamente.', 'error');
    } finally {
        submitBtn.innerHTML = originalBtnText;
        submitBtn.disabled = false;
    }
}

function showMessage(message, type = 'info') {
    // Remove existing messages
    const existingMessages = document.querySelectorAll('.blog-message');
    existingMessages.forEach(msg => msg.remove());
    
    // Create new message element
    const messageElement = document.createElement('div');
    messageElement.className = `blog-message blog-message-${type}`;
    messageElement.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        ${message}
    `;
    
    // Insert at top of main content
    const mainContent = document.querySelector('.blog-content');
    if (mainContent) {
        mainContent.insertBefore(messageElement, mainContent.firstChild);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            messageElement.remove();
        }, 5000);
        
        // Scroll to message
        messageElement.scrollIntoView({ 
            behavior: 'smooth', 
            block: 'center' 
        });
    }
}

// Utility function (if not already defined in main script.js)
if (typeof isValidEmail === 'undefined') {
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
}

if (typeof formatDate === 'undefined') {
    function formatDate(dateString) {
        const options = { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        };
        
        return new Date(dateString).toLocaleDateString('pt-BR', options);
    }
}
