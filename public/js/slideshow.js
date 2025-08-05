/**
 * Media Slideshow JavaScript
 * Optimized and secure slideshow for Laravel application
 */

class MediaSlideshow {
    constructor(options = {}) {
        // Configuration
        this.config = {
            refreshInterval: options.refreshInterval || 60000, // 60 seconds
            slideDisplayTime: options.slideDisplayTime || 5000, // 5 seconds
            transitionDuration: options.transitionDuration || 1500, // 1.5 seconds
            maxRetries: options.maxRetries || 3,
            retryDelay: options.retryDelay || 5000, // 5 seconds
            ...options
        };

        // State
        this.slides = [];
        this.currentSlide = 0;
        this.isTransitioning = false;
        this.isInitialized = false;
        this.retryCount = 0;
        this.currentSlideTimeout = null;
        this.refreshIntervalId = null;

        // DOM elements
        this.container = document.getElementById('slideshow-container');
        this.loading = document.getElementById('loading');
        this.statusIndicator = document.getElementById('status-indicator');
        this.errorMessage = document.getElementById('error-message');

        // Bind methods
        this.handleVisibilityChange = this.handleVisibilityChange.bind(this);
        this.handleError = this.handleError.bind(this);

        this.init();
    }

    async init() {
        try {
            this.setupEventListeners();
            await this.fetchSlides();
            this.hideLoading();
            
            if (this.slides.length > 0) {
                this.loadSlide(this.currentSlide);
                this.startAutoRefresh();
                this.isInitialized = true;
                this.retryCount = 0;
            } else {
                this.showError('Tidak ada media yang tersedia untuk ditampilkan');
            }
        } catch (error) {
            console.error('Error initializing slideshow:', error);
            this.handleInitializationError(error);
        }
    }

    setupEventListeners() {
        // Handle page visibility change
        document.addEventListener('visibilitychange', this.handleVisibilityChange);
        
        // Handle global errors
        window.addEventListener('error', this.handleError);
        
        // Handle unhandled promise rejections
        window.addEventListener('unhandledrejection', (event) => {
            console.error('Unhandled promise rejection:', event.reason);
        });

        // Handle network status changes
        window.addEventListener('online', () => {
            console.log('Network connection restored');
            this.updateStatus(true);
            if (!this.isInitialized) {
                this.init();
            }
        });

        window.addEventListener('offline', () => {
            console.log('Network connection lost');
            this.updateStatus(false);
        });
    }

    async fetchSlides() {
        try {
            const response = await fetch('/slideshow/media', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.getCSRFToken(),
                    'Cache-Control': 'no-cache'
                },
                cache: 'no-cache'
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            
            // Validate and filter slides
            this.slides = this.validateSlides(data);
            this.updateStatus(true);
            
            console.log(`Successfully loaded ${this.slides.length} slides`);
            return this.slides;
            
        } catch (error) {
            console.error('Error fetching slides:', error);
            this.updateStatus(false);
            throw error;
        }
    }

