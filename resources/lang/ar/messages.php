<?php

return [
    // General
    'guest' => 'زائر',
    'welcome' => 'أهلاً بك، {{name}}!',
    'home_link_text' => 'العودة للصفحة الرئيسية',

    // Home Page Keys Nested under 'home'
    'home' => [
        'page_title' => 'أهلاً بك في phpLiteCore',
        'hero_title' => 'تم التثبيت بنجاح!',
        'hero_subtitle' => 'أهلاً بك {{name}} في عالم phpLiteCore.',
        'hero_description' => 'إطار عمل PHP بسيط للمبتدئين، وقوي للمحترفين. أنت الآن جاهز لبناء تطبيقات ويب سريعة وخفيفة.',
        'card_docs_title' => 'اقرأ التوثيق',
        'card_docs_text' => 'ابدأ بقراءة التوثيق الشامل لفهم المفاهيم والميزات الأساسية.',
        'card_docs_button' => 'ابدأ الآن',
        'card_code_title' => 'استكشف الكود',
        'card_code_text' => 'أفضل طريقة للتعلم هي التطبيق. ابدأ بتعديل routes/web.php',
        'card_code_button' => 'افتح الملف',
        'card_community_title' => 'انضم للمجتمع',
        'card_community_text' => 'ساهم، أبلغ عن المشاكل، أو فقط قل مرحباً على مستودعنا في GitHub.',
        'card_community_button' => 'عرض على GitHub',
        'version_label' => 'الإصدار:',
    ],

    // About Page Keys (NEW)
    'about' => [
        'page_title' => 'من نحن',
        'page_content' => 'هذه هي صفحة "من نحن"، بدعم من phpLiteCore.',
    ],

    // Post-specific keys
    'posts' => [
        'index_title' => 'كل المقالات',
        'create_title' => 'إنشاء مقالة جديدة',
        'not_found' => 'المقالة ذات المعرف {{id}} غير موجودة.',
        'no_posts' => 'لم يتم العثور على مقالات.',
        'back_link' => 'العودة للرئيسية',
        'create_button' => 'إنشاء المقالة',
        'cancel_button' => 'إلغاء',
        'form_title' => 'عنوان المقالة',
        'form_content' => 'محتوى المقالة',
        'published_on' => 'نشر في:',
    ],

    // Framework Status (Top Level)
    'framework_running' => 'phpLiteCore يعمل الآن.',

    // Error Messages (Top Level)
    'error_500_title' => 'خطأ داخلي بالخادم',
    'error_500_message' => 'نحن نأسف، ولكن حدث خطأ مؤقت. لقد تم إخطار فريقنا ونحن نعمل على إصلاح المشكلة في أقرب وقت ممكن.',
    'error_404_title' => 'غير موجود',
    'error_404_message' => 'الصفحة التي تبحث عنها غير موجودة.',
];