<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>توثيق إطار عمل phpLiteCore</title>
    <style>
        :root {
            --color-bg: #ffffff;
            --color-text: #222;
            --color-heading: #004080;
            --color-link: #0066cc;
            --color-border: #e0e0e0;
            --color-code-bg: #f9f9f9;
            --color-code-text: #333;
            --color-nav-bg: #f8f8f8;
            --color-nav-text: #333;
            --color-nav-active: #004080;
            --color-nav-active-text: #ffffff;
            --shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        html[data-theme='dark'] {
            --color-bg: #1a1a1a;
            --color-text: #e0e0e0;
            --color-heading: #80c0ff;
            --color-link: #58a6ff;
            --color-border: #333;
            --color-code-bg: #2a2a2a;
            --color-code-text: #e0e0e0;
            --color-nav-bg: #222;
            --color-nav-text: #e0e0e0;
            --color-nav-active: #80c0ff;
            --color-nav-active-text: #1a1a1a;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            margin: 0;
            padding: 0;
            line-height: 1.7;
            background-color: var(--color-bg);
            color: var(--color-text);
            display: flex;
        }

        #sidebar {
            width: 280px;
            height: 100vh;
            position: fixed;
            top: 0;
            right: 0;
            background-color: var(--color-nav-bg);
            border-left: 1px solid var(--color-border);
            padding: 20px;
            overflow-y: auto;
            box-shadow: var(--shadow);
        }

        #sidebar h2 {
            font-size: 1.5rem;
            color: var(--color-heading);
            border-bottom: 2px solid var(--color-border);
            padding-bottom: 10px;
        }

        #sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        #sidebar ul ul {
            padding-right: 20px;
        }

        #sidebar li a {
            display: block;
            padding: 10px 15px;
            text-decoration: none;
            color: var(--color-nav-text);
            border-radius: 5px;
            transition: all 0.2s ease;
        }

        #sidebar li a:hover {
            background-color: var(--color-border);
        }

        #sidebar li a.active {
            background-color: var(--color-nav-active);
            color: var(--color-nav-active-text);
            font-weight: bold;
        }

        main {
            margin-right: 320px; /* sidebar width + padding */
            padding: 40px;
            max-width: 900px;
            width: 100%;
        }

        section {
            margin-bottom: 60px;
            border-bottom: 1px solid var(--color-border);
            padding-bottom: 30px;
        }

        h1, h2, h3 {
            color: var(--color-heading);
            border-bottom: 1px solid var(--color-border);
            padding-bottom: 10px;
        }

        h1 { font-size: 2.5rem; }
        h2 { font-size: 2rem; }
        h3 { font-size: 1.5rem; border-bottom: none; }

        pre {
            background-color: var(--color-code-bg);
            color: var(--color-code-text);
            padding: 20px;
            border-radius: 8px;
            border: 1px solid var(--color-border);
            overflow-x: auto;
            font-family: "Courier New", Courier, monospace;
            font-size: 0.95rem;
            direction: ltr;
            text-align: left;
        }

        code {
            background-color: var(--color-code-bg);
            padding: 3px 6px;
            border-radius: 5px;
            border: 1px solid var(--color-border);
            font-family: "Courier New", Courier, monospace;
        }

        a {
            color: var(--color-link);
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin: 20px 0;
            font-size: 0.9rem;
        }
        .alert-warning {
            background-color: #fffaf0;
            border: 1px solid #ffde7a;
            color: #7a5c00;
        }
        html[data-theme='dark'] .alert-warning {
            background-color: #3d2f00;
            border-color: #a8934f;
            color: #fff5cc;
        }

        .theme-toggle {
            position: absolute;
            top: 20px;
            left: 20px;
            padding: 8px 12px;
            background-color: var(--color-code-bg);
            color: var(--color-text);
            border: 1px solid var(--color-border);
            border-radius: 5px;
            cursor: pointer;
            z-index: 100;
        }

    </style>
</head>
<body>

<button class="theme-toggle" id="theme-toggle-btn">تبديل الوضع</button>

