
document.addEventListener('DOMContentLoaded', () => {
    console.log("Main.js loaded successfully!");

    // 1. Confirmation Prompts for Critical Actions
    const confirmButtons = document.querySelectorAll('.confirm-action');
    confirmButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            const confirmMessage = button.dataset.confirm || "Are you sure?";
            if (!confirm(confirmMessage)) {
                e.preventDefault();
            }
        });
    });

    // 2. Dynamic Search Filters (for job search page)
    const jobFilterForm = document.querySelector('#job-filter-form');
    if (jobFilterForm) {
        jobFilterForm.addEventListener('submit', (e) => {
            e.preventDefault(); // Prevent form submission for now
            const filterData = new FormData(jobFilterForm);
            console.log("Filters applied:", Object.fromEntries(filterData));
            
        });
    }

    // 3. Form Validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', (e) => {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('is-invalid');
                } else {
                    field.classList.remove('is-invalid');
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert("Please fill in all required fields.");
            }
        });
    });

    // 4. File Size Validation
    const fileInputs = document.querySelectorAll('input[type="file"]');
    fileInputs.forEach(input => {
        input.addEventListener('change', () => {
            const maxFileSize = input.dataset.maxSize || 5 * 1024 * 1024; // Default: 5MB
            const file = input.files[0];
            if (file && file.size > maxFileSize) {
                alert(`The file size exceeds the maximum allowed size of ${maxFileSize / 1024 / 1024}MB.`);
                input.value = ''; // Reset file input
            }
        });
    });

    // 5. Display Current Year in Footer
    const currentYearElement = document.querySelector('#current-year');
    if (currentYearElement) {
        currentYearElement.textContent = new Date().getFullYear();
    }

    // 6. Scroll to Top Button
    const scrollToTopButton = document.querySelector('#scroll-to-top');
    if (scrollToTopButton) {
        scrollToTopButton.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        window.addEventListener('scroll', () => {
            if (window.scrollY > 200) {
                scrollToTopButton.classList.add('visible');
            } else {
                scrollToTopButton.classList.remove('visible');
            }
        });
    }
});
