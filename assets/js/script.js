// Global Variables
let currentUser = null;
let isLoading = false;

// DOM Elements
const hamburger = document.querySelector('.hamburger');
const navMenu = document.querySelector('.nav-menu');
const contactForm = document.getElementById('contact-form');
const blogPostsContainer = document.getElementById('blog-posts');

// Initialize the application
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

function initializeApp() {
    setupEventListeners();
    loadBlogPosts();
    initializeAnimations();
    setupSmoothScrolling();
}

// Event Listeners
function setupEventListeners() {
    // Mobile menu toggle
    if (hamburger && navMenu) {
        hamburger.addEventListener('click', toggleMobileMenu);
    }

    // Contact form submission
    if (contactForm) {
        contactForm.addEventListener('submit', handleContactFormSubmission);
    }

    // Navigation links smooth scrolling
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', handleSmoothScroll);
    });

    // Window scroll events
    window.addEventListener('scroll', handleScroll);
    window.addEventListener('resize', handleResize);

    // Close mobile menu when clicking outside
    document.addEventListener('click', function(e) {
        if (!hamburger.contains(e.target) && !navMenu.contains(e.target)) {
            navMenu.classList.remove('active');
        }
    });
}

// Mobile Menu Toggle
function toggleMobileMenu() {
    hamburger.classList.toggle('active');
    navMenu.classList.toggle('active');
}

// Smooth Scrolling
function handleSmoothScroll(e) {
    e.preventDefault();
    const targetId = this.getAttribute('href');
    const targetSection = document.querySelector(targetId);
    
    if (targetSection) {
        const headerHeight = document.querySelector('.header').offsetHeight;
        const targetPosition = targetSection.offsetTop - headerHeight;
        
        window.scrollTo({
            top: targetPosition,
            behavior: 'smooth'
        });

        // Close mobile menu if open
        navMenu.classList.remove('active');
        hamburger.classList.remove('active');
    }
}

function setupSmoothScrolling() {
    // Update active navigation link based on scroll position
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.nav-link[href^="#"]');

    function updateActiveNavLink() {
        const scrollPosition = window.scrollY + 100;

        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;
            const sectionId = section.getAttribute('id');

            if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
                navLinks.forEach(link => {
                    link.classList.remove('active');
                    if (link.getAttribute('href') === `#${sectionId}`) {
                        link.classList.add('active');
                    }
                });
            }
        });
    }

    window.addEventListener('scroll', updateActiveNavLink);
}

// Contact Form Handling
async function handleContactFormSubmission(e) {
    e.preventDefault();
    
    if (isLoading) return;
    
    const formData = new FormData(contactForm);
    const submitButton = contactForm.querySelector('button[type="submit"]');
    const originalButtonText = submitButton.innerHTML;
    
    // Validate form
    if (!validateContactForm(formData)) {
        return;
    }
    
    try {
        setLoading(true);
        submitButton.innerHTML = '<div class="spinner"></div> Enviando...';
        
        const response = await fetch('api/contact.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showMessage('Mensagem enviada com sucesso! Entrarei em contato em breve.', 'success');
            contactForm.reset();
        } else {
            throw new Error(result.message || 'Erro ao enviar mensagem');
        }
        
    } catch (error) {
        console.error('Contact form error:', error);
        showMessage('Erro ao enviar mensagem. Tente novamente ou entre em contato por email.', 'error');
    } finally {
        setLoading(false);
        submitButton.innerHTML = originalButtonText;
    }
}

function validateContactForm(formData) {
    const name = formData.get('name');
    const email = formData.get('email');
    const subject = formData.get('subject');
    const message = formData.get('message');
    
    if (!name || name.trim().length < 2) {
        showMessage('Por favor, insira um nome válido.', 'error');
        return false;
    }
    
    if (!email || !isValidEmail(email)) {
        showMessage('Por favor, insira um email válido.', 'error');
        return false;
    }
    
    if (!subject) {
        showMessage('Por favor, selecione um assunto.', 'error');
        return false;
    }
    
    if (!message || message.trim().length < 10) {
        showMessage('Por favor, escreva uma mensagem com pelo menos 10 caracteres.', 'error');
        return false;
    }
    
    return true;
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Blog Posts Loading
async function loadBlogPosts() {
    if (!blogPostsContainer) return;
    
    try {
        const response = await fetch('api/blog.php?limit=3');
        const result = await response.json();
        
        if (result.success && result.posts.length > 0) {
            displayBlogPosts(result.posts);
        } else {
            displayNoBlogPosts();
        }
        
    } catch (error) {
        console.error('Blog loading error:', error);
        displayBlogError();
    }
}

function displayBlogPosts(posts) {
    const postsHTML = posts.map(post => `
        <article class="blog-card fade-in">
            <div class="blog-image">
                <img src="${post.image || 'assets/images/blog-placeholder.jpg'}" alt="${post.title}" loading="lazy">
            </div>
            <div class="blog-content">
                <div class="blog-meta">
                    <span><i class="fas fa-calendar"></i> ${formatDate(post.date)}</span>
                    <span><i class="fas fa-tag"></i> ${post.category}</span>
                </div>
                <h3 class="blog-title">${post.title}</h3>
                <p class="blog-excerpt">${post.excerpt}</p>
                <a href="blog-post.html?id=${post.id}" class="blog-read-more">
                    Ler mais <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </article>
    `).join('');
    
    blogPostsContainer.innerHTML = postsHTML;
    
    // Trigger animations
    setTimeout(() => {
        const blogCards = document.querySelectorAll('.blog-card');
        blogCards.forEach((card, index) => {
            setTimeout(() => {
                card.classList.add('visible');
            }, index * 200);
        });
    }, 100);
}

function displayNoBlogPosts() {
    blogPostsContainer.innerHTML = `
        <div class="blog-empty">
            <i class="fas fa-newspaper"></i>
            <h3>Em Breve</h3>
            <p>Novos artigos jurídicos serão publicados em breve. Acompanhe nossas redes sociais!</p>
        </div>
    `;
}

function displayBlogError() {
    blogPostsContainer.innerHTML = `
        <div class="blog-error">
            <i class="fas fa-exclamation-triangle"></i>
            <h3>Erro ao Carregar</h3>
            <p>Não foi possível carregar os artigos no momento. Tente novamente mais tarde.</p>
        </div>
    `;
}

// Utility Functions
function formatDate(dateString) {
    const options = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    };
    
    return new Date(dateString).toLocaleDateString('pt-BR', options);
}