<nav id="sidebar">
    <h2>توثيق phpLiteCore</h2>
    <ul>
        <li><a href="#intro">المقدمة والفلسفة</a></li>
        <li><a href="#structure">الهيكل الأساسي</a></li>
        <li><a href="#routing">نظام التوجيه (Routing)</a></li>
        <li><a href="#controllers">المتحكمات (Controllers)</a></li>
        <li>
            <a href="#models">النماذج وقاعدة البيانات</a>
            <ul>
                <li><a href="#models-query">1. الاستعلام (Querying)</a></li>
                <li><a href="#models-manipulate">2. المعالجة (Manipulation)</a></li>
            </ul>
        </li>
        <li><a href="#views">ملفات العرض والقوالب (Views)</a></li>
        <li><a href="#translation">نظام الترجمة (i18n)</a></li>
        <li><a href="#validation">التحقق من المدخلات (Validation)</a></li>
        <li><a href="#errors">معالجة الأخطاء</a></li>
        <li><a href="#assets">إدارة الأصول (Assets)</a></li>
    </ul>
</nav>

<main>

    <section id="intro">
        <h1>المقدمة والفلسفة</h1>
        <p>
            <code>phpLiteCore</code> هو إطار عمل PHP حديث، خفيف الوزن، وسريع لبناء تطبيقات الويب من أي حجم.
        </p>
        <p>
            يعتمد الإطار على فلسفة تصميم واضحة كما هو محدد في دستوره (القسم 2):
        </p>
        <ul>
            <li><strong>المتحكم الأمامي (Front Controller):</strong> جميع الطلبات تمر عبر ملف <code>index.php</code> واحد، ويقوم <code>.htaccess</code> بتوجيه كل شيء إليه.</li>
            <li><strong>الفصل الصارم للمسؤوليات (MVC):</strong> يتم فصل المنطق عن العرض بشكل كامل. المتحكمات (Controllers) تحضر البيانات وتمررها، وملفات العرض (Views) تعرضها فقط.</li>
            <li><strong>نمط Active Record الهجين:</strong> طريقة قوية للتعامل مع قاعدة البيانات تجمع بين بساطة Active Record وقوة منشئ الاستعلامات (Query Builder).</li>
        </ul>
    </section>

    <section id="structure">
        <h1>الهيكل الأساسي</h1>
        <p>يعتمد الإطار على هيكل ملفات واضح لفصل المسؤوليات (دستور القسم 3 و 4):</p>
        <ul>
            <li><code>/app</code>: يحتوي على كود التطبيق الخاص بك (<code>Controllers</code> و <code>Models</code>).</li>
            <li><code>/src</code>: يحتوي على نواة (Core) إطار العمل (<code>Database</code>, <code>Routing</code>, <code>Lang</code>, etc.).</li>
            <li><code>/resources</code>: ملفات المصدر قبل البناء (<code>js</code>, <code>scss</code>, <code>lang</code>).</li>
            <li><code>/public</code>: المجلد الوحيد المتاح للويب. يحتوي فقط على الأصول (Assets) المبنية.</li>
            <li><code>/views</code>: جميع ملفات قوالب العرض (<code>layouts</code>, <code>partials</code>, <code>themes</code>).</li>
            <li><code>/routes</code>: ملف <code>web.php</code> لتعريف المسارات.</li>
        </ul>
    </section>

    <section id="routing">
        <h1>نظام التوجيه (Routing)</h1>
        <p>
            يتم تعريف جميع مسارات الويب في ملف <code>routes/web.php</code>. يقوم هذا الملف بتسجيل المسارات (Routes) إلى كائن <code>$router</code>.
        </p>
        <p>
            [cite_start]يقوم <code>Router.php</code> [cite: 2] بمطابقة الرابط الحالي مع المسارات المسجلة وتشغيل المتحكم المناسب.
        </p>

        <h3>أمثلة على المسارات</h3>

        <p><strong>مسار GET بسيط:</strong></p>
        <pre><code>// routes/web.php

// يربط الرابط '/' (الجذر) بالدالة 'index' في 'HomeController'
$router->get('/', ['HomeController', 'index']);

// يربط الرابط '/about' بالدالة 'index' في 'AboutController'
$router->get('/about', ['AboutController', 'index']);</code></pre>

        <p><strong>مسار POST:</strong></p>
        <pre><code>// routes/web.php

// يربط طلب POST إلى '/posts' بالدالة 'store' في 'PostController'
$router->post('/posts', ['PostController', 'store']);</code></pre>

        <p><strong>المسارات الديناميكية (Dynamic Routes):</strong></p>
        <pre><code>// routes/web.php