    validateSlides(data) {
        if (!Array.isArray(data)) {
            console.warn('Invalid slide data format, expected array');
            return [];
        }

        return data.filter(slide => {
            // Basic validation
            if (!slide || !slide.url || !slide.type || !slide.extension) {
                console.warn('Invalid slide data:', slide);
                return false;
            }

            // Type validation
            if (!['image', 'video'].includes(slide.type)) {
                console.warn('Unsupported slide type:', slide.type);
                return false;
            }

            // Extension validation - sesuaikan dengan yang didukung di backend
            const validExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'webm'];
            if (!validExtensions.includes(slide.extension.toLowerCase())) {
                console.warn('Unsupported file extension:', slide.extension);
                return false;
            }

            return true;
        });
    }

    createSlideElement(slideData) {
        const slide = document.createElement('div');
        slide.className = 'slide';
        slide.dataset.slideId = slideData.id;
        slide.dataset.slideType = slideData.type;

        const mediaElement = this.createMediaElement(slideData);
        if (!mediaElement) {
            console.error('Failed to create media element for slide:', slideData);
            return null;
        }

        slide.appendChild(mediaElement);

        // Add slide information dengan filename
        const slideInfo = this.createSlideInfo(slideData);
        slide.appendChild(slideInfo);

        return slide;
    }

    createMediaElement(slideData) {
        const extension = slideData.extension.toLowerCase();

        try {
            if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(extension)) {
                return this.createImageElement(slideData);
            } else if (['mp4', 'webm'].includes(extension)) {
                return this.createVideoElement(slideData);
            } else {
                throw new Error(`Unsupported media type: ${extension}`);
            }
        } catch (error) {
            console.error('Error creating media element:', error);
            return this.createErrorElement(error.message);
        }
    }

    createImageElement(slideData) {
        const img = document.createElement('img');
        img.src = slideData.url;
        img.alt = slideData.filename || 'Media slideshow';
        img.loading = 'eager';
        
        img.onload = () => {
            console.log(`Image loaded successfully: ${slideData.filename}`);
        };
        
        img.onerror = () => {
            console.error(`Failed to load image: ${slideData.filename}`);
            setTimeout(() => this.nextSlide(), 1000);
        };
        
        return img;
    }

    createVideoElement(slideData) {
        const video = document.createElement('video');
        video.src = slideData.url;
        video.autoplay = true;
        video.muted = true;
        video.playsInline = true;
        video.controls = false;
        video.preload = 'metadata';
        
        video.onended = () => {
            console.log(`Video playback ended: ${slideData.filename}`);
            this.nextSlide();
        };
        
        video.onerror = () => {
            console.error(`Failed to load video: ${slideData.filename}`);
            setTimeout(() => this.nextSlide(), 1000);
        };
        
        video.onloadeddata = () => {
            console.log(`Video loaded successfully: ${slideData.filename}`);
        };
        
        return video;
    }

    createSlideInfo(slideData) {
        const slideInfo = document.createElement('div');
        return slideInfo;
    }

    createErrorElement(message) {
        const div = document.createElement('div');
        div.className = 'slide-error';
        div.innerHTML = `<p style="color: white; text-align: center;">${message}</p>`;
        return div;
    }

    preloadNextSlide(index) {
        if (!this.slides.length) return;

        const nextIndex = (index + 1) % this.slides.length;
        const nextSlide = this.slides[nextIndex];
        
        if (nextSlide && ['jpg', 'jpeg', 'png', 'gif'].includes(nextSlide.extension.toLowerCase())) {
            const img = new Image();
            img.src = nextSlide.url;
        }
    }

    loadSlide(index) {
        if (!this.slides.length || this.isTransitioning) return;

        this.isTransitioning = true;
        this.clearCurrentSlideTimeout();

        const slideData = this.slides[index];
        const newSlide = this.createSlideElement(slideData);

        if (!newSlide) {
            console.error('Failed to create slide, skipping to next');
            this.isTransitioning = false;
            this.nextSlide();
            return;
        }

        // Remove old slide
        this.removeOldSlide();

        // Show new slide
        this.container.appendChild(newSlide);
        requestAnimationFrame(() => {
            newSlide.classList.add('show');
            this.isTransitioning = false;
        });

        // Preload next slide
        this.preloadNextSlide(index);

        // Set timeout for next slide (except for videos)
        if (!['mp4', 'webm'].includes(slideData.extension.toLowerCase())) {
            this.currentSlideTimeout = setTimeout(() => {
                this.nextSlide();
            }, this.config.slideDisplayTime);
        }

        console.log(`Showing slide ${index + 1}/${this.slides.length}: ${slideData.filename}`);
    }

    removeOldSlide() {
        const oldSlide = this.container.querySelector('.slide.show');
        if (oldSlide) {
            oldSlide.classList.remove('show');
            setTimeout(() => {
                if (oldSlide.parentNode) {
                    oldSlide.remove();
                }
            }, this.config.transitionDuration + 100);
        }
    }

    nextSlide() {
        if (this.slides.length === 0) return;
        
        this.currentSlide = (this.currentSlide + 1) % this.slides.length;
        this.loadSlide(this.currentSlide);
    }

    async refreshSlides() {
        try {
            const oldSlidesCount = this.slides.length;
            await this.fetchSlides();
            
            if (this.slides.length !== oldSlidesCount) {
                console.log('Slide list updated, restarting slideshow');
                this.currentSlide = 0;
                this.loadSlide(this.currentSlide);
            }
        } catch (error) {
            console.error('Error refreshing slides:', error);
        }
    }

    startAutoRefresh() {
        if (this.refreshIntervalId) {
            clearInterval(this.refreshIntervalId);
        }
        
        this.refreshIntervalId = setInterval(() => {
            this.refreshSlides();
        }, this.config.refreshInterval);
    }

    clearCurrentSlideTimeout() {
        if (this.currentSlideTimeout) {
            clearTimeout(this.currentSlideTimeout);
            this.currentSlideTimeout = null;
        }
    }

    hideLoading() {
        if (this.loading) {
            this.loading.style.display = 'none';
        }
    }

    showError(message) {
        this.hideLoading();
        
        if (this.errorMessage) {
            this.errorMessage.style.display = 'block';
            // Update message if it's a custom one
            if (message !== 'Tidak ada media yang tersedia untuk ditampilkan') {
                const errorContent = this.errorMessage.querySelector('.error-content');
                if (errorContent) {
                    errorContent.innerHTML = `
                        <h2>Error</h2>
                        <p>${message}</p>
                        <p style="font-size: 0.9rem; margin-top: 1rem;">Halaman akan dimuat ulang dalam 10 detik...</p>
                    `;
                }
                
                setTimeout(() => {
                    window.location.reload();
                }, 10000);
            }
        }
    }

    updateStatus(isConnected) {
        if (this.statusIndicator) {
            if (isConnected) {
                this.statusIndicator.classList.remove('error');
            } else {
                this.statusIndicator.classList.add('error');
            }
        }
    }

    handleVisibilityChange() {
        if (document.hidden) {
            console.log('Tab hidden, pausing slideshow');
            this.clearCurrentSlideTimeout();
            if (this.refreshIntervalId) {
                clearInterval(this.refreshIntervalId);
            }
        } else {
            console.log('Tab visible, resuming slideshow');
            if (this.isInitialized) {
                this.startAutoRefresh();
                // Resume current slide timer if not a video
                const currentSlideData = this.slides[this.currentSlide];
                if (currentSlideData && !['mp4', 'webm'].includes(currentSlideData.extension.toLowerCase())) {
                    this.currentSlideTimeout = setTimeout(() => {
                        this.nextSlide();
                    }, this.config.slideDisplayTime);
                }
            }
        }
    }

    handleError(event) {
        console.error('Global error caught:', event.error);
    }

    handleInitializationError(error) {
        this.retryCount++;
        
        if (this.retryCount <= this.config.maxRetries) {
            console.log(`Initialization failed, retrying (${this.retryCount}/${this.config.maxRetries})...`);
            setTimeout(() => {
                this.init();
            }, this.config.retryDelay);
        } else {
            this.showError('Gagal memuat slideshow setelah beberapa percobaan');
        }
    }

    getCSRFToken() {
        const token = document.querySelector('meta[name="csrf-token"]');
        return token ? token.getAttribute('content') : '';
    }

    // Public methods for external control
    pause() {
        this.clearCurrentSlideTimeout();
    }

    resume() {
        if (this.isInitialized && this.slides.length > 0) {
            const currentSlideData = this.slides[this.currentSlide];
            if (currentSlideData && !['mp4', 'webm'].includes(currentSlideData.extension.toLowerCase())) {
                this.currentSlideTimeout = setTimeout(() => {
                    this.nextSlide();
                }, this.config.slideDisplayTime);
            }
        }
    }

    destroy() {
        this.clearCurrentSlideTimeout();
        if (this.refreshIntervalId) {
            clearInterval(this.refreshIntervalId);
        }
        
        document.removeEventListener('visibilitychange', this.handleVisibilityChange);
        window.removeEventListener('error', this.handleError);
        
        this.isInitialized = false;
    }
}

// Initialize slideshow when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.slideshow = new MediaSlideshow();
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = MediaSlideshow;
}
