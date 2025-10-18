document.addEventListener('DOMContentLoaded', () => {

    // --- Theme Toggler ---
    const toggleButton = document.getElementById('theme-toggle-btn');
    if (toggleButton) {
        const lightModeIcon = '🌙'; // Icon shown in light mode (to switch to dark)
        const darkModeIcon = '☀️'; // Icon shown in dark mode (to switch to light)

        // Read theme from local storage or default to light
        const currentTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', currentTheme);

        // --- UPDATED: Set initial button ICON based on theme ---
        toggleButton.textContent = currentTheme === 'dark' ? darkModeIcon : lightModeIcon;

        // Add click listener to toggle theme
        toggleButton.addEventListener('click', () => {
            let theme = document.documentElement.getAttribute('data-theme');
            if (theme === 'light') {
                theme = 'dark';
                toggleButton.textContent = darkModeIcon; // Show sun icon
            } else {
                theme = 'light';
                toggleButton.textContent = lightModeIcon; // Show moon icon
            }
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('theme', theme);
        });
    }

    // --- Active Sidebar Link on Scroll ---
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.querySelector('main');

    if (sidebar && mainContent) {
        const sections = mainContent.querySelectorAll('section');
        const navLinks = sidebar.querySelectorAll('li a');
        const isSubPage = window.location.pathname.includes('query-builder-guide');

        const onScroll = () => {
            let currentSectionId = '';
            // Determine the current section based on scroll position
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                if (window.pageYOffset >= (sectionTop - 80)) { // 80px offset
                    currentSectionId = section.getAttribute('id');
                }
            });

            // Update active class on sidebar links
            navLinks.forEach(link => {
                link.classList.remove('active');
                // Check if the link's hash corresponds to the current section
                const linkHash = link.hash; // Gets the part starting with #
                if (linkHash && linkHash === `#${currentSectionId}`) {
                    link.classList.add('active');
                }
            });

            // Ensure the main link is active if no section is matched (top of page)
            // Or if it's a sub-page without sections scrolled yet
            if (!currentSectionId && !isSubPage) {
                const firstLink = sidebar.querySelector('li a[href*="#intro"]'); // More specific selector
                if(firstLink) firstLink.classList.add('active');
            } else if (!currentSectionId && isSubPage) {
                const firstLink = sidebar.querySelector('li a[href*="#guide-intro"]'); // More specific selector
                if(firstLink) firstLink.classList.add('active');
            }
        };

        // Attach scroll listener only if sections exist
        if (sections.length > 0) {
            window.addEventListener('scroll', onScroll);
            // Run once on load to set the initial active link
            onScroll();
        } else {
            // Fallback for pages without sections
            const firstLinkSelector = isSubPage ? 'li a[href*="#guide-intro"]' : 'li a[href*="#intro"]';
            const firstLink = sidebar.querySelector(firstLinkSelector);
            if (firstLink) {
                navLinks.forEach(a => a.classList.remove('active'));
                firstLink.classList.add('active');
            }
        }
    }
});