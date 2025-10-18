<?php
return [
    // General
    'guest' => 'Guest',
    'welcome' => 'Welcome, {{name}}!',
    'home_link_text' => 'Back to Home Page',

    // Navigation (NEW)
    'nav' => [
        'home' => 'Home',
        'posts' => 'Posts',
        'create_post' => 'Create Post',
        'about' => 'About',
    ],

    // Home Page Keys Nested under 'home'
    'home' => [
        'page_title' => 'Welcome to phpLiteCore',
        'hero_title' => 'Installation Successful!',
        'hero_subtitle' => 'Welcome {{name}} to the world of phpLiteCore.',
        'hero_description' => 'A simple PHP framework for beginners, powerful for professionals. You are now ready to build fast and lightweight web applications.',
        'card_docs_title' => 'Read the Docs',
        'card_docs_text' => 'Start by reading our comprehensive documentation to understand the core concepts and features.',
        'card_docs_button' => 'Get Started',
        'card_code_title' => 'Explore the Code',
        'card_code_text' => 'The best way to learn is by doing. Start by editing routes/web.php',
        'card_code_button' => 'Open File',
        'card_community_title' => 'Join the Community',
        'card_community_text' => 'Contribute, report issues, or just say hi on our GitHub repository.',
        'card_community_button' => 'View on GitHub',
        'version_label' => 'Version:',
    ],

    // About Page Keys
    'about' => [
        'page_title' => 'About Us',
        'page_content' => 'This is the about us page, powered by phpLiteCore.',
    ],

    // Post-specific keys
    'posts' => [
        'index_title' => 'All Posts',
        'create_title' => 'Create New Post',
        'edit_title' => 'Edit Post: {{title}}', // NEW for edit page title
        'not_found' => 'Post with ID {{id}} not found.',
        'no_posts' => 'No posts found.',
        'back_link' => 'Back to Posts', // Changed from Back to Home for consistency
        'create_button' => 'Create Post',
        'update_button' => 'Update Post', // NEW
        'edit_button' => 'Edit', // NEW for link on show page
        'cancel_button' => 'Cancel',
        'form_title' => 'Post Title',
        'form_content' => 'Post Content',
        'published_on' => 'Published on:',
    ],

    // Framework Status (Top Level)
    'framework_running' => 'phpLiteCore is up and running.',

    // Error Messages (Top Level)
    'error_500_title' => 'Internal Server Error',
    'error_500_message' => 'We are sorry, but a temporary error occurred. Our team has been notified and we are working to fix the problem as soon as possible.',
    'error_404_title' => 'Not Found',
    'error_404_message' => 'The page you are looking for could not be found.',
];