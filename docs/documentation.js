document.addEventListener('DOMContentLoaded', () => {

    // --- Theme Toggler (Task 2) ---
    const toggleButton = document.getElementById('theme-toggle-btn');
    if (toggleButton) {
        const currentTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', currentTheme);

        if (currentTheme === 'dark') {
            toggleButton.textContent = 'الوضع الفاتح';
        } else {
            toggleButton.textContent = 'الوضع الداكن';
        }

        toggleButton.addEventListener('click', () => {
            let theme = document.documentElement.getAttribute('data-theme');
            if (theme === 'light') {
                document.documentElement.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
                toggleButton.textContent = 'الوضع الفاتح';
            } else {
                document.documentElement.setAttribute('data-theme', 'light');
                localStorage.setItem('theme', 'light');
                toggleButton.textContent = 'الوضع الداكن';
            }
        });
    }

    // --- Active Sidebar Link on Scroll (Task 2) ---
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.querySelector('main');

    if (sidebar && mainContent) {
        const sections = mainContent.querySelectorAll('section');
        const navLinks = sidebar.querySelectorAll('li a');

        const onScroll = () => {
            let current = '';
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                if (pageYOffset >= (sectionTop - 80)) { // 80px offset
                    current = section.getAttribute('id');
                }
            });

            navLinks.forEach(a => {
                a.classList.remove('active');
                // Check if the link's hash matches the current section's ID
                if (a.getAttribute('href').includes(`#${current}`)) {
                    a.classList.add('active');
                }
            });
        };

        // Only attach scroll listener if there are sections to track
        if (sections.length > 0) {
            window.addEventListener('scroll', onScroll);
            // Call once on load
            onScroll();
        } else {
            // Otherwise, just activate the first link if it's the main page
            const firstLink = sidebar.querySelector('li:first-child a');
            if (firstLink && !window.location.href.includes('.html#')) {
                navLinks.forEach(a => a.classList.remove('active'));
                if (firstLink) firstLink.classList.add('active');
            }
        }
    }
});