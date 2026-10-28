import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
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
});