function showMessage(message, type = 'info') {
    // Remove existing messages
    const existingMessages = document.querySelectorAll('.form-message');
    existingMessages.forEach(msg => msg.remove());
    
    // Create new message element
    const messageElement = document.createElement('div');
    messageElement.className = `form-message form-${type}`;
    messageElement.textContent = message;
    
    // Insert before contact form
    if (contactForm) {
        contactForm.parentNode.insertBefore(messageElement, contactForm);
        
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

function setLoading(loading) {
    isLoading = loading;
    
    if (loading) {
        document.body.classList.add('loading');
    } else {
        document.body.classList.remove('loading');
    }
}

// Animations
function initializeAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, observerOptions);
    
    // Observe elements for animation
    const animatedElements = document.querySelectorAll('.fade-in, .slide-in-left, .slide-in-right');
    animatedElements.forEach(el => {
        observer.observe(el);
    });
}

// Scroll Events
function handleScroll() {
    const header = document.querySelector('.header');
    const scrollPosition = window.scrollY;
    
    // Header background on scroll
    if (scrollPosition > 100) {
        header.classList.add('scrolled');
    } else {
        header.classList.remove('scrolled');
    }
    
    // Show/hide scroll to top button
    const scrollTopButton = document.querySelector('.scroll-top');
    if (scrollTopButton) {
        if (scrollPosition > 500) {
            scrollTopButton.classList.add('visible');
        } else {
            scrollTopButton.classList.remove('visible');
        }
    }
}

// Resize Events
function handleResize() {
    // Close mobile menu on resize
    if (window.innerWidth > 768) {
        navMenu.classList.remove('active');
        hamburger.classList.remove('active');
    }
}

// Scroll to Top Functionality
function createScrollToTopButton() {
    const button = document.createElement('button');
    button.className = 'scroll-top';
    button.innerHTML = '<i class="fas fa-arrow-up"></i>';
    button.title = 'Voltar ao topo';
    
    button.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
    
    document.body.appendChild(button);
}

// Initialize scroll to top button
document.addEventListener('DOMContentLoaded', createScrollToTopButton);

// Form Input Enhancements
document.addEventListener('DOMContentLoaded', function() {
    // Add floating label effect
    const formInputs = document.querySelectorAll('.form-group input, .form-group textarea, .form-group select');
    
    formInputs.forEach(input => {
        // Add focus/blur events for styling
        input.addEventListener('focus', function() {
            this.parentNode.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            if (!this.value) {
                this.parentNode.classList.remove('focused');
            }
        });
        
        // Check if input has value on load
        if (input.value) {
            input.parentNode.classList.add('focused');
        }
    });
    
    // Phone number formatting
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            if (value.length >= 11) {
                value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            } else if (value.length >= 7) {
                value = value.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
            } else if (value.length >= 3) {
                value = value.replace(/(\d{2})(\d{0,5})/, '($1) $2');
            }
            
            e.target.value = value;
        });
    }
});

// Performance Optimizations
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Apply debounce to scroll and resize events
window.addEventListener('scroll', debounce(handleScroll, 10));
window.addEventListener('resize', debounce(handleResize, 250));

// Lazy Loading for Images
function initializeLazyLoading() {
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }
}

// Error Handling
window.addEventListener('error', function(e) {
    console.error('JavaScript Error:', e.error);
    // Could send error to logging service here
});

// Service Worker Registration (for PWA capabilities)
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('/sw.js')
            .then(function(registration) {
                console.log('ServiceWorker registration successful');
            })
            .catch(function(err) {
                console.log('ServiceWorker registration failed');
            });
    });
}

// Export functions for testing
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        validateContactForm,
        isValidEmail,
        formatDate,
        debounce
    };
}