// يربط أي رابط مثل '/posts/1' أو '/posts/101'
// بالدالة 'show' في 'PostController'
// ويمرر '101' كبارامتر $id
$router->get('/posts/{id}', ['PostController', 'show']);</code></pre>

        <div class="alert alert-warning">
            <strong>هام جداً (ترتيب المسارات):</strong> يجب دائماً تعريف المسارات الثابتة (مثل <code>/posts/create</code>) <strong>قبل</strong> المسارات الديناميكية (مثل <code>/posts/{id}</code>).
            <br>
            إذا تم تعريف <code>/posts/{id}</code> أولاً، سيتعامل الموجّه مع <code>/posts/create</code> على أنه طلب لـ ID بقيمة "create"، مما يسبب خطأ فادحاً (كما تم إصلاحه).
        </div>

        <pre><code>// routes/web.php (المثال الصحيح)

// (صحيح) المسار الثابت أولاً
$router->get('/posts/create', ['PostController', 'create']);

// (صحيح) المسار الديناميكي ثانياً
$router->get('/posts/{id}', ['PostController', 'show']);</code></pre>
    </section>

    <section id="controllers">
        <h1>المتحكمات (Controllers)</h1>
        <p>
            المتحكمات موجودة في <code>app/Controllers</code> وهي مسؤولة عن منطق الطلب (دستور القسم 2 - MVC).
        </p>
        <p>
            يجب أن ترث جميع المتحكمات من <code>BaseController</code>. هذا يمنحها الوصول إلى كائن التطبيق (<code>$this->app</code>) (الذي يوفر خدمات مثل <code>$this->app->translator</code>) ودالة عرض القوالب <code>$this->view()</code>.
        </p>

        <h3>مثال كامل: <code>HomeController.php</code></h3>
        <p>
            هذا مثال مثالي يوضح "الفصل الصارم للمسؤوليات" (دستور القسم 2):
        </p>
        <ol>
            <li>يجلب البيانات (<code>User::find(1)</code>).</li>
            <li>يحضر <strong>جميع</strong> النصوص المترجمة باستخدام المترجم (<code>$translator->get(...)</code>).</li>
            <li>يمرر البيانات والنصوص المترجمة إلى ملف العرض (<code>$this->view(...)</code>).</li>
        </ol>

        <pre><code>&lt;?php
namespace App\Controllers;

use App\Models\User;
use PhpLiteCore\View\Exceptions\ViewNotFoundException;

class HomeController extends BaseController
{
    /**
     * Show the application startup page.
     */
    public function index(): void
    {
        // 1. Business Logic
        $user = User::find(1); //

        // 2. Prepare ALL translated variables for the view
        $pageTitle = $this->app->translator->get('messages.home.page_title'); //
        $heroTitle = $this->app->translator->get('messages.home.hero_title');
        $heroSubtitle = $this->app->translator->get(
            'messages.home.hero_subtitle',
            ['name' => $user->name ?? $this->app->translator->get('messages.guest')]
        );
        $versionLabel = $this->app->translator->get('messages.home.version_label'); //

        // ... (other variables) ...

        // 3. Render the view, passing all final translated strings.
        $this->view('home', compact(
            'pageTitle',
            'heroTitle',
            'heroSubtitle',
            'versionLabel'
            // ... (other variables) ...
        )); //
    }
}</code></pre>
    </section>

    <section id="models">
        <h1>النماذج وقاعدة البيانات</h1>
        <p>
            يعتمد الإطار على نمط **"Active Record الهجين"** (دستور القسم 2). هذا يعني:
        </p>
        <ul>
            <li><strong>الاستعلام (Querying):</strong> يتم عبر دوال ثابتة (Static) مثل <code>User::find()</code>.</li>
            <li><strong>المعالجة (Manipulation):</strong> تتم عبر دوال الكائن (Instance) مثل <code>$user->save()</code>.</li>
        </ul>
        <p>
            يتم تحقيق ذلك بجعل جميع النماذج (مثل <code>User.php</code> و <code>Post.php</code>) ترث من <code>src/Database/Model/BaseModel.php</code>.
        </p>

        <h3 id="models-query">1. الاستعلام (Querying)</h3>
        <p>تستخدم دوال ثابتة (Static) لبدء الاستعلام.</p>

        <pre><code>// Get all users
// (Calls User::query()->get())
$users = User::all(); //

// Find a user by ID
// (Calls User::query()->where('id', '=', 1)->first())
$user = User::find(1); //

