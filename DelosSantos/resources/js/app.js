import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    const themeToggle = document.querySelector('[data-theme-toggle]');
    const themeIcon = document.querySelector('[data-theme-icon]');
    const themeLabel = document.querySelector('[data-theme-label]');

    const applyTheme = (theme) => {
        const isDark = theme === 'dark';
        document.documentElement.classList.toggle('dark-mode', isDark);
        themeToggle?.setAttribute('aria-pressed', String(isDark));
        themeToggle?.setAttribute('title', isDark ? 'Switch to light mode' : 'Switch to dark mode');
        if (themeIcon) themeIcon.textContent = isDark ? '☼' : '☾';
        if (themeLabel) themeLabel.textContent = isDark ? 'Light mode' : 'Dark mode';
    };

    applyTheme(localStorage.getItem('puihaha-theme') || 'light');
    themeToggle?.addEventListener('click', () => {
        const nextTheme = document.documentElement.classList.contains('dark-mode') ? 'light' : 'dark';
        localStorage.setItem('puihaha-theme', nextTheme);
        applyTheme(nextTheme);
    });

    const mobileMenuToggle = document.querySelector('[data-mobile-nav-toggle]');
    const mobileNav = document.getElementById('portal-main-nav');
    const closeMobileNav = () => {
        document.querySelector('.portal-topbar')?.classList.remove('portal-nav-open');
        mobileMenuToggle?.setAttribute('aria-expanded', 'false');
    };
    mobileMenuToggle?.addEventListener('click', () => {
        const isOpen = document.querySelector('.portal-topbar')?.classList.toggle('portal-nav-open');
        mobileMenuToggle.setAttribute('aria-expanded', String(Boolean(isOpen)));
    });
    mobileNav?.querySelectorAll('a').forEach((link) => link.addEventListener('click', closeMobileNav));
    const navDropdowns = [...document.querySelectorAll('[data-nav-dropdown]')];
    const closeNavDropdowns = () => {
        navDropdowns.forEach((dropdown) => {
            dropdown.classList.remove('is-open');
            dropdown.querySelector('[data-nav-dropdown-toggle]')?.setAttribute('aria-expanded', 'false');
            dropdown.querySelector('[data-nav-dropdown-menu]')?.setAttribute('hidden', '');
        });
    };
    const toggleNavDropdown = (dropdown) => {
        const toggle = dropdown.querySelector('[data-nav-dropdown-toggle]');
        const menu = dropdown.querySelector('[data-nav-dropdown-menu]');
        const shouldOpen = !dropdown.classList.contains('is-open');
        closeNavDropdowns();
        dropdown.classList.toggle('is-open', shouldOpen);
        toggle?.setAttribute('aria-expanded', String(shouldOpen));
        menu?.toggleAttribute('hidden', !shouldOpen);
    };
    navDropdowns.forEach((dropdown) => {
        dropdown.querySelector('[data-nav-dropdown-toggle]')?.addEventListener('click', (event) => {
            event.stopPropagation();
            toggleNavDropdown(dropdown);
        });
        dropdown.querySelectorAll('[data-nav-dropdown-menu] a').forEach((link) => link.addEventListener('click', () => {
            closeNavDropdowns();
            closeMobileNav();
        }));
    });
    window.closePuihahaNavDropdowns = closeNavDropdowns;
    document.addEventListener('click', (event) => {
        if (!event.target.closest('[data-nav-dropdown]')) closeNavDropdowns();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeMobileNav();
            closeNavDropdowns();
        }
    });

    const description = document.querySelector('#description');
    const characterCount = document.querySelector('[data-character-count]');
    const form = document.querySelector('[data-task-form]');
    const submitButton = document.querySelector('[data-submit-button]');

    const updateCharacterCount = () => {
        if (!description || !characterCount) return;
        characterCount.textContent = `${description.value.length} / ${description.maxLength}`;
    };

    description?.addEventListener('input', updateCharacterCount);
    updateCharacterCount();

    form?.addEventListener('submit', () => {
        submitButton?.classList.add('is-submitting');
        if (submitButton) submitButton.innerHTML = '<span class="button-plus">↻</span> Saving...';
    });

    document.querySelector('.alert-close')?.addEventListener('click', (event) => {
        event.currentTarget.closest('.alert')?.remove();
    });

    const filterButtons = document.querySelectorAll('[data-filter]');
    const taskItems = document.querySelectorAll('[data-task-item]');
    const emptyFilter = document.querySelector('[data-empty-filter]');

    filterButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const filter = button.dataset.filter;
            let visibleCount = 0;

            filterButtons.forEach((item) => {
                const isActive = item === button;
                item.classList.toggle('is-active', isActive);
                item.setAttribute('aria-selected', String(isActive));
            });

            taskItems.forEach((item) => {
                const shouldShow = filter === 'all' || item.dataset.status === filter;
                item.classList.toggle('is-hidden', !shouldShow);
                if (shouldShow) visibleCount += 1;
            });

            if (emptyFilter) emptyFilter.hidden = visibleCount > 0;
        });
    });

    document.querySelectorAll('[data-confirm]').forEach((confirmForm) => {
        confirmForm.addEventListener('submit', (event) => {
            if (!window.confirm(confirmForm.dataset.confirm)) event.preventDefault();
        });
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const closeExtensionModal = (modal) => {
        if (!modal) return;
        modal.classList.remove('is-open');
        window.setTimeout(() => modal.setAttribute('hidden', ''), 180);
    };

    document.querySelectorAll('[data-extension-open]').forEach((button) => {
        button.addEventListener('click', () => {
            const modal = document.getElementById(button.dataset.extensionOpen);
            modal?.removeAttribute('hidden');
            window.requestAnimationFrame(() => modal?.classList.add('is-open'));
            modal?.querySelector('input, textarea, button')?.focus();
        });
    });

    document.querySelectorAll('[data-extension-cancel]').forEach((button) => {
        button.addEventListener('click', () => closeExtensionModal(button.closest('[data-extension-modal]')));
    });

    document.querySelectorAll('[data-extension-modal]').forEach((modal) => {
        modal.addEventListener('click', (event) => {
            if (event.target === modal) closeExtensionModal(modal);
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;
        document.querySelectorAll('[data-extension-modal]:not([hidden])').forEach(closeExtensionModal);
    });
});

const puihahaValidationMessage = (field) => {
    const name = field.name;
    const value = field.value.trim();
    const hasPasswordPolicy = Boolean(field.closest('form[data-password-policy]'));
    if (field.required && !value) return 'This field is required.';
    if (value && field.type === 'email' && !/^\S+@\S+\.\S+$/.test(value)) return 'Enter a valid email address.';
    if (value && name === 'phone' && !/^(09\d{9}|\+639\d{9})$/.test(value)) return 'Use 09XXXXXXXXX or +639XXXXXXXXX.';
    if (value && field.type === 'date' && value > new Date().toISOString().slice(0, 10)) return 'Birth date cannot be in the future.';
    if (name === 'password' && hasPasswordPolicy && value && !(/^(?=.{8,}$)(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).*$/.test(value))) return 'Use 8+ characters with uppercase, lowercase, number, and symbol.';
    if (name === 'password_confirmation' && value && document.querySelector('#password')?.value !== value) return 'Passwords do not match.';
    return '';
};

const validatePuihahaField = (field) => {
    if (!field || field.type === 'hidden' || field.disabled) return true;
    const message = puihahaValidationMessage(field);
    field.classList.toggle('input-invalid', Boolean(message));
    field.setAttribute('aria-invalid', message ? 'true' : 'false');
    const fieldContainer = field.closest('.password-wrap')?.parentElement ?? field.parentElement;
    let error = fieldContainer?.querySelector('[data-client-error]');
    if (!error && message) {
        error = document.createElement('span');
        error.className = 'field-error';
        error.dataset.clientError = 'true';
        fieldContainer?.append(error);
    }
    if (error) {
        error.textContent = message;
        error.hidden = !message;
    }
    return !message;
};

window.initPuihahaMaps = () => {
    document.querySelectorAll('[data-address-map]').forEach((root) => {
        if (root.dataset.mapReady === 'true' || !window.google?.maps) return;
        root.dataset.mapReady = 'true';
        const geocoder = new google.maps.Geocoder();
        const scope = root.matches('[data-payment-step]') ? root : root.closest('[data-payment-step]') || root;
        const canvas = root.querySelector('[data-map-canvas]') || scope.querySelector('[data-map-canvas]');
        const latitude = root.querySelector('#latitude') || scope.querySelector('#latitude');
        const longitude = root.querySelector('#longitude') || scope.querySelector('#longitude');
        const addressInput = root.querySelector('[data-full-address]') || scope.querySelector('[data-full-address]');
        const parts = [...root.querySelectorAll('[data-address-part]')];
        const fallback = root.querySelector('[data-map-fallback]') || scope.querySelector('[data-map-fallback]');
        let marker;
        let lookups = 0;
        let timer;
        const updateAddress = () => {
            if (addressInput) addressInput.value = parts.map((input) => input.value.trim()).filter(Boolean).join(', ');
        };
        const setMarker = (position) => {
            if (!canvas) return;
            const map = canvas._puihahaMap;
            if (!map) return;
            if (!marker) {
                marker = new google.maps.Marker({ map, draggable: true });
                marker.addListener('dragend', (event) => reverseGeocode(event.latLng));
            }
            marker.setPosition(position);
            map.panTo(position);
            if (latitude) latitude.value = Number(position.lat()).toFixed(7);
            if (longitude) longitude.value = Number(position.lng()).toFixed(7);
        };
        const fillFromResult = (result, overwrite = false) => {
            const components = {};
            result.address_components?.forEach((component) => component.types.forEach((type) => { components[type] = component.long_name; }));
            const values = {
                house_street: [components.street_number, components.route].filter(Boolean).join(' '),
                barangay: components.sublocality_level_1 || components.sublocality || '',
                city_municipality: components.locality || components.administrative_area_level_2 || '',
                province: components.administrative_area_level_1 || '',
                zip_code: components.postal_code || '',
            };
            Object.entries(values).forEach(([name, value]) => {
                const input = root.querySelector(`[name="${name}"]`);
                if (input && value && (overwrite || !input.value.trim())) input.value = value;
            });
            updateAddress();
        };
        const reverseGeocode = (position) => {
            if (lookups >= 5) { if (fallback) { fallback.hidden = false; fallback.textContent = 'Map lookup limit reached for this session. Your typed address remains usable.'; } return; }
            lookups += 1;
            geocoder.geocode({ location: position }, (results, status) => {
                if (status === 'OK' && results?.[0]) fillFromResult(results[0], true);
                else if (fallback) fallback.hidden = false;
            });
        };
        const geocodeTypedAddress = () => {
            const query = parts.map((input) => input.value.trim()).filter(Boolean).join(', ');
            if (!query || lookups >= 5) return;
            const cacheKey = `puihaha-geocode:${query.toLowerCase()}`;
            const cached = sessionStorage.getItem(cacheKey);
            if (cached) {
                const result = JSON.parse(cached);
                setMarker({ lat: () => result.lat, lng: () => result.lng });
                return;
            }
            lookups += 1;
            geocoder.geocode({ address: `${query}, Philippines` }, (results, status) => {
                if (status === 'OK' && results?.[0]) {
                    const location = results[0].geometry.location;
                    const result = { lat: location.lat(), lng: location.lng() };
                    sessionStorage.setItem(cacheKey, JSON.stringify(result));
                    setMarker(location);
                    fillFromResult(results[0]);
                } else if (fallback) fallback.hidden = false;
            });
        };
        const map = new google.maps.Map(canvas, { center: { lat: 14.5995, lng: 120.9842 }, zoom: 11, disableDefaultUI: true });
        canvas._puihahaMap = map;
        if (latitude?.value && longitude?.value) setMarker({ lat: () => Number(latitude.value), lng: () => Number(longitude.value) });
        map.addListener('click', (event) => {
            if (parts.some((input) => input.value.trim()) && !window.confirm('Replace address fields with the selected map location?')) return;
            setMarker(event.latLng);
            reverseGeocode(event.latLng);
        });
        (root.querySelector('[data-locate-map]') || scope.querySelector('[data-locate-map]'))?.addEventListener('click', geocodeTypedAddress);
        parts.forEach((input) => {
            input.addEventListener('input', () => {
                if (latitude) latitude.value = '';
                if (longitude) longitude.value = '';
                updateAddress();
                clearTimeout(timer);
                timer = setTimeout(geocodeTypedAddress, 1000);
            });
            input.addEventListener('blur', geocodeTypedAddress);
        });
    });
};

document.addEventListener('DOMContentLoaded', () => {
    const canonicalEyePath = 'M2.5 12s3.4-5.5 9.5-5.5 9.5 5.5 9.5 5.5-3.4 5.5-9.5 5.5S2.5 12 2.5 12Z';
    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        button.querySelector('.password-toggle-icon > path:not(.eye-slash)')?.setAttribute('d', canonicalEyePath);
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.passwordToggle);
            if (!input) return;
            input.type = input.type === 'password' ? 'text' : 'password';
            const isVisible = input.type === 'text';
            button.classList.toggle('is-visible', isVisible);
            button.setAttribute('aria-label', isVisible ? 'Hide password' : 'Show password');
            button.setAttribute('title', isVisible ? 'Hide password' : 'Show password');
        });
    });

    document.querySelectorAll('[data-live-validation]').forEach((form) => {
        const fields = [...form.querySelectorAll('input, select, textarea')];
        fields.forEach((field) => {
            field.addEventListener('blur', () => validatePuihahaField(field));
            field.addEventListener('input', () => { if (field.classList.contains('input-invalid')) validatePuihahaField(field); });
        });
        form.addEventListener('submit', (event) => {
            const valid = fields.map(validatePuihahaField).every(Boolean);
            if (!valid) { event.preventDefault(); form.querySelector('.input-invalid')?.focus(); }
        });
    });

    document.querySelectorAll('[data-level-benefits-select]').forEach((select) => {
        const card = document.querySelector('[data-selected-level-benefits]');
        const name = card?.querySelector('[data-selected-level-name]');
        const fee = card?.querySelector('[data-selected-level-fee]');
        const validity = card?.querySelector('[data-selected-level-validity]');
        const benefits = card?.querySelector('[data-selected-level-benefits-text]');
        const renderSelectedLevel = () => {
            const option = select.selectedOptions[0];
            const hasSelection = Boolean(option?.value);
            card?.toggleAttribute('hidden', !hasSelection);
            if (!hasSelection) return;
            if (name) name.textContent = option.dataset.levelName || option.textContent.trim();
            if (fee) fee.textContent = `₱${option.dataset.levelFee || '0.00'}`;
            if (validity) validity.textContent = `${option.dataset.levelValidity || '0'} months`;
            if (benefits) benefits.textContent = option.dataset.levelBenefits || 'Benefits will be confirmed by your administrator.';
        };
        select.addEventListener('change', renderSelectedLevel);
        renderSelectedLevel();
    });

    const profileButton = document.querySelector('[data-profile-toggle]');
    const profileMenu = document.querySelector('[data-profile-menu]');
    const closeProfileMenu = () => {
        profileMenu?.setAttribute('hidden', '');
        profileButton?.setAttribute('aria-expanded', 'false');
    };
    const closeNotificationPanel = () => {
        notificationPanel?.setAttribute('hidden', '');
        notificationButton?.setAttribute('aria-expanded', 'false');
    };
    profileButton?.addEventListener('click', () => {
        const isHidden = profileMenu?.hasAttribute('hidden');
        closeNotificationPanel();
        window.closePuihahaNavDropdowns?.();
        profileMenu?.toggleAttribute('hidden', !isHidden);
        profileButton.setAttribute('aria-expanded', String(Boolean(isHidden)));
    });
    document.addEventListener('click', (event) => {
        if (!event.target.closest('[data-profile-menu], [data-profile-toggle]')) closeProfileMenu();
        if (!event.target.closest('[data-notification-panel], [data-notification-button]')) closeNotificationPanel();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeProfileMenu();
            closeNotificationPanel();
        }
    });

    const notificationButton = document.querySelector('[data-notification-button]');
    const notificationPanel = document.querySelector('[data-notification-panel]');
    const notificationList = document.querySelector('[data-notification-list]');
    const notificationCount = document.querySelector('[data-notification-count]');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const escapeNotification = (value) => String(value ?? '').replace(/[&<>"']/g, (character) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[character]);
    const loadNotifications = () => {
        if (!notificationPanel) return;
        fetch('/notifications', { headers: { Accept: 'application/json' } })
            .then((response) => response.ok ? response.json() : null)
            .then((payload) => {
                if (!payload) return;
                if (notificationCount) { notificationCount.textContent = payload.unread; notificationCount.hidden = payload.unread < 1; }
                if (!notificationList) return;
                notificationList.innerHTML = payload.notifications.length ? payload.notifications.map((item) => `<a class="notification-item ${item.read ? '' : 'is-unread'}" data-notification-id="${escapeNotification(item.id)}" href="${escapeNotification(item.url || '#')}"><strong>${escapeNotification(item.title)}</strong><span>${escapeNotification(item.message)}</span><small>${escapeNotification(item.created_at || '')}</small></a>`).join('') : '<p class="notification-empty">No notifications yet.</p>';
            }).catch(() => {});
    };
    notificationButton?.addEventListener('click', () => {
        const isHidden = notificationPanel?.hasAttribute('hidden');
        closeProfileMenu();
        window.closePuihahaNavDropdowns?.();
        notificationPanel?.toggleAttribute('hidden', !isHidden);
        notificationButton.setAttribute('aria-expanded', String(Boolean(isHidden)));
        if (isHidden) loadNotifications();
    });
    document.querySelector('[data-notification-read-all]')?.addEventListener('click', () => {
        fetch('/notifications/read-all', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' } }).then(loadNotifications).catch(() => {});
    });
    notificationList?.addEventListener('click', (event) => {
        const item = event.target.closest('[data-notification-id]');
        if (!item) return;
        event.preventDefault();
        const destination = item.getAttribute('href') || '/tasks';
        fetch(`/notifications/${encodeURIComponent(item.dataset.notificationId)}/read`, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' } })
            .catch(() => {})
            .finally(() => { window.location.assign(destination); });
    });
    loadNotifications();
    if (notificationPanel) window.setInterval(loadNotifications, 60000);
    const signoutModal = document.querySelector('[data-signout-modal]');
    const openSignoutModal = () => {
        closeProfileMenu();
        signoutModal?.removeAttribute('hidden');
        window.requestAnimationFrame(() => signoutModal?.classList.add('is-open'));
    };
    const closeSignoutModal = () => {
        signoutModal?.classList.remove('is-open');
        window.setTimeout(() => signoutModal?.setAttribute('hidden', ''), 180);
    };
    document.querySelectorAll('[data-signout-trigger]').forEach((button) => button.addEventListener('click', openSignoutModal));
    document.querySelectorAll('[data-signout-cancel]').forEach((button) => button.addEventListener('click', closeSignoutModal));
    signoutModal?.addEventListener('click', (event) => { if (event.target === signoutModal) closeSignoutModal(); });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && signoutModal && !signoutModal.hasAttribute('hidden')) closeSignoutModal();
    });

    document.querySelectorAll('[data-toast]').forEach((toast) => {
        let timeout;
        const start = () => { timeout = setTimeout(() => toast.remove(), 15000); };
        const stop = () => clearTimeout(timeout);
        toast.addEventListener('mouseenter', stop); toast.addEventListener('mouseleave', start); start();
        toast.querySelector('[data-toast-close]')?.addEventListener('click', () => toast.remove());
    });

    document.querySelectorAll('[data-weather-card]').forEach((card) => {
        const latitude = card.dataset.latitude;
        const longitude = card.dataset.longitude;
        const status = card.querySelector('[data-weather-status]');
        const details = card.querySelector('[data-weather-details]');
        if (!latitude || !longitude) {
            return;
        }

        const controller = new AbortController();
        const timeout = window.setTimeout(() => controller.abort(), 10000);
        fetch(`/weather?lat=${encodeURIComponent(latitude)}&lon=${encodeURIComponent(longitude)}`, { headers: { Accept: 'application/json' }, signal: controller.signal })
            .then((response) => response.ok ? response.json() : Promise.reject())
            .then((weather) => {
                if (status) status.hidden = true;
                if (details) details.hidden = false;
                const icon = card.querySelector('[data-weather-icon]');
                const iconMap = { '01': '☀', '02': '◐', '03': '☁', '04': '☁', '09': '☂', '10': '☂', '11': 'ϟ', '13': '❄', '50': '≋' };
                if (icon) icon.textContent = iconMap[String(weather.icon || '01').slice(0, 2)] || '☼';
                card.querySelector('[data-weather-temperature]').textContent = `${weather.temperature}°C`;
                card.querySelector('[data-weather-place]').textContent = weather.place;
                card.querySelector('[data-weather-description]').textContent = weather.description;
                card.querySelector('[data-weather-meta]').textContent = `Humidity ${weather.humidity}% · Wind ${weather.wind_speed} m/s`;
            })
            .catch(() => { if (status) { status.hidden = false; status.textContent = 'Weather is currently unavailable. Your saved location remains safe.'; } })
            .finally(() => window.clearTimeout(timeout));
    });

    document.querySelectorAll('[data-insight-tab]').forEach((button) => button.addEventListener('click', () => {
        const panel = document.querySelector('[data-insight-panel]');
        const list = document.querySelector('.task-list');
        const empty = document.querySelector('.empty-filter');
        const insights = button.dataset.insightTab === 'insights';
        panel?.toggleAttribute('hidden', !insights); list?.toggleAttribute('hidden', insights); empty?.toggleAttribute('hidden', insights || !empty.hidden);
        button.classList.toggle('is-active', insights); button.setAttribute('aria-selected', String(insights));
    }));
    document.querySelectorAll('[data-insight-mode]').forEach((button) => button.addEventListener('click', () => {
        const charts = document.querySelector('[data-insight-charts]');
        const cards = document.querySelector('[data-insight-cards]');
        const showCharts = button.dataset.insightMode === 'charts';
        charts?.toggleAttribute('hidden', !showCharts); cards?.toggleAttribute('hidden', showCharts);
        document.querySelectorAll('[data-insight-mode]').forEach((item) => item.classList.toggle('is-active', item === button));
    }));

    document.querySelectorAll('.js-task-scroll').forEach((link) => link.addEventListener('click', (event) => {
        const target = document.querySelector('#insights');
        if (!target || window.location.pathname !== new URL(link.href).pathname) return;
        event.preventDefault();
        const distance = target.getBoundingClientRect().top;
        if (Math.abs(distance) < 4) return;
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) { target.scrollIntoView(); return; }
        const start = window.scrollY; const destination = start + distance; const duration = Math.min(1200, Math.max(600, Math.abs(distance) * 1.4)); const started = performance.now();
        const animate = (now) => { const progress = Math.min(1, (now - started) / duration); const eased = progress < .5 ? 2 * progress * progress : 1 - Math.pow(-2 * progress + 2, 2) / 2; window.scrollTo(0, start + ((destination - start) * eased)); if (progress < 1) requestAnimationFrame(animate); };
        requestAnimationFrame(animate);
    }));

    const paymentForm = document.querySelector('[data-payment-checkout]');
    const paymentMethodStep = paymentForm?.querySelector('[data-payment-step="method"]');
    const paymentPayerStep = paymentForm?.querySelector('[data-payment-step="payer"]');
    const showPayerStep = () => {
        const selectedMethod = paymentForm?.querySelector('input[name="payment_channel"]:checked');
        if (!selectedMethod) {
            paymentForm?.querySelector('input[name="payment_channel"]')?.focus();
            return;
        }
        paymentMethodStep?.setAttribute('hidden', '');
        paymentPayerStep?.removeAttribute('hidden');
        paymentPayerStep?.querySelector('input, textarea')?.focus();
    };
    paymentForm?.querySelector('[data-payment-continue]')?.addEventListener('click', showPayerStep);
    paymentForm?.querySelector('[data-payment-back]')?.addEventListener('click', () => {
        paymentPayerStep?.setAttribute('hidden', '');
        paymentMethodStep?.removeAttribute('hidden');
        paymentForm?.querySelector('input[name="payment_channel"]:checked')?.focus();
    });
    paymentForm?.querySelectorAll('input[name="payment_channel"]').forEach((input) => input.addEventListener('change', () => {
        document.querySelector('[data-online-note]')?.toggleAttribute('hidden', input.value === 'front_desk');
        document.querySelector('[data-frontdesk-note]')?.toggleAttribute('hidden', input.value !== 'front_desk');
    }));
    if (document.body.classList.contains('tasks-page')) window.scrollTo(0, 0);
});

