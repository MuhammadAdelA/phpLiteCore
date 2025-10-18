document.addEventListener('DOMContentLoaded', () => {

    // --- Theme Toggler ---
    const toggleButton = document.getElementById('theme-toggle-btn');
    if (toggleButton) {
        // Read theme from local storage or default to light
        const currentTheme = localStorage.getItem('theme') || 'light';
        const docLang = document.documentElement.lang; // Get current page language ('ar' or 'en')
        document.documentElement.setAttribute('data-theme', currentTheme);

        // --- NEW: Set initial button text based on language and theme ---
        const setText = (theme, lang) => {
            if (lang === 'ar') {
                toggleButton.textContent = theme === 'dark' ? 'الوضع الفاتح' : 'الوضع الداكن';
            } else { // Default to English
                toggleButton.textContent = theme === 'dark' ? 'Light Mode' : 'Dark Mode';
            }
        };
        setText(currentTheme, docLang);
        // --- END NEW ---

        // Add click listener to toggle theme
        toggleButton.addEventListener('click', () => {
            let theme = document.documentElement.getAttribute('data-theme');
            if (theme === 'light') {
                theme = 'dark';
            } else {
                theme = 'light';
            }
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('theme', theme);
            // --- NEW: Update text based on new theme and language ---
            setText(theme, docLang);
            // --- END NEW ---
        });
    }

    // --- Active Sidebar Link on Scroll ---
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.querySelector('main');

    if (sidebar && mainContent) {
        const sections = mainContent.querySelectorAll('section');
        const navLinks = sidebar.querySelectorAll('li a');
        // --- NEW: Identify if it's a sub-page (like query builder guide) ---
        const isSubPage = window.location.pathname.includes('query-builder-guide');
        // --- END NEW ---

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
                // Make sure to compare only the hash part for section links
                const linkHash = link.hash; // Gets the part starting with #
                if (linkHash && linkHash === `#${currentSectionId}`) {
                    link.classList.add('active');
                }
            });

            // --- NEW: Ensure the main link is active if no section is matched (top of page) ---
            // Or if it's a sub-page without sections scrolled yet
            if (!currentSectionId && !isSubPage) {
                const firstLink = sidebar.querySelector('li a[href="#intro"]');
                if(firstLink) firstLink.classList.add('active');
            } else if (!currentSectionId && isSubPage) {
                const firstLink = sidebar.querySelector('li a[href="#guide-intro"]');
                if(firstLink) firstLink.classList.add('active');
            }
            // --- END NEW ---
        };

        // Attach scroll listener only if sections exist
        if (sections.length > 0) {
            window.addEventListener('scroll', onScroll);
            // Run once on load to set the initial active link
            onScroll();
        } else {
            // Fallback for pages without sections (e.g., initial load of query builder guide)
            // --- MODIFIED: More robust check for the correct first link ---
            const firstLinkSelector = isSubPage ? 'li a[href="#guide-intro"]' : 'li a[href="#intro"]';
            const firstLink = sidebar.querySelector(firstLinkSelector);
            if (firstLink) {
                navLinks.forEach(a => a.classList.remove('active'));
                firstLink.classList.add('active');
            }
            // --- END MODIFIED ---
        }
    }
});