<script>
    (function() {
        'use strict';

        /* ═══════════════════════════════════════════════════════════════════
         |  1.  NProgress  – top loading bar
         ══════════════════════════════════════════════════════════════════ */
        NProgress.configure({
            showSpinner: false,
            trickleSpeed: 200,
            minimum: 0.08,
        });

        // Finish bar when DOM is ready (covers normal page loads)
        document.addEventListener('DOMContentLoaded', function() {
            NProgress.done();
        });

        // Start bar on any page navigation (non-Axios link clicks)
        document.addEventListener('click', function(e) {
            const anchor = e.target.closest('a');
            if (
                anchor &&
                anchor.href &&
                !anchor.target &&
                !anchor.href.startsWith('#') &&
                !anchor.href.startsWith('javascript') &&
                anchor.href.startsWith(window.location.origin)
            ) {
                NProgress.start();
            }
        });

        window.addEventListener('beforeunload', function() {
            NProgress.start();
        });


        /* ═══════════════════════════════════════════════════════════════════
         |  2.  Global Toast helper
         |      Usage:
         |        Toast.success('Saved!')
         |        Toast.error('Something went wrong.')
         |        Toast.warning('Check your input.')
         |        Toast.info('Page refreshed.')
         ══════════════════════════════════════════════════════════════════ */
        window.Toast = (function() {

            function show(message, className, duration) {
                Toastify({
                    text: message,
                    duration: duration || 3500,
                    gravity: 'top',
                    position: 'right',
                    className: className,
                    stopOnFocus: true,
                    style: {}, // colours are handled via CSS classes
                }).showToast();
            }

            return {
                success: function(msg, duration) {
                    show(msg, 'toast-success', duration);
                },
                error: function(msg, duration) {
                    show(msg, 'toast-error', duration);
                },
                warning: function(msg, duration) {
                    show(msg, 'toast-warning', duration);
                },
                info: function(msg, duration) {
                    show(msg, 'toast-info', duration);
                },

                /**
                 * Auto-dispatch from any standard ApiResponse payload.
                 *
                 * Usage:  Toast.fromResponse(res.data)
                 *         Toast.fromResponse(err.response.data)
                 */
                fromResponse: function(data) {
                    if (!data || !data.message) return;
                    if (data.success) {
                        this.success(data.message);
                    } else {
                        this.error(data.message);
                    }
                },
            };
        })();


        /* ═══════════════════════════════════════════════════════════════════
         |  3.  Global Alert helper  (SweetAlert2 wrapper)
         |
         |  Usage examples:
         |
         |    // Simple confirm → returns Promise<bool>
         |    Alert.confirm('Delete this item?')
         |         .then(ok => { if (ok) doDelete(); });
         |
         |    // Confirm with custom options
         |    Alert.confirm('Log out?', {
         |        confirmText : 'Yes, log out',
         |        icon        : 'warning',
         |        type        : 'danger',       // 'confirm' | 'danger' | 'warning'
         |    }).then(ok => { if (ok) logout(); });
         |
         |    // Info / success / error / warning dialogs
         |    Alert.success('Record saved!')
         |    Alert.error('Something went wrong.')
         |    Alert.warning('Are you sure?')
         |    Alert.info('Session will expire soon.')
         ══════════════════════════════════════════════════════════════════ */
        window.Alert = (function() {

            // Base SweetAlert2 defaults shared by all calls
            var _base = {
                buttonsStyling: false, // we use our own CSS classes
                reverseButtons: true,
                allowOutsideClick: false,
                customClass: {
                    cancelButton: 'swal2-cancel',
                },
            };

            /**
             * Map shorthand type → CSS class on the confirm button.
             * Note: SweetAlert2 replaces the confirm button's className entirely
             * with whatever is in customClass.confirmButton, so we must NOT
             * include 'swal2-confirm' here — SweetAlert2 adds that itself.
             */
            function _confirmClass(type) {
                var map = {
                    confirm: 'swal-btn-confirm',
                    danger: 'swal-btn-danger',
                    warning: 'swal-btn-warning',
                };
                return map[type] || map.confirm;
            }

            /**
             * Show a confirm dialog.
             *
             * @param  {string}  text     Body text
             * @param  {object}  options  Optional overrides
             * @returns {Promise<boolean>}
             */
            function confirm(text, options) {
                options = options || {};
                return Swal.fire(Object.assign({}, _base, {
                    title: options.title || 'Are you sure?',
                    html: text,
                    icon: options.icon || 'warning',
                    showCancelButton: true,
                    confirmButtonText: options.confirmText || 'Yes, confirm',
                    cancelButtonText: options.cancelText || 'Cancel',
                    customClass: Object.assign({}, _base.customClass, {
                        confirmButton: _confirmClass(options.type || 'confirm'),
                    }),
                })).then(function(result) {
                    return result.isConfirmed;
                });
            }

            /**
             * Simple one-button alert dialogs.
             */
            function _alert(title, text, icon, type) {
                return Swal.fire(Object.assign({}, _base, {
                    title: title,
                    html: text || '',
                    icon: icon,
                    showCancelButton: false,
                    confirmButtonText: 'OK',
                    customClass: Object.assign({}, _base.customClass, {
                        confirmButton: _confirmClass(type || 'confirm'),
                    }),
                }));
            }

            return {
                confirm: confirm,
                success: function(title, text) {
                    return _alert(title, text, 'success', 'confirm');
                },
                error: function(title, text) {
                    return _alert(title, text, 'error', 'danger');
                },
                warning: function(title, text) {
                    return _alert(title, text, 'warning', 'warning');
                },
                info: function(title, text) {
                    return _alert(title, text, 'info', 'confirm');
                },
            };
        })();


        /* ═══════════════════════════════════════════════════════════════════
         |  4.  Axios – global defaults & interceptors
         ══════════════════════════════════════════════════════════════════ */
        const csrfToken = document.querySelector('meta[name="csrf-token"]');

        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        axios.defaults.headers.common['Accept'] = 'application/json';

        if (csrfToken) {
            axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.content;
        }

        // ── Request: start progress bar ───────────────────────────────────
        axios.interceptors.request.use(function(config) {
            NProgress.start();
            return config;
        }, function(error) {
            NProgress.done();
            return Promise.reject(error);
        });

        // ── Response: finish progress bar, auto-toast on 5xx ─────────────
        axios.interceptors.response.use(
            function(response) {
                NProgress.done();
                return response;
            },
            function(error) {
                NProgress.done();

                const status = error.response?.status;

                if (status === 401) {
                    // Session expired — redirect if server says so
                    const redirect = error.response?.data?.redirect;
                    if (redirect) {
                        Toast.error(error.response.data.message || 'Session expired.');
                        setTimeout(function() {
                            window.location.href = redirect;
                        }, 1500);
                    }
                } else if (status === 403) {
                    // Forbidden — session expired mid-flow (OTP, etc.)
                    const data = error.response?.data;
                    if (data?.redirect) {
                        Toast.error(data.message || 'Access denied. Redirecting…');
                        setTimeout(function() {
                            window.location.href = data.redirect;
                        }, 1500);
                    }
                } else if (status === 419) {
                    Toast.warning('Page expired. Refreshing…');
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);
                } else if (status >= 500) {
                    Toast.error('A server error occurred. Please try again.');
                }

                return Promise.reject(error);
            }
        );

    })();
</script>