document.addEventListener('DOMContentLoaded', () => {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const workspace = document.querySelector('[data-task-workspace]');
    const allTaskItems = () => [...document.querySelectorAll('[data-task-item]')];

    const updateSummary = (summary) => {
        Object.entries({ assigned: summary.assigned, todo: summary.todo, completed: summary.completed }).forEach(([key, value]) => {
            document.querySelectorAll(`[data-task-summary="${key}"]`).forEach((element) => { element.textContent = value; });
        });
        document.querySelectorAll('[data-completion-percent]').forEach((element) => { element.textContent = `${summary.completion_percent}%`; });
        document.querySelectorAll('[data-completion-count]').forEach((element) => { element.textContent = summary.completed; });
        document.querySelectorAll('[data-remaining-count]').forEach((element) => { element.textContent = summary.todo; });
        document.querySelectorAll('[data-overdue-count]').forEach((element) => { element.textContent = summary.overdue; });
        document.querySelectorAll('[data-overdue-pill]').forEach((element) => { element.hidden = summary.overdue < 1; });
        document.querySelectorAll('[data-completion-donut] .donut-done').forEach((element) => { element.setAttribute('stroke-dasharray', `${summary.completion_percent} ${100 - summary.completion_percent}`); });
    };

    const updateCompletionChart = (completedByDay) => {
        document.querySelectorAll('[data-completion-point]').forEach((point) => {
            const day = point.dataset.day;
            const count = Number(completedByDay?.[day] ?? 0);
            point.style.bottom = `${count ? Math.min(82, 16 + (count * 18)) : 8}%`;
            point.dataset.tooltip = `${day}: ${count} completed`;
            point.title = point.dataset.tooltip;
        });
    };

    const updateTaskCard = (payload) => {
        const item = document.querySelector(`[data-task-item][data-task-id="${payload.id}"]`);
        if (!item) return;
        item.dataset.taskCompleted = String(payload.completed);
        item.dataset.taskStatus = payload.filter_status;
        item.classList.toggle('is-completed', payload.completed);
        const mark = item.querySelector('.task-status-mark');
        if (mark) {
            mark.textContent = payload.completed ? '✓' : '';
            mark.setAttribute('aria-pressed', String(payload.completed));
            mark.setAttribute('aria-label', payload.completed ? 'Mark task active' : 'Mark task complete');
        }
        const status = item.querySelector('.task-status');
        if (status) {
            status.className = `task-status task-status-${payload.status_key}`;
            status.textContent = payload.status_label;
        }
    };

    const matchesFilter = (item) => {
        if (!workspace) return true;
        const query = workspace.querySelector('[data-task-search]')?.value.trim().toLowerCase() || '';
        const priority = workspace.querySelector('[data-task-priority]')?.value || 'all';
        const status = workspace.querySelector('[data-task-status]')?.value || 'all';
        const searchable = item.textContent.toLowerCase();
        return (!query || searchable.includes(query))
            && (priority === 'all' || item.dataset.taskPriority === priority)
            && (status === 'all' || item.dataset.taskStatus === status);
    };

    let currentPage = 1;
    const applyTaskFilters = () => {
        const items = allTaskItems();
        if (!workspace) return;
        const matching = items.filter(matchesFilter);
        const pageSize = Number(workspace.dataset.pageSize || 8);
        const pageCount = Math.max(1, Math.ceil(matching.length / pageSize));
        currentPage = Math.min(currentPage, pageCount);
        const start = (currentPage - 1) * pageSize;
        items.forEach((item) => item.classList.add('is-hidden'));
        matching.slice(start, start + pageSize).forEach((item) => item.classList.remove('is-hidden'));
        workspace.querySelector('[data-task-empty]')?.toggleAttribute('hidden', matching.length > 0);
        const resultCount = workspace.querySelector('[data-task-result-count]');
        if (resultCount) resultCount.textContent = `${matching.length} task${matching.length === 1 ? '' : 's'}`;
        const pagination = workspace.querySelector('[data-task-pagination]');
        if (!pagination) return;
        pagination.innerHTML = '';
        if (pageCount <= 1) return;
        const addButton = (label, page, disabled = false, active = false) => {
            const button = document.createElement('button');
            button.type = 'button'; button.textContent = label; button.disabled = disabled; button.classList.toggle('is-active', active); button.setAttribute('aria-label', `Go to task page ${page}`);
            button.addEventListener('click', () => { currentPage = page; applyTaskFilters(); });
            pagination.append(button);
        };
        addButton('←', Math.max(1, currentPage - 1), currentPage === 1);
        for (let page = 1; page <= pageCount; page += 1) addButton(String(page), page, false, page === currentPage);
        addButton('→', Math.min(pageCount, currentPage + 1), currentPage === pageCount);
    };

    workspace?.querySelectorAll('[data-task-search], [data-task-priority], [data-task-status]').forEach((field) => field.addEventListener(field.matches('input') ? 'input' : 'change', () => { currentPage = 1; applyTaskFilters(); }));
    workspace?.querySelector('[data-task-filter-submit]')?.addEventListener('click', () => { currentPage = 1; applyTaskFilters(); });
    workspace?.querySelector('[data-task-filter-clear]')?.addEventListener('click', () => {
        workspace.querySelector('[data-task-search]').value = '';
        workspace.querySelector('[data-task-priority]').value = 'all';
        workspace.querySelector('[data-task-status]').value = 'all';
        currentPage = 1; applyTaskFilters();
    });

    document.querySelectorAll('[data-task-completion-form]').forEach((form) => form.addEventListener('submit', async (event) => {
        if (!window.fetch) return;
        event.preventDefault();
        const button = form.querySelector('.task-status-mark');
        if (button) button.disabled = true;
        try {
            const response = await fetch(form.action, { method: 'POST', body: new FormData(form), headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            if (!response.ok) throw new Error('Task update failed');
            const payload = await response.json();
            updateTaskCard(payload.task);
            updateSummary(payload.summary);
            updateCompletionChart(payload.completed_by_day);
            applyTaskFilters();
        } catch (error) {
            HTMLFormElement.prototype.submit.call(form);
        } finally {
            if (button) button.disabled = false;
        }
    }));

    document.querySelectorAll('[data-dashboard-tab]').forEach((tab) => tab.addEventListener('click', () => {
        const selected = tab.dataset.dashboardTab;
        document.querySelectorAll('[data-dashboard-tab]').forEach((item) => {
            const active = item === tab;
            item.classList.toggle('is-active', active);
            item.setAttribute('aria-selected', String(active));
        });
        document.querySelectorAll('[data-dashboard-panel]').forEach((panel) => {
            panel.toggleAttribute('hidden', panel.dataset.dashboardPanel !== selected);
        });
    }));

    const closeTransactionModals = () => document.querySelectorAll('[data-cancel-modal], [data-reason-modal]').forEach((modal) => {
        modal.classList.remove('is-open');
        window.setTimeout(() => modal.setAttribute('hidden', ''), 180);
    });
    document.querySelectorAll('[data-cancel-trigger]').forEach((button) => button.addEventListener('click', () => {
        const modal = document.querySelector('[data-cancel-modal]');
        const form = modal?.querySelector('[data-cancel-modal-form]');
        const reason = modal?.querySelector('[name="cancel_reason"]');
        if (!modal || !form) return;
        form.action = button.dataset.cancelAction;
        form.reset();
        modal.removeAttribute('hidden');
        window.requestAnimationFrame(() => modal.classList.add('is-open'));
        window.setTimeout(() => reason?.focus(), 0);
    }));
    document.querySelectorAll('[data-reason-trigger]').forEach((button) => button.addEventListener('click', () => {
        const modal = document.querySelector('[data-reason-modal]');
        const text = modal?.querySelector('[data-reason-text]');
        if (!modal || !text) return;
        text.textContent = button.dataset.reason || 'No reason was recorded.';
        modal.removeAttribute('hidden');
        window.requestAnimationFrame(() => modal.classList.add('is-open'));
    }));
    document.querySelectorAll('[data-modal-close]').forEach((button) => button.addEventListener('click', closeTransactionModals));
    document.querySelectorAll('[data-cancel-modal], [data-reason-modal]').forEach((modal) => modal.addEventListener('click', (event) => {
        if (event.target === modal) closeTransactionModals();
    }));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closeTransactionModals();
    });

    applyTaskFilters();
});
