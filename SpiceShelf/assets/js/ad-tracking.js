/**
 * Ad Tracking JavaScript - FIXED VERSION
 */

// Check if already initialized to prevent duplicate execution
if (typeof window.adTrackingInitialized === 'undefined') {
    window.adTrackingInitialized = true;
    
    // Track which ads we've already counted to prevent duplicate impressions
    const countedAdIds = new Set();

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize everything
        initializeAdTracking();
    });

    function initializeAdTracking() {
        // 1. First, set up close button handlers (PRIORITY)
        setupCloseButtons();
        
        // 2. Then track impressions
        trackAdImpressions();
        
        // 3. Finally, track clicks (but not on close buttons)
        setupAdClickTracking();
    }

    /**
     * Set up close button functionality - RUNS FIRST
     * ONLY handles AD-related close buttons, NOT notifications
     */
    function setupCloseButtons() {
        // Handle ONLY ad close buttons using event delegation
        document.body.addEventListener('click', function(e) {
            // ONLY check for AD-specific close buttons, NOT general .close
            const isAdCloseButton = 
                e.target.matches('.ad-close, .close-btn, .close-popup, [data-action="close"]') ||
                e.target.closest('.ad-close, .close-btn, .close-popup, [data-action="close"]') ||
                // Only check for × or X if it's within an ad container
                (e.target.closest('[data-ad-id], .ad-popup') && 
                 e.target.textContent && 
                 (e.target.textContent.trim() === '×' || e.target.textContent.trim() === 'X'));
            
            if (isAdCloseButton) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                
                // Find the ad popup (NOT general notifications)
                const popup = e.target.closest('.ad-popup, [data-ad-id]');
                if (popup) {
                    console.log('Closing popup ad');
                    
                    // Hide the popup
                    popup.style.display = 'none';
                    
                    // Hide any overlay
                    const overlay = document.querySelector('.ad-popup-overlay, .ad-overlay');
                    if (overlay) {
                        overlay.style.display = 'none';
                    }
                    
                    // Remember it was closed
                    const adId = popup.getAttribute('data-ad-id');
                    if (adId) {
                        sessionStorage.setItem(`ad_popup_closed_${adId}`, 'true');
                    }
                }
                
                return false;
            }
        }, true); // Use capture phase to intercept before bubbling
    }

    /**
     * Track ad impressions
     */
    function trackAdImpressions() {
        const adContainers = document.querySelectorAll('[data-ad-id]');
        
        if (adContainers.length === 0) return;
        
        // Check session storage for closed popups
        adContainers.forEach(ad => {
            if (ad.classList.contains('ad-popup')) {
                const adId = ad.getAttribute('data-ad-id');
                if (adId && sessionStorage.getItem(`ad_popup_closed_${adId}`) === 'true') {
                    ad.style.display = 'none';
                    const overlay = document.querySelector('.ad-popup-overlay, .ad-overlay');
                    if (overlay) overlay.style.display = 'none';
                }
            }
        });
        
        // Use Intersection Observer for impressions
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const adId = entry.target.getAttribute('data-ad-id');
                    
                    if (adId && !countedAdIds.has(adId)) {
                        countedAdIds.add(adId);
                        
                        const position = entry.target.classList.contains('ad-popup') ? 'popup' : 'sidebar';
                        console.log(`Recording impression for ad ID: ${adId}, Type: ${position}`);
                        
                        // Send impression data to server
                        fetch(`index.php?action=logAdImpression&id=${adId}`, {
                            method: 'GET',
                            credentials: 'same-origin'
                        }).catch(error => {
                            console.error('Error logging ad impression:', error);
                        });
                        
                        observer.unobserve(entry.target);
                    }
                }
            });
        }, {
            threshold: 0.5
        });
        
        adContainers.forEach(ad => {
            observer.observe(ad);
        });
    }

    /**
     * Set up click tracking for ads (excluding close buttons)
     */
    function setupAdClickTracking() {
        // Use event delegation for all ad clicks
        document.body.addEventListener('click', function(e) {
            // Skip if it's ANY close button (let other scripts handle them)
            if (e.target.matches('.ad-close, .close-btn, .close-popup, .close, [data-action="close"], .notification-dismiss') ||
                e.target.closest('.ad-close, .close-btn, .close-popup, .close, [data-action="close"], .notification-dismiss')) {
                return;
            }
            
            // Check if it's a link within an ad
            const link = e.target.closest('a');
            if (!link) return;
            
            // Check if the link is within an ad container
            const adContainer = link.closest('[data-ad-id]');
            if (!adContainer) return;
            
            const adId = adContainer.getAttribute('data-ad-id');
            if (!adId) return;
            
            // This is an ad click - track it
            e.preventDefault();
            
            const destination = link.href;
            console.log("Ad clicked! ID:", adId, "Destination:", destination);
            
            // Log the click
            fetch(`index.php?action=adClick&id=${adId}`, {
                method: 'GET',
                credentials: 'same-origin'
            }).then(response => {
                // Redirect after logging
                window.location.href = destination;
            }).catch(error => {
                console.error('Error logging ad click:', error);
                // Redirect anyway
                window.location.href = destination;
            });
        });
    }
}