// Find the first user by email
$user = User::where('email', '=', 'ahmed@example.com')->first(); //

// Get all posts, ordered by creation date
$posts = Post::orderBy('created_at', 'DESC')->get();

// Paginate results
// (Returns an array ['paginator' => ..., 'items' => ...])
$data = Post::orderBy('id', 'ASC')->paginate(5, 1); //
$posts = $data['items'];</code></pre>

        <div class="alert alert-warning">
            <strong>عقد البيانات (Data Contract):</strong> دوال مثل <code>get()</code> و <code>first()</code> و <code>paginate()</code> تقوم "بترطيب" (Hydrate) النتائج إلى <strong>كائنات (Objects)</strong> من نوع النموذج، وليس مصفوفات.
        </div>

        <h3 id="models-manipulate">2. المعالجة (Manipulation)</h3>
        <p>تستخدم كائن النموذج (Instance) لإنشاء السجلات أو تحديثها.</p>

        <h4>إنشاء سجل جديد</h4>
        <pre><code>// 1. Create a new Post object with data
$post = new Post([
    'title'   => 'New Post Title',
    'body'    => 'This is the post body.',
    'user_id' => 1,
]); //

// 2. Save it to the database (executes INSERT)
$post->save(); //</code></pre>

        <h4>تحديث سجل موجود</h4>
        <pre><code>// 1. Find an existing post
$post = Post::find(10);

if ($post) {
    // 2. Modify its properties
    $post->title = 'Updated Title';

    // 3. Save it to the database (executes UPDATE)
    $post->save(); //
}</code></pre>
    </section>

    <section id="views">
        <h1>ملفات العرض والقوالب (Views)</h1>
        <p>
            تتبع ملفات العرض قاعدة "الفصل الصارم" (دستور القسم 2 - MVC):
        </p>
        <ul>
            <li>مسؤولة <strong>فقط</strong> عن عرض المتغيرات التي تم تمريرها إليها.</li>
            <li>ممنوع منعاً باتاً وضع أي منطق برمجي (مثل استدعاء المترجم أو الوصول لقاعدة البيانات) داخلها.</li>
            <li>استخدم دائماً صيغة <code>&lt;?= htmlspecialchars(...) ?&gt;</code> لطباعة المتغيرات لمنع هجمات XSS.</li>
        </ul>

        <h3>آلية العمل</h3>
        <p>
            عندما تستدعي <code>$this->view('home', $data)</code> من المتحكم:
        </p>
        <ol>
            [cite_start]<li>كلاس <code>Layout.php</code> [cite: 3] [cite_start]ينشئ كائن <code>View.php</code> [cite: 4] لملف العرض (مثل <code>views/themes/default/home.php</code>).</li>
            <li>يتم عرض <code>home.php</code> وتخزين مخرجاته في متغير <code>$content</code>.</li>
            [cite_start]<li>يتم عرض القالب الرئيسي <code>views/layouts/app.php</code>[cite: 1].</li>
            <li>القالب الرئيسي يقوم بطباعة متغير <code>$content</code> بداخله.</li>
        </ol>

        <h4>مثال: القالب الرئيسي <code>views/layouts/app.php</code></h4>
        <pre><code>&lt;?php
// Include the header partial
require PHPLITECORE_ROOT . 'views' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'header.php';

// This is where the content of your pages (e.g., home.php) will be injected
echo $content;

// Include the footer partial
require PHPLITECORE_ROOT . 'views' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'footer.php';
?&gt;</code></pre>

        <h4>مثال: ملف العرض <code>views/themes/default/post.php</code></h4>
        <p>
            لاحظ كيف يستخدم <code>$post->title</code> (الوصول ككائن) لأنه يستلم كائن (Object) من <code>PostController</code> (كما هو محدد في القسم 1.6.1).
        </p>
        <pre><code>&lt;article&gt;
    &lt;h1&gt;&lt;?= htmlspecialchars($post->title) ?&gt;&lt;/h1&gt;
    &lt;p&gt;&lt;?= nl2br(htmlspecialchars($post->body)) ?&gt;&lt;/p&gt;
    &lt;hr&gt;

    &lt;!-- يستخدم المتغيرات المترجمة التي تم تمريرها من المتحكم --&gt;
    &lt;small&gt;&lt;?= htmlspecialchars($publishedOnText) ?&gt; &lt;?= date('Y-m-d', strtotime($post->created_at)) ?&gt;&lt;/small&gt;

