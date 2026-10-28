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
