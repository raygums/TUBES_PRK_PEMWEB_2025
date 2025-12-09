/**
 * voting-widget.js
 * Priority voting system for LampungSmart public complaints
 * Zero-Auth | sessionStorage Persistence | Anti-Abuse Safeguards
 * 
 * WCAG 2.2 AA Compliant | Haptic Feedback | Keyboard Navigation
 */

class VotingWidget {
    constructor() {
        this.config = {
            maxVotes: 3,
            resetIntervalMs: 86400000, // 24 hours
            storageKey: 'lampungsmart_votes',
            hapticDuration: 50
        };
        
        this.votes = this.loadVotes();
        this.elements = {
            buttons: document.querySelectorAll('.vote-btn'),
            counters: document.querySelectorAll('[id^="vote-count-"]'),
            remaining: document.querySelector('#votes-remaining'),
            announcer: document.querySelector('#vote-announcer')
        };
        
        this.init();
    }
    
    init() {
        // Check for automatic reset
        this.checkReset();
        
        // Initialize vote buttons
        this.initButtons();
        
        // Update UI based on stored votes
        this.renderInitialState();
        
        // Update rate limit counter
        this.updateRateLimitCounter();
    }
    
    loadVotes() {
        try {
            const stored = sessionStorage.getItem(this.config.storageKey);
            if (!stored) {
                return this.createFreshVoteData();
            }
            
            const data = JSON.parse(stored);
            
            // Validate structure
            if (!data.votes || typeof data.count !== 'number' || !data.timestamp) {
                return this.createFreshVoteData();
            }
            
            return data;
        } catch (e) {
            console.error('VotingWidget: Error loading votes', e);
            return this.createFreshVoteData();
        }
    }
    
    createFreshVoteData() {
        return {
            votes: {},
            count: 0,
            timestamp: Date.now()
        };
    }
    
    saveVotes() {
        try {
            sessionStorage.setItem(this.config.storageKey, JSON.stringify(this.votes));
        } catch (e) {
            console.error('VotingWidget: Error saving votes', e);
        }
    }
    
    checkReset() {
        const elapsed = Date.now() - this.votes.timestamp;
        
        if (elapsed > this.config.resetIntervalMs) {
            this.resetVotes();
        }
    }
    
    resetVotes() {
        this.votes = this.createFreshVoteData();
        this.saveVotes();
        this.renderInitialState();
        this.updateRateLimitCounter();
    }
    
    initButtons() {
        this.elements.buttons.forEach(btn => {
            // Click handler
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const complaintId = btn.dataset.complaintId;
                this.handleVote(complaintId, btn);
            });
            
            // Keyboard support (Enter/Space)
            btn.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    const complaintId = btn.dataset.complaintId;
                    this.handleVote(complaintId, btn);
                }
            });
        });
    }
    
    renderInitialState() {
        // Disable buttons for already voted complaints
        this.elements.buttons.forEach(btn => {
            const complaintId = btn.dataset.complaintId;
            
            if (this.votes.votes[complaintId]) {
                this.markAsVoted(btn);
            }
        });
    }
    
    handleVote(complaintId, btn) {
        // Check if already voted for this complaint
        if (this.votes.votes[complaintId]) {
            this.showAlreadyVotedMessage(btn);
            return;
        }
        
        // Check rate limit
        if (this.votes.count >= this.config.maxVotes) {
            this.showRateLimitModal();
            return;
        }
        
        // Haptic feedback (if supported)
        this.triggerHaptic();
        
        // Record vote
        this.votes.votes[complaintId] = true;
        this.votes.count++;
        
        // Persist to storage
        this.saveVotes();
        
        // Update UI
        this.updateVoteDisplay(complaintId);
        this.markAsVoted(btn);
        this.updateRateLimitCounter();
        
        // Announce to screen readers
        this.announceVote(complaintId);
    }
    
    markAsVoted(btn) {
        btn.disabled = true;
        btn.classList.remove('btn-primary');
        btn.classList.add('btn-success');
        btn.innerHTML = '<i class="bi bi-check-lg"></i> Voted';
        btn.setAttribute('aria-label', 'Anda sudah vote untuk pengaduan ini');
    }
    
    updateVoteDisplay(complaintId) {
        const badge = document.querySelector(`#vote-count-${complaintId}`);
        if (!badge) return;
        
        const currentCount = parseInt(badge.textContent.match(/\d+/)[0]);
        const newCount = currentCount + 1;
        
        // Check for reduced motion
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        
        if (!prefersReducedMotion) {
            // Animate increment
            badge.classList.add('scale-up');
            setTimeout(() => {
                badge.innerHTML = `<i class="bi bi-people-fill"></i> ${newCount}`;
                badge.classList.remove('scale-up');
            }, 150);
        } else {
            // Instant update
            badge.innerHTML = `<i class="bi bi-people-fill"></i> ${newCount}`;
        }
    }
    
    updateRateLimitCounter() {
        if (!this.elements.remaining) return;
        
        const remaining = this.config.maxVotes - this.votes.count;
        const parent = this.elements.remaining.parentElement;
        
        if (remaining === 0) {
            parent.classList.remove('bg-lampung-blue');
            parent.classList.add('bg-danger');
            this.elements.remaining.textContent = 'Batas vote tercapai';
        } else {
            parent.classList.remove('bg-danger');
            parent.classList.add('bg-lampung-blue');
            this.elements.remaining.textContent = `${remaining} vote${remaining !== 1 ? 's' : ''} remaining`;
        }
    }
    
    triggerHaptic() {
        // Vibration API support check
        if ('vibrate' in navigator) {
            try {
                navigator.vibrate(this.config.hapticDuration);
            } catch (e) {
                // Silent fail
            }
        }
    }
    
    announceVote(complaintId) {
        if (!this.elements.announcer) return;
        
        const remaining = this.config.maxVotes - this.votes.count;
        const message = `Vote berhasil dicatat. ${remaining} vote tersisa.`;
        
        this.elements.announcer.textContent = message;
        
        // Clear after 3 seconds
        setTimeout(() => {
            this.elements.announcer.textContent = '';
        }, 3000);
    }
    
    showAlreadyVotedMessage(btn) {
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-info-circle"></i> Sudah Vote';
        
        setTimeout(() => {
            btn.innerHTML = originalHTML;
        }, 2000);
    }
    
    showRateLimitModal() {
        // Check if Bootstrap modal exists
        const modalEl = document.getElementById('rateLimitModal');
        
        if (modalEl && typeof bootstrap !== 'undefined') {
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        } else {
            // Fallback: native alert
            alert('Batas voting tercapai! Anda telah menggunakan 3 vote. Data akan reset dalam 24 jam.');
        }
    }
}

// Initialize on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.votingWidget = new VotingWidget();
    });
} else {
    window.votingWidget = new VotingWidget();
}