&lt;/article&gt;
&lt;a href="/posts"&gt;&lt;?= htmlspecialchars($backLinkText) ?&gt;&lt;/a&gt;</code></pre>
    </section>

    <section id="translation">
        <h1>نظام الترجمة (i18n)</h1>
        <p>
            الترجمة **إلزامية** لجميع النصوص الموجهة للمستخدم (دستور القسم 1.5).
        </p>
        <p>
            يستخدم الإطار نظاماً مجزأ (Modular) يعتمد على "التحميل الكسول" (Lazy Loading).
        </p>
        <ul>
            <li><strong>المسار:</strong> <code>resources/lang/{locale}/{filename}.php</code></li>
            [cite_start]<li><strong>أمثلة:</strong> <code>/lang/en/messages.php</code> [cite: 1][cite_start], <code>/lang/ar/validation.php</code>[cite: 3].</li>
        </ul>

        <h3>آلية "Dot Notation"</h3>
        <p>
            للوصول إلى نص مترجم، نستخدم <code>$translator->get('filename.key')</code>.
        </p>

        <h4>مثال 1: مفتاح بسيط</h4>
        <p>للوصول للمفتاح <code>framework_running</code> داخل <code>resources/lang/en/messages.php</code>:</p>
        <pre><code>$text = $this->app->translator->get('messages.framework_running');
// $text = "phpLiteCore is up and running."</code></pre>

        <h4>مثال 2: مفتاح متداخل (Nested)</h4>
        <p>للوصول للمفتاح <code>page_title</code> المتداخل داخل <code>home</code> في <code>messages.php</code>:</p>
        <pre><code>$title = $this->app->translator->get('messages.home.page_title');
// $title = "Welcome to phpLiteCore"</code></pre>

        <h4>مثال 3: التحميل الكسول (Lazy Loading)</h4>
        <p>
            عند طلب مفتاح من ملف لم يتم تحميله بعد، يقوم المترجم بتحميله تلقائياً.
        </p>
        <p>للوصول للمفتاح <code>required</code> داخل <code>resources/lang/en/validation.php</code>:</p>
        <pre><code>// المترجم سيقوم تلقائياً بتحميل "validation.php"
$error = $this->app->translator->get('validation.required');
// $error = "The {{field}} field is required."</code></pre>

        <h4>مثال 4: تمرير متغيرات (Placeholders)</h4>
        <p>
            يتم تمرير مصفوفة كبارامتر ثانٍ لاستبدال المتغيرات (المحاطة بـ <code>{{...}}</code>).
        </p>
        <pre><code>// المفتاح: 'messages.posts.not_found' => 'Post with ID {{id}} not found.'
$id = urldecode('سسسس'); //

$message = $this->app->translator->get(
    'messages.posts.not_found',
    ['id' => $id]
); //

// $message = "المقالة ذات المعرف سسسس غير موجودة."</code></pre>
    </section>

    <section id="validation">
        <h1>التحقق من المدخلات (Validation)</h1>
        <p>
            يوفر الإطار كلاس <code>Validator</code> بسيط (موجود في <code>src/Validation/Validator.php</code>).
        </p>
        <p>
            بفضل الإصلاحات، أصبح <code>Validator</code> الآن متصلاً بخدمة الترجمة ويعيد رسائل خطأ مترجمة من <code>validation.php</code> تلقائياً (دستور القسم 1.6.2).
        </p>

        <h3>مثال: من <code>PostController</code></h3>
        <pre><code>// داخل دالة store() في PostController

