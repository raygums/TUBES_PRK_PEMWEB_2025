/**
 * dashboard-live.js
 * Real-time dashboard simulation for LampungSmart public landing page
 * Zero-Auth Public Interface - Progressive Enhancement
 * 
 * WCAG 2.2 AA Compliant | Mobile-First | Reduced Motion Support
 */

class LiveDashboard {
    constructor() {
        this.metrics = {
            processing: 47,
            avgResponse: 2.5, // hours
            lastUpdate: Date.now()
        };
        
        this.config = {
            updateInterval: 5000, // 5 seconds
            minProcessing: 35,
            maxProcessing: 65,
            minResponse: 1.8,
            maxResponse: 3.8
        };
        
        this.elements = {
            counter: document.querySelector('#live-counter'),
            gauge: document.querySelector('#response-gauge'),
            responseTime: document.querySelector('#response-time')
        };
        
        // Check for reduced motion preference
        this.prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        
        this.init();
    }
    
    init() {
        // Validate elements exist
        if (!this.elements.counter || !this.elements.gauge) {
            console.warn('LiveDashboard: Required elements not found');
            return;
        }
        
        // Start simulation
        this.startSimulation();
        
        // Pause simulation when page is hidden (performance optimization)
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.pauseSimulation();
            } else {
                this.resumeSimulation();
            }
        });
    }
    
    startSimulation() {
        this.simulationTimer = setInterval(() => {
            this.updateMetrics();
            this.renderUI();
        }, this.config.updateInterval);
    }
    
    pauseSimulation() {
        if (this.simulationTimer) {
            clearInterval(this.simulationTimer);
        }
    }
    
    resumeSimulation() {
        this.startSimulation();
    }
    
    updateMetrics() {
        // Realistic complaint processing simulation
        // Simulates civic patterns: weekday peaks, weekend dips
        const hour = new Date().getHours();
        const isWorkingHours = hour >= 8 && hour <= 17;
        
        // Random walk with bounds
        const delta = isWorkingHours 
            ? Math.floor(Math.random() * 5) - 1  // -1 to 3 during work hours
            : Math.floor(Math.random() * 3) - 2; // -2 to 0 outside work hours
        
        this.metrics.processing = Math.max(
            this.config.minProcessing,
            Math.min(this.config.maxProcessing, this.metrics.processing + delta)
        );
        
        // Response time fluctuates (lower is better)
        this.metrics.avgResponse = Number(
            (this.config.minResponse + Math.random() * (this.config.maxResponse - this.config.minResponse))
            .toFixed(1)
        );
        
        this.metrics.lastUpdate = Date.now();
    }
    
    renderUI() {
        this.updateCounter();
        this.updateGauge();
    }
    
    updateCounter() {
        const target = this.metrics.processing;
        const current = parseInt(this.elements.counter.textContent);
        
        if (current === target) return;
        
        // Add pulse animation (respects reduced motion)
        if (!this.prefersReducedMotion) {
            this.elements.counter.classList.add('pulse-lampung');
            setTimeout(() => {
                this.elements.counter.classList.remove('pulse-lampung');
            }, 600);
        }
        
        // Animate counter with easing
        this.animateCounter(this.elements.counter, current, target, 500);
    }
    
    animateCounter(element, start, end, duration) {
        const startTime = performance.now();
        const difference = end - start;
        
        const step = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Ease-out cubic
            const easeProgress = 1 - Math.pow(1 - progress, 3);
            const currentValue = Math.round(start + (difference * easeProgress));
            
            element.textContent = currentValue;
            
            if (progress < 1) {
                requestAnimationFrame(step);
            }
        };
        
        requestAnimationFrame(step);
    }
    
    updateGauge() {
        const responseTime = this.metrics.avgResponse;
        
        // Calculate percentage (inverted: lower time = higher percentage)
        // 1.8h = 100%, 3.8h = 0%
        const percentage = ((this.config.maxResponse - responseTime) / 
                          (this.config.maxResponse - this.config.minResponse)) * 100;
        
        // SVG circle circumference = 2πr = 2 * 3.14159 * 75 = 471
        const circumference = 471;
        const offset = circumference - (circumference * percentage / 100);
        
        // Update gauge with smooth transition
        this.elements.gauge.style.strokeDashoffset = offset;
        
        // Update text display
        this.elements.responseTime.textContent = responseTime;
        
        // Update color based on performance
        let color = '#009639'; // Green (good)
        if (responseTime > 3.0) {
            color = '#D60000'; // Red (poor)
        } else if (responseTime > 2.5) {
            color = '#FFD700'; // Gold (fair)
        }
        
        this.elements.gauge.style.stroke = color;
        this.elements.responseTime.style.color = color;
    }
    
    destroy() {
        this.pauseSimulation();
    }
}

// Initialize on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.liveDashboard = new LiveDashboard();
    });
} else {
    window.liveDashboard = new LiveDashboard();
}

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (window.liveDashboard) {
        window.liveDashboard.destroy();
    }
});
