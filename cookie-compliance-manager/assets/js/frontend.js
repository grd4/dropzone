/**
 * Cookie Compliance Manager - Frontend JavaScript
 */

(function($) {
    'use strict';

    // Cookie Compliance Manager Object
    window.CCM = {
        /**
         * Initialize the plugin
         */
        init: function() {
            this.banner = $('#ccm-cookie-banner');
            this.modal = $('#ccm-settings-modal');
            this.consent = this.getConsent();

            // If no consent exists, show banner
            if (!this.consent) {
                this.showBanner();
            } else {
                // Apply saved consent
                this.applyConsent(this.consent);
            }

            // Event listeners
            this.bindEvents();
        },

        /**
         * Bind event listeners
         */
        bindEvents: function() {
            var self = this;

            // Accept all button
            $('.ccm-btn-accept, .ccm-btn-accept-all').on('click', function(e) {
                e.preventDefault();
                self.acceptAll();
            });

            // Reject all button
            $('.ccm-btn-reject').on('click', function(e) {
                e.preventDefault();
                self.rejectAll();
            });

            // Settings button
            $('.ccm-btn-settings').on('click', function(e) {
                e.preventDefault();
                self.openModal();
            });

            // Save preferences button
            $('.ccm-btn-save-preferences').on('click', function(e) {
                e.preventDefault();
                self.savePreferences();
            });

            // Modal close
            $('.ccm-modal-close').on('click', function() {
                self.closeModal();
            });

            // Close modal on outside click
            $(window).on('click', function(e) {
                if ($(e.target).is('#ccm-settings-modal')) {
                    self.closeModal();
                }
            });
        },

        /**
         * Show cookie banner
         */
        showBanner: function() {
            this.banner.fadeIn(300).attr('data-shown', 'true');
        },

        /**
         * Hide cookie banner
         */
        hideBanner: function() {
            this.banner.fadeOut(300);
        },

        /**
         * Open settings modal
         */
        openModal: function() {
            // Load current consent into checkboxes
            var consent = this.getConsent();
            if (consent) {
                $('#ccm-analytics').prop('checked', consent.analytics || false);
                $('#ccm-marketing').prop('checked', consent.marketing || false);
            }
            this.modal.fadeIn(300);
        },

        /**
         * Close settings modal
         */
        closeModal: function() {
            this.modal.fadeOut(300);
        },

        /**
         * Accept all cookies
         */
        acceptAll: function() {
            var consent = {
                essential: true,
                analytics: true,
                marketing: true,
                timestamp: new Date().toISOString()
            };

            this.saveConsent(consent);
            this.applyConsent(consent);
            this.hideBanner();
            this.closeModal();
            this.logConsent(consent);
        },

        /**
         * Reject all cookies (except essential)
         */
        rejectAll: function() {
            var consent = {
                essential: true,
                analytics: false,
                marketing: false,
                timestamp: new Date().toISOString()
            };

            this.saveConsent(consent);
            this.applyConsent(consent);
            this.hideBanner();
            this.closeModal();
            this.logConsent(consent);
        },

        /**
         * Save user preferences
         */
        savePreferences: function() {
            var consent = {
                essential: true, // Always true
                analytics: $('#ccm-analytics').is(':checked'),
                marketing: $('#ccm-marketing').is(':checked'),
                timestamp: new Date().toISOString()
            };

            this.saveConsent(consent);
            this.applyConsent(consent);
            this.hideBanner();
            this.closeModal();
            this.logConsent(consent);
        },

        /**
         * Save consent to cookie
         */
        saveConsent: function(consent) {
            var expiryDays = ccmData.expiryDays || 365;
            var expiryDate = new Date();
            expiryDate.setTime(expiryDate.getTime() + (expiryDays * 24 * 60 * 60 * 1000));

            document.cookie = 'ccm_consent=' + JSON.stringify(consent) +
                              '; expires=' + expiryDate.toUTCString() +
                              '; path=/; SameSite=Lax';

            this.consent = consent;

            // Trigger custom event
            var event = new CustomEvent('ccm-consent-updated', { detail: consent });
            document.dispatchEvent(event);
        },

        /**
         * Get consent from cookie
         */
        getConsent: function() {
            var name = 'ccm_consent=';
            var decodedCookie = decodeURIComponent(document.cookie);
            var ca = decodedCookie.split(';');

            for (var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) == ' ') {
                    c = c.substring(1);
                }
                if (c.indexOf(name) == 0) {
                    try {
                        return JSON.parse(c.substring(name.length, c.length));
                    } catch (e) {
                        return null;
                    }
                }
            }
            return null;
        },

        /**
         * Check if user has consented to a specific category
         */
        hasConsent: function(category) {
            var consent = this.getConsent();
            return consent && consent[category] === true;
        },

        /**
         * Apply consent by enabling/disabling scripts
         */
        applyConsent: function(consent) {
            // Enable scripts based on consent
            this.enableScripts('essential', true); // Always enabled
            this.enableScripts('analytics', consent.analytics || false);
            this.enableScripts('marketing', consent.marketing || false);
        },

        /**
         * Enable scripts for a specific category
         */
        enableScripts: function(category, enabled) {
            var scripts = $('script[data-cookie-category="' + category + '"]');

            scripts.each(function() {
                var script = $(this);

                if (enabled && script.attr('type') === 'text/plain') {
                    // Enable the script
                    var newScript = document.createElement('script');
                    newScript.textContent = script.html();

                    // Copy attributes
                    $.each(this.attributes, function() {
                        if (this.name !== 'type' && this.name !== 'data-cookie-category') {
                            newScript.setAttribute(this.name, this.value);
                        }
                    });

                    script.replaceWith(newScript);
                } else if (!enabled && script.attr('type') !== 'text/plain') {
                    // Disable the script (convert to text/plain)
                    script.attr('type', 'text/plain');
                }
            });

            // Also handle iframes
            var iframes = $('iframe[data-cookie-category="' + category + '"]');
            iframes.each(function() {
                var iframe = $(this);
                if (enabled && iframe.attr('data-src')) {
                    iframe.attr('src', iframe.attr('data-src'));
                    iframe.removeAttr('data-src');
                } else if (!enabled && iframe.attr('src')) {
                    iframe.attr('data-src', iframe.attr('src'));
                    iframe.removeAttr('src');
                }
            });
        },

        /**
         * Log consent to server via AJAX
         */
        logConsent: function(consent) {
            $.ajax({
                url: ccmData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'ccm_save_consent',
                    nonce: ccmData.nonce,
                    consent: consent
                },
                success: function(response) {
                    console.log('Cookie consent logged successfully');
                },
                error: function(xhr, status, error) {
                    console.error('Failed to log consent:', error);
                }
            });
        },

        /**
         * Revoke consent (for GDPR right to withdraw)
         */
        revokeConsent: function() {
            // Delete the consent cookie
            document.cookie = 'ccm_consent=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
            this.consent = null;

            // Reload to show banner again
            location.reload();
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        CCM.init();
    });

    // Expose revoke function globally for "Manage Cookie Preferences" links
    window.CCMRevokeConsent = function() {
        CCM.revokeConsent();
    };

})(jQuery);