try {
    // 1. Define the validation rules.
    $rules = [
        'title' => 'required|min:5',
        'body'  => 'required|min:10',
    ];

    // 2. Run the validator.
    $validatedData = Validator::validate($_POST, $rules); //

    // 3. (Success) Create the post...
    $post = new Post($validatedData);
    $post->save();
    Response::redirect('/posts');

} catch (ValidationException $e) {
    // 4. (Failure)
    // $e->getErrors() ستحتوي على الأخطاء المترجمة
    // مثل: ['title' => ['The title field is required.']]
    http_response_code(422);
    echo json_encode(['errors' => $e->getErrors()]);
}</code></pre>
    </section>

    <section id="errors">
        <h1>معالجة الأخطاء</h1>
        <p>
            يتم التحكم في معالجة الأخطاء بواسطة <code>src/Bootstrap/ErrorHandler.php</code> (دستور القسم 2).
        </p>

        <h3>بيئة التطوير (<code>ENV=development</code>)</h3>
        <p>
            يعرض صفحة خطأ مفصلة (Stack Trace) باستخدام <code>views/system/error.php</code>. هذا مفيد لتصحيح الأخطاء.
        </p>

        <h3>بيئة الإنتاج (<code>ENV=production</code>)</h3>
        <p>
            يقوم بثلاثة أشياء:
        </p>
        <ol>
            <li>يسجل الخطأ المفصل في <code>storage/logs/php-error.log</code>.</li>
            <li>يرسل بريداً إلكترونياً (SMTP) مفصلاً بالخطأ إلى المطور (<code>DEVELOPER_EMAIL</code> في <code>.env</code>).</li>
            <li>يعرض صفحة خطأ عامة ومترجمة للمستخدم (<code>views/system/http_error.php</code>).</li>
        </ol>

        <h3>أخطاء 404 (غير موجود)</h3>
        <p>
            يمكنك إطلاق صفحة 404 مترجمة من أي متحكم باستخدام <code>Response::notFound()</code>.
        </p>

        <pre><code>// استخدام رسالة 404 الافتراضية المترجمة
Response::notFound();

// استخدام رسالة مخصصة (يجب أن تكون مترجمة مسبقاً)
$message = $this->app->translator->get('messages.posts.not_found', ['id' => $id]);
Response::notFound($message); //</code></pre>
    </section>

    <section id="assets">
        <h1>إدارة الأصول (Assets)</h1>
        <p>
            يعتمد الإطار على <strong>Webpack</strong>, <strong>NPM</strong>, و <strong>SCSS</strong> (دستور القسم 2).
        </p>
        <p>
            يتم تعريف الاعتماديات (مثل Bootstrap) في <code>package.json</code>، ويتم تعريف إعدادات البناء في <code>webpack.config.js</code>.
        </p>

        <h3>تدفق العمل (Workflow)</h3>
        <ol>
            <li>تعديل ملفات المصدر: <code>resources/js/app.js</code> أو <code>resources/scss/app.scss</code>.</li>
            <li>تشغيل <code>npm run dev</code> للمراقبة والتطوير، أو <code>npm run build</code> لبناء ملفات الإنتاج.</li>
            <li>يقوم Webpack بتجميع وبناء الملفات النهائية في <code>public/assets/app.js</code> و <code>public/assets/app.css</code>.</li>
            <li>ملف القالب <code>header.php</code> يقوم باستدعاء هذه الملفات المبنية.</li>
        </ol>

        <pre><code>&lt;!-- views/partials/header.php --&gt;
&lt;!DOCTYPE html&gt;
&lt;html lang="&lt;?= LANG ?? 'en' ?&gt;" dir="&lt;?= HTML_DIR ?? 'ltr' ?&gt;"&gt;
&lt;head&gt;
    &lt;meta charset="UTF-8"&gt;
    &lt;title&gt;&lt;?= htmlspecialchars($pageTitle ?? 'phpLiteCore') ?&gt;&lt;/title&gt;

    &lt;!-- استدعاء ملف CSS المبني --&gt;
    &lt;link rel="stylesheet" href="/assets/app.css"&gt;
&lt;/head&gt;
&lt;body&gt;
...</code></pre>
        <pre><code>&lt;!-- views/partials/footer.php --&gt;
...
    &lt;!-- استدعاء ملف JS المبني --&gt;
    &lt;script src="/assets/app.js"&gt;&lt;/script&gt;
&lt;/body&gt;
&lt;/html&gt;</code></pre>
    </section>

</main>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const toggleButton = document.getElementById('theme-toggle-btn');
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

        // Active sidebar link based on scroll
        const sections = document.querySelectorAll('main section');
        const navLinks = document.querySelectorAll('#sidebar li a');

        window.addEventListener('scroll', () => {
            let current = '';
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                if (pageYOffset >= (sectionTop - 80)) { // 80px offset
                    current = section.getAttribute('id');
                }
            });

            navLinks.forEach(a => {
                a.classList.remove('active');
                if (a.getAttribute('href') === `#${current}`) {
                    a.classList.add('active');
                }
            });
        });
    });
</script>
</body>
</html>