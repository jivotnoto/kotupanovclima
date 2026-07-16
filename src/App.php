<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/Logger.php';
require_once __DIR__ . '/DataStore.php';
require_once __DIR__ . '/Catalog.php';
require_once __DIR__ . '/Auth.php';
require_once __DIR__ . '/View.php';

final class App
{
    private Logger $logger;
    private DataStore $dataStore;
    private Catalog $catalog;
    private Auth $auth;
    private View $view;
    private string $requestId;
    private float $startedAt;
    private string $basePath;
    private ?array $turnstileConfiguration = null;

    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, DIRECTORY_SEPARATOR);
        $this->logger = new Logger($this->basePath . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs');
        $this->dataStore = new DataStore($this->basePath . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'data');
        $this->catalog = new Catalog($this->dataStore);
        $this->auth = new Auth($this->dataStore, $this->logger);
        $this->view = new View($this->basePath . DIRECTORY_SEPARATOR . 'views');
        $this->requestId = bin2hex(random_bytes(12));
        $this->startedAt = microtime(true);
    }

    public function run(): void
    {
        $path = request_path();
        if (!in_array($path, ['/robots.txt', '/sitemap.xml'], true) && session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $this->sendSecurityHeaders($path);
        header('X-Request-Id: ' . $this->requestId);
        register_shutdown_function(fn () => $this->logAccess());

        if ($this->isSuspiciousPath($path)) {
            $this->logger->security('suspicious_request_path', $this->requestContext([
                'path' => $path,
            ]), 'warn');
        }

        try {
            $this->dispatch($path, $_SERVER['REQUEST_METHOD'] ?? 'GET');
        } catch (Throwable $exception) {
            http_response_code(500);
            $this->logger->application('uncaught_request_exception', $this->requestContext([
                'path' => $path,
                'exception' => $exception,
            ]), 'critical');

            echo $this->view->render('public/error', [
                'pageTitle' => 'Грешка в приложението',
                'company' => $this->catalog->getCompany(),
                'currentPath' => $path,
                'statusCode' => 500,
                'message' => 'Възникна неочаквана грешка. Провери application.log за повече информация.',
            ]);
        }
    }

    private function dispatch(string $path, string $method): void
    {
        $method = strtoupper($method);

        if ($path === '/robots.txt') {
            $this->robotsTxt();
            return;
        }

        if ($path === '/sitemap.xml') {
            $this->sitemapXml();
            return;
        }

        if ($path === '/') {
            $this->home();
            return;
        }

        if ($path === '/promocii') {
            $this->promotionsPage();
            return;
        }

        if ($path === '/kontakti' && $method === 'GET') {
            $this->contactsPage();
            return;
        }

        if ($path === '/kontakti' && $method === 'POST') {
            $this->contactSubmit();
            return;
        }

        if ($path === '/remont-i-profilaktika') {
            $this->serviceRepairPage();
            return;
        }

        if ($path === '/obshti-usloviya') {
            $this->termsPage();
            return;
        }

        if ($path === '/politika-za-poveritelnost') {
            $this->privacyPage();
            return;
        }

        if ($path === '/produkti/klimatici') {
            $this->catalogPage('airConditioners');
            return;
        }

        if ($path === '/produkti/termopompi') {
            $this->catalogPage('heatPumps');
            return;
        }

        if (preg_match('#^/produkti/(klimatici|termopompi)/([^/]+)$#', $path, $matches) === 1) {
            $this->productDetail($matches[1] === 'termopompi' ? 'heatPumps' : 'airConditioners', $matches[2]);
            return;
        }

        if ($path === '/admin/login' && $method === 'GET') {
            $this->adminLoginPage();
            return;
        }

        if ($path === '/admin/login' && $method === 'POST') {
            $this->adminLoginSubmit();
            return;
        }

        if ($path === '/admin/logout' && $method === 'POST') {
            $this->adminLogout();
            return;
        }

        if ($path === '/admin') {
            $this->auth->requireAdmin();
            $this->adminDashboard();
            return;
        }

        if ($path === '/admin/products') {
            $this->auth->requireAdmin();
            $this->adminProductsList();
            return;
        }

        if ($path === '/admin/products/new') {
            $this->auth->requireAdmin();
            $this->adminProductForm(null);
            return;
        }

        if ($path === '/admin/products/edit') {
            $this->auth->requireAdmin();
            $this->adminProductForm((string) ($_GET['slug'] ?? ''));
            return;
        }

        if ($path === '/admin/products/save' && $method === 'POST') {
            $this->auth->requireAdmin();
            $this->adminProductSave();
            return;
        }

        if ($path === '/admin/products/delete' && $method === 'POST') {
            $this->auth->requireAdmin();
            $this->adminProductDelete();
            return;
        }

        if ($path === '/admin/products/reorder' && $method === 'POST') {
            $this->auth->requireAdmin();
            $this->adminProductReorder();
            return;
        }

        if ($path === '/admin/promotions') {
            $this->auth->requireAdmin();
            $this->adminPromotionsList();
            return;
        }

        if ($path === '/admin/promotions/new') {
            $this->auth->requireAdmin();
            $this->adminPromotionForm(null);
            return;
        }

        if ($path === '/admin/promotions/edit') {
            $this->auth->requireAdmin();
            $this->adminPromotionForm((string) ($_GET['id'] ?? ''));
            return;
        }

        if ($path === '/admin/promotions/save' && $method === 'POST') {
            $this->auth->requireAdmin();
            $this->adminPromotionSave();
            return;
        }

        if ($path === '/admin/promotions/delete' && $method === 'POST') {
            $this->auth->requireAdmin();
            $this->adminPromotionDelete();
            return;
        }

        if ($path === '/admin/settings') {
            $this->auth->requireAdmin();
            $this->adminSettingsPage();
            return;
        }

        if ($path === '/admin/settings/save' && $method === 'POST') {
            $this->auth->requireAdmin();
            $this->adminSettingsSave();
            return;
        }

        http_response_code(404);
        echo $this->view->render('public/error', [
            'pageTitle' => 'Страницата не е намерена',
            'company' => $this->catalog->getCompany(),
            'currentPath' => $path,
            'statusCode' => 404,
            'message' => 'Търсената страница не беше открита.',
        ]);
    }

    private function home(): void
    {
        $company = $this->catalog->getCompany();
        $faq = $this->faqEntries();

        echo $this->view->render('public/home', [
            'pageTitle' => 'Котупановклима ЕООД',
            'metaTitle' => 'Котупановклима ЕООД | Климатици и термопомпи в Перник',
            'metaDescription' => 'Продажба, монтаж, ремонт и профилактика на климатици и термопомпи в Перник и региона. Официални марки, гаранция и професионална консултация. Обади се!',
            'metaKeywords' => 'климатици Перник, термопомпи Перник, монтаж на климатици Перник, ремонт на климатици Перник, инверторни климатици, термопомпа въздух-вода, климатици цени Перник, Котупановклима',
            'ogImage' => '/images/site-og-image.png',
            'company' => $company,
            'settings' => $this->catalog->getSettings(),
            'promotions' => array_slice($this->catalog->getPromotions(true), 0, 4),
            'brandShowcase' => $this->catalog->getBrandShowcase(),
            'faq' => $faq,
            'currentPath' => '/',
            'jsonLd' => [
                $this->businessSchema($company, true),
                $this->faqSchema($faq),
            ],
        ]);
    }

    private function promotionsPage(): void
    {
        echo $this->view->render('public/promotions', [
            'pageTitle' => 'Промоции',
            'metaTitle' => 'Промоции за климатици и термопомпи в Перник | Котупановклима',
            'metaDescription' => 'Актуални промоции за климатици и термопомпи с цени в евро, монтаж и консултация в Перник и региона.',
            'metaKeywords' => 'промоции климатици Перник, климатици на промоция, промоции термопомпи, климатици цени Перник, оферти климатици',
            'company' => $this->catalog->getCompany(),
            'promotions' => $this->catalog->getPromotions(true),
            'currentPath' => '/promocii',
            'jsonLd' => [
                $this->breadcrumbSchema([
                    ['name' => 'Начало', 'path' => '/'],
                    ['name' => 'Промоции', 'path' => '/promocii'],
                ]),
            ],
        ]);
    }

    private function contactsPage(): void
    {
        $company = $this->catalog->getCompany();
        $topics = $this->contactTopics();
        $requestedTopic = trim((string) ($_GET['topic'] ?? 'general'));
        $selectedTopic = array_key_exists($requestedTopic, $topics) ? $requestedTopic : 'general';
        $turnstile = $this->turnstileConfig();
        $mathCaptcha = $turnstile['enabled'] ? null : $this->contactMathCaptcha();

        echo $this->view->render('public/contacts', [
            'pageTitle' => 'Контакти',
            'metaTitle' => 'Контакти | Котупановклима ЕООД',
            'metaDescription' => 'Свържи се с Котупановклима ЕООД за оферта, монтаж, ремонт или профилактика на климатична техника в Перник и региона.',
            'metaKeywords' => 'контакти Котупановклима, климатици Перник контакти, оферта климатик Перник, телефон климатици Перник',
            'company' => $company,
            'currentPath' => '/kontakti',
            'flash' => flash_get('contact'),
            'csrfToken' => $this->auth->csrfToken(),
            'contactTopics' => $topics,
            'selectedTopic' => $selectedTopic,
            'turnstileSiteKey' => $turnstile['enabled'] ? $turnstile['siteKey'] : null,
            'captchaQuestion' => $mathCaptcha['question'] ?? null,
            'captchaId' => $mathCaptcha['id'] ?? null,
            'jsonLd' => [
                $this->businessSchema($company, true),
                $this->breadcrumbSchema([
                    ['name' => 'Начало', 'path' => '/'],
                    ['name' => 'Контакти', 'path' => '/kontakti'],
                ]),
            ],
        ]);
    }

    private function contactSubmit(): void
    {
        if (!$this->auth->verifyCsrf($_POST['_csrf'] ?? null)) {
            $this->logger->security('contact_form_csrf_failed', $this->requestContext(), 'warn');
            flash_set('contact', 'Формата не е валидна. Опитай отново.', 'error');
            redirect_to('/kontakti');
        }

        if (trim((string) ($_POST['website'] ?? '')) !== '') {
            $this->logger->security('contact_form_honeypot_triggered', $this->requestContext(), 'warn');
            flash_set('contact', 'Благодарим, запитването е прието.', 'success');
            redirect_to('/kontakti');
        }

        $lastSentAt = isset($_SESSION['contact_last_sent_at']) ? (int) $_SESSION['contact_last_sent_at'] : 0;
        if ($lastSentAt > 0 && time() - $lastSentAt < 45) {
            flash_set('contact', 'Изчакай малко преди да изпратиш ново запитване.', 'error');
            redirect_to('/kontakti');
        }

        $name = $this->limitText($_POST['name'] ?? '', 100);
        $email = $this->limitText($_POST['email'] ?? '', 160);
        $phone = $this->limitText($_POST['phone'] ?? '', 70);
        $message = $this->limitText($_POST['message'] ?? '', 2200);
        $topicKey = trim((string) ($_POST['topic'] ?? 'general'));
        $topics = $this->contactTopics();
        $topic = $topics[$topicKey] ?? $topics['general'];
        $consent = isset($_POST['privacyConsent']);

        if ($name === '' || $message === '' || mb_strlen($message, 'UTF-8') < 10) {
            flash_set('contact', 'Попълни име и малко повече информация за запитването.', 'error');
            redirect_to('/kontakti');
        }

        if ($email === '' && $phone === '') {
            flash_set('contact', 'Остави телефон или имейл, за да можем да върнем отговор.', 'error');
            redirect_to('/kontakti');
        }

        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            flash_set('contact', 'Имейл адресът не изглежда валиден.', 'error');
            redirect_to('/kontakti');
        }

        if (!$consent) {
            flash_set('contact', 'Потвърди, че си съгласен да обработим данните за отговор на запитването.', 'error');
            redirect_to('/kontakti');
        }

        $turnstile = $this->turnstileConfig();
        if ($turnstile['enabled']) {
            $token = trim((string) ($_POST['cf-turnstile-response'] ?? ''));
            $verification = $this->verifyTurnstileToken($token, $turnstile);
            if (!$verification['success']) {
                $this->logger->security('contact_form_turnstile_failed', $this->requestContext([
                    'errorCodes' => $verification['errorCodes'],
                    'hostname' => $verification['hostname'],
                ]), 'warn');
                flash_set('contact', 'Потвърди, че не си робот, и опитай отново.', 'error');
                redirect_to('/kontakti');
            }
        } elseif (!$this->verifyContactMathCaptcha(
            (string) ($_POST['captcha_id'] ?? ''),
            (string) ($_POST['captcha_answer'] ?? '')
        )) {
            $this->logger->security('contact_form_captcha_failed', $this->requestContext(), 'warn');
            flash_set('contact', 'Отговорът на проверката не е правилен. Опитай отново.', 'error');
            redirect_to('/kontakti');
        }

        $company = $this->catalog->getCompany();
        $recipient = $this->contactRecipient($company);
        if ($recipient === null) {
            $this->logger->application('contact_form_recipient_missing', $this->requestContext(), 'error');
            flash_set('contact', 'Формата още няма настроен получател. Моля, използвай телефона за контакт.', 'error');
            redirect_to('/kontakti');
        }

        $subject = $this->encodedMailSubject('Запитване от сайта: ' . $topic);
        $host = parse_url($this->currentSeoBaseUrl(), PHP_URL_HOST) ?: 'kotupanovclima.eu';
        $body = implode("\n", [
            'Ново запитване от kotupanovclima.eu',
            '',
            'Тема: ' . $topic,
            'Име: ' . $name,
            'Имейл: ' . ($email !== '' ? $email : 'не е оставен'),
            'Телефон: ' . ($phone !== '' ? $phone : 'не е оставен'),
            '',
            'Съобщение:',
            $message,
            '',
            'IP: ' . (detect_client_ip() ?? 'unknown'),
            'Дата: ' . date('Y-m-d H:i:s'),
        ]);
        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'From: Kotupanovclima Website <no-reply@' . $host . '>',
        ];

        if ($email !== '') {
            $headers[] = 'Reply-To: ' . $email;
        }

        $sent = @mail($recipient, $subject, $body, implode("\r\n", $headers));
        if (!$sent) {
            $this->logger->application('contact_form_mail_failed', $this->requestContext([
                'recipientDomain' => substr(strrchr($recipient, '@') ?: '', 1),
            ]), 'error');
            flash_set('contact', 'Не успяхме да изпратим формата. Моля, обади се директно по телефона.', 'error');
            redirect_to('/kontakti');
        }

        $_SESSION['contact_last_sent_at'] = time();
        $this->logger->application('contact_form_sent', $this->requestContext([
            'topic' => $topicKey,
        ]));
        flash_set('contact', 'Благодарим, запитването е изпратено успешно.', 'success');
        redirect_to('/kontakti');
    }

    private function serviceRepairPage(): void
    {
        $company = $this->catalog->getCompany();
        $faq = $this->faqEntries();

        echo $this->view->render('public/repair-service', [
            'pageTitle' => 'Ремонт и профилактика',
            'metaTitle' => 'Ремонт и профилактика на климатици в Перник | Котупановклима',
            'metaDescription' => 'Ремонт, профилактика, почистване и диагностика на климатична техника за Перник и региона. Ясни пакети и бърза реакция.',
            'metaKeywords' => 'ремонт на климатици Перник, профилактика на климатици Перник, сервиз климатици Перник, зареждане на климатик с фреон, почистване на климатик',
            'company' => $company,
            'currentPath' => '/remont-i-profilaktika',
            'faq' => $faq,
            'jsonLd' => [
                [
                    '@context' => 'https://schema.org',
                    '@type' => 'Service',
                    'serviceType' => 'Ремонт и профилактика на климатици и термопомпи',
                    'provider' => $this->businessSchema($company),
                    'areaServed' => ['@type' => 'City', 'name' => 'Перник'],
                    'description' => 'Диагностика, ремонт, почистване и сезонна профилактика на климатична техника за Перник и региона.',
                ],
                $this->breadcrumbSchema([
                    ['name' => 'Начало', 'path' => '/'],
                    ['name' => 'Ремонт и профилактика', 'path' => '/remont-i-profilaktika'],
                ]),
                $this->faqSchema($faq),
            ],
        ]);
    }

    private function termsPage(): void
    {
        echo $this->view->render('public/terms', [
            'pageTitle' => 'Общи условия',
            'metaTitle' => 'Общи условия | Котупановклима ЕООД',
            'metaDescription' => 'Общи условия за използване на сайта kotupanovclima.eu и информация за оферти, услуги и контакт.',
            'company' => $this->catalog->getCompany(),
            'currentPath' => '/obshti-usloviya',
        ]);
    }

    private function privacyPage(): void
    {
        echo $this->view->render('public/privacy', [
            'pageTitle' => 'Политика за поверителност',
            'metaTitle' => 'Политика за поверителност | Котупановклима ЕООД',
            'metaDescription' => 'Информация за обработването на лични данни, контактната форма и използването на бисквитки в kotupanovclima.eu.',
            'company' => $this->catalog->getCompany(),
            'currentPath' => '/politika-za-poveritelnost',
        ]);
    }

    private function robotsTxt(): void
    {
        header('Content-Type: text/plain; charset=UTF-8');
        header('Cache-Control: public, max-age=3600');
        $baseUrl = $this->currentSeoBaseUrl();

        echo implode("\n", [
            'User-agent: *',
            'Disallow: /admin/',
            'Disallow: /admin',
            'Sitemap: ' . $baseUrl . '/sitemap.xml',
            '',
        ]);
    }

    private function sitemapXml(): void
    {
        $lastmod = date('Y-m-d');
        $baseUrl = $this->currentSeoBaseUrl();
        $urls = [
            ['loc' => $baseUrl . '/', 'priority' => '1.0', 'lastmod' => $lastmod],
            ['loc' => $baseUrl . '/promocii', 'priority' => '0.8', 'lastmod' => $lastmod],
            ['loc' => $baseUrl . '/remont-i-profilaktika', 'priority' => '0.8', 'lastmod' => $lastmod],
            ['loc' => $baseUrl . '/produkti/klimatici', 'priority' => '0.8', 'lastmod' => $lastmod],
            ['loc' => $baseUrl . '/produkti/termopompi', 'priority' => '0.8', 'lastmod' => $lastmod],
            ['loc' => $baseUrl . '/kontakti', 'priority' => '0.7', 'lastmod' => $lastmod],
            ['loc' => $baseUrl . '/obshti-usloviya', 'priority' => '0.3', 'lastmod' => $lastmod],
            ['loc' => $baseUrl . '/politika-za-poveritelnost', 'priority' => '0.3', 'lastmod' => $lastmod],
        ];

        foreach (['airConditioners' => 'klimatici', 'heatPumps' => 'termopompi'] as $category => $path) {
            foreach ($this->catalog->getProductsByCategory($category) as $product) {
                $urls[] = [
                    'loc' => $baseUrl . '/produkti/' . $path . '/' . $product['slug'],
                    'priority' => '0.6',
                    'lastmod' => $lastmod,
                ];
            }
        }

        header('Content-Type: application/xml; charset=UTF-8');
        header('Cache-Control: public, max-age=3600');
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $url) {
            echo '  <url>' . "\n";
            echo '    <loc>' . e($url['loc']) . '</loc>' . "\n";
            echo '    <lastmod>' . e($url['lastmod']) . '</lastmod>' . "\n";
            echo '    <changefreq>weekly</changefreq>' . "\n";
            echo '    <priority>' . e($url['priority']) . '</priority>' . "\n";
            echo '  </url>' . "\n";
        }

        echo '</urlset>' . "\n";
    }

    private function currentSeoBaseUrl(): string
    {
        return 'https://kotupanovclima.eu';
    }

    private function catalogPage(string $category): void
    {
        $path = $category === 'heatPumps' ? '/produkti/termopompi' : '/produkti/klimatici';
        $products = $this->catalog->getProductsByCategory($category);
        $company = $this->catalog->getCompany();
        $title = $category === 'heatPumps'
            ? 'Термопомпи за отопление и охлаждане — доставка и монтаж в Перник'
            : 'Климатици за дома и офиса — над 50 модела с цени и монтаж';

        echo $this->view->render('public/catalog', [
            'pageTitle' => $category === 'heatPumps' ? 'Термопомпи' : 'Климатици',
            'metaTitle' => $category === 'heatPumps'
                ? 'Термопомпи въздух-вода в Перник | Котупановклима'
                : 'Климатици за дома и офиса в Перник | Котупановклима',
            'metaDescription' => $category === 'heatPumps'
                ? 'Каталог с подбрани термопомпи въздух-вода за Перник и региона — мощности, цени, технически параметри и монтаж.'
                : 'Каталог с подбрани климатици за Перник и региона — марки, мощности, цени, технически параметри и монтаж.',
            'metaKeywords' => $category === 'heatPumps'
                ? 'термопомпи Перник, термопомпа въздух-вода, отопление с термопомпа, монтаж на термопомпа Перник, LG Therma V, Crystal термопомпа, термопомпа цена'
                : 'климатици Перник, климатици цени Перник, инверторни климатици, монтаж на климатици Перник, климатик за апартамент, Gree, Daikin, Crystal, Fujitsu Airstage, Mitsubishi Heavy',
            'company' => $company,
            'currentPath' => $path,
            'products' => $products,
            'category' => $category,
            'title' => $title,
            'description' => $category === 'heatPumps'
                ? 'Структурата е подготвена за по-ясно сравнение между серии, мощности и типове системи.'
                : 'Намери точния модел за секунди: Селекция, филтрирана по мощност, технология и бюджет.',
            'officialLinks' => $this->catalog->getOfficialBrandLinks(),
            'jsonLd' => [
                $this->businessSchema($company),
                $this->breadcrumbSchema([
                    ['name' => 'Начало', 'path' => '/'],
                    ['name' => $category === 'heatPumps' ? 'Термопомпи' : 'Климатици', 'path' => $path],
                ]),
            ],
        ]);
    }

    private function productDetail(string $category, string $slug): void
    {
        $product = $this->catalog->getProductBySlug($category, $slug);
        if ($product === null) {
            http_response_code(404);
            echo $this->view->render('public/error', [
                'pageTitle' => 'Продуктът не е намерен',
                'company' => $this->catalog->getCompany(),
                'currentPath' => request_path(),
                'statusCode' => 404,
                'message' => 'Търсеният продукт липсва в каталога.',
            ]);
            return;
        }

        $company = $this->catalog->getCompany();
        $categoryLabel = $category === 'heatPumps' ? 'Термопомпи' : 'Климатици';
        $categoryPath = $category === 'heatPumps' ? '/produkti/termopompi' : '/produkti/klimatici';
        $categoryTerm = $category === 'heatPumps' ? 'термопомпа' : 'климатик';
        $categoryTermPlural = $category === 'heatPumps' ? 'термопомпи' : 'климатици';
        $productKeywords = array_values(array_unique(array_filter([
            $product['title'],
            $product['brand'] . ' ' . $categoryTerm,
            $product['brand'] . ' Перник',
            $categoryTerm . ' ' . $product['brand'] . ' цена',
            $product['series'] !== '' ? $product['brand'] . ' ' . $product['series'] : null,
            $categoryTermPlural . ' Перник',
            $categoryTerm . ' Перник цена',
        ])));

        echo $this->view->render('public/product', [
            'pageTitle' => $product['title'],
            'metaTitle' => $product['title'] . ' | Котупановклима',
            'metaDescription' => $product['description'] ?: $product['title'] . ' с цена ' . format_price_eur($product['priceEur']) . ' и технически параметри.',
            'metaKeywords' => implode(', ', $productKeywords),
            'ogType' => 'product',
            'ogImage' => $product['imagePath'] ?? null,
            'company' => $company,
            'currentPath' => request_path(),
            'product' => $product,
            'jsonLd' => [
                $this->productSchema($product, $company),
                $this->breadcrumbSchema([
                    ['name' => 'Начало', 'path' => '/'],
                    ['name' => $categoryLabel, 'path' => $categoryPath],
                    ['name' => $product['title'], 'path' => $categoryPath . '/' . $product['slug']],
                ]),
            ],
        ]);
    }

    private function adminLoginPage(): void
    {
        $settings = $this->catalog->getSettings();
        if (!$this->auth->isAllowedIp($settings)) {
            http_response_code(403);
            $this->logger->security('admin_login_page_denied_by_ip', $this->requestContext(), 'warn');

            echo $this->view->render('public/error', [
                'pageTitle' => 'Достъпът е ограничен',
                'company' => $this->catalog->getCompany(),
                'currentPath' => '/admin/login',
                'statusCode' => 403,
                'message' => 'Този IP адрес няма достъп до администрацията.',
            ]);
            return;
        }

        echo $this->view->render('admin/login', [
            'pageTitle' => 'Админ вход',
            'company' => $this->catalog->getCompany(),
            'currentPath' => '/admin/login',
            'flash' => flash_get('login'),
            'csrfToken' => $this->auth->csrfToken(),
        ]);
    }

    private function adminLoginSubmit(): void
    {
        if (!$this->auth->verifyCsrf($_POST['_csrf'] ?? null)) {
            $this->logger->security('csrf_validation_failed', $this->requestContext(['path' => '/admin/login']), 'warn');
            flash_set('login', 'Невалидна форма за вход.', 'error');
            redirect_to('/admin/login');
        }

        $code = trim((string) ($_POST['code'] ?? ''));
        if (!$this->auth->attemptLogin($code)) {
            flash_set('login', 'Невалиден код или IP адресът не е разрешен.', 'error');
            redirect_to('/admin/login');
        }

        flash_set('dashboard', 'Успешен вход в администрацията.', 'success');
        redirect_to('/admin');
    }

    private function adminLogout(): void
    {
        if (!$this->auth->verifyCsrf($_POST['_csrf'] ?? null)) {
            redirect_to('/admin');
        }

        $this->auth->logout();
        flash_set('login', 'Излезе успешно от администрацията.', 'success');
        redirect_to('/admin/login');
    }

    private function adminDashboard(): void
    {
        echo $this->view->render('admin/dashboard', [
            'pageTitle' => 'Администрация',
            'company' => $this->catalog->getCompany(),
            'currentPath' => '/admin',
            'csrfToken' => $this->auth->csrfToken(),
            'flash' => flash_get('dashboard'),
            'productsCount' => count($this->catalog->getProductsByCategory('airConditioners')) + count($this->catalog->getProductsByCategory('heatPumps')),
            'promotionsCount' => count($this->catalog->getPromotions()),
            'settings' => $this->catalog->getSettings(),
        ], 'layout');
    }

    private function adminProductsList(): void
    {
        echo $this->view->render('admin/products-list', [
            'pageTitle' => 'Продукти',
            'company' => $this->catalog->getCompany(),
            'currentPath' => '/admin/products',
            'csrfToken' => $this->auth->csrfToken(),
            'flash' => flash_get('products'),
            'airProducts' => $this->catalog->getProductsByCategory('airConditioners'),
            'heatProducts' => $this->catalog->getProductsByCategory('heatPumps'),
        ]);
    }

    private function adminProductForm(?string $slug): void
    {
        $category = ($_GET['category'] ?? 'airConditioners') === 'heatPumps' ? 'heatPumps' : 'airConditioners';
        $existing = $slug ? $this->catalog->findRawProduct($category, $slug) : null;

        echo $this->view->render('admin/product-form', [
            'pageTitle' => $existing ? 'Редакция на продукт' : 'Нов продукт',
            'company' => $this->catalog->getCompany(),
            'currentPath' => '/admin/products',
            'csrfToken' => $this->auth->csrfToken(),
            'flash' => flash_get('products'),
            'category' => $category,
            'existing' => $existing,
        ]);
    }

    private function adminProductSave(): void
    {
        if (!$this->auth->verifyCsrf($_POST['_csrf'] ?? null)) {
            flash_set('products', 'Невалидна заявка за запис.', 'error');
            redirect_to('/admin/products');
        }

        $category = ($_POST['category'] ?? 'airConditioners') === 'heatPumps' ? 'heatPumps' : 'airConditioners';
        $brand = trim((string) ($_POST['brand'] ?? ''));
        $series = trim((string) ($_POST['series'] ?? ''));
        $modelLabel = trim((string) ($_POST['modelLabel'] ?? ''));
        $oldSlug = trim((string) ($_POST['oldSlug'] ?? ''));

        if ($brand === '' || $series === '' || $modelLabel === '') {
            flash_set('products', 'Марка, серия и модел са задължителни.', 'error');
            redirect_to('/admin/products');
        }

        $seed = $this->dataStore->getProductSeed();
        $key = $category === 'heatPumps' ? 'heatPumps' : 'airConditioners';
        $seed[$key] = $this->removeProductFromSeries($seed[$key] ?? [], $oldSlug);

        $uploadedImage = $this->handleProductUpload($brand, $series, $modelLabel);
        $installationMode = in_array($_POST['installationMode'] ?? null, ['included', 'excluded'], true) ? $_POST['installationMode'] : null;
        $warrantyYears = $this->nullableInt($_POST['warrantyYears'] ?? null);
        $warrantyYears = $warrantyYears !== null && $warrantyYears > 0 ? $warrantyYears : null;

        $model = [
            'modelLabel' => $modelLabel,
            'description' => $this->nullableText($_POST['description'] ?? null),
            'priceBgn' => convert_eur_to_bgn($this->nullableFloat($_POST['priceEur'] ?? null)),
            'btu' => $this->nullableInt($_POST['btu'] ?? null),
            'powerKw' => $this->nullableFloat($_POST['powerKw'] ?? null),
            'technology' => in_array($_POST['technology'] ?? 'pending', ['inverter', 'hyperinverter', 'pending'], true) ? $_POST['technology'] : 'pending',
            'type' => $this->nullableText($_POST['type'] ?? null),
            'customImagePath' => $uploadedImage ?: $this->nullableText($_POST['customImagePath'] ?? null),
            'customImageSource' => $this->nullableText($_POST['customImageSource'] ?? null),
            'officialModelCode' => $this->nullableText($_POST['officialModelCode'] ?? null),
            'indoorUnit' => $this->nullableText($_POST['indoorUnit'] ?? null),
            'outdoorUnit' => $this->nullableText($_POST['outdoorUnit'] ?? null),
            'nominalCoolingKw' => $this->nullableFloat($_POST['nominalCoolingKw'] ?? null),
            'nominalHeatingKw' => $this->nullableFloat($_POST['nominalHeatingKw'] ?? null),
            'coolingRangeKw' => $this->nullableText($_POST['coolingRangeKw'] ?? null),
            'heatingRangeKw' => $this->nullableText($_POST['heatingRangeKw'] ?? null),
            'powerInputCoolingKw' => $this->nullableText($_POST['powerInputCoolingKw'] ?? null),
            'powerInputHeatingKw' => $this->nullableText($_POST['powerInputHeatingKw'] ?? null),
            'seer' => $this->nullableFloat($_POST['seer'] ?? null),
            'scop' => $this->nullableFloat($_POST['scop'] ?? null),
            'energyCooling' => $this->nullableText($_POST['energyCooling'] ?? null),
            'energyHeating' => $this->nullableText($_POST['energyHeating'] ?? null),
            'coverageM2' => $this->nullableText($_POST['coverageM2'] ?? null),
            'indoorNoiseDb' => $this->nullableText($_POST['indoorNoiseDb'] ?? null),
            'outdoorNoiseDb' => $this->nullableText($_POST['outdoorNoiseDb'] ?? null),
            'indoorDimensionsMm' => $this->nullableText($_POST['indoorDimensionsMm'] ?? null),
            'outdoorDimensionsMm' => $this->nullableText($_POST['outdoorDimensionsMm'] ?? null),
            'indoorWeightKg' => $this->nullableFloat($_POST['indoorWeightKg'] ?? null),
            'outdoorWeightKg' => $this->nullableFloat($_POST['outdoorWeightKg'] ?? null),
            'refrigerant' => $this->nullableText($_POST['refrigerant'] ?? null),
            'wifi' => isset($_POST['wifi']),
            'heatingOperatingRange' => $this->nullableText($_POST['heatingOperatingRange'] ?? null),
            'coolingOperatingRange' => $this->nullableText($_POST['coolingOperatingRange'] ?? null),
            'installationMode' => $installationMode,
            'warrantyYears' => $warrantyYears,
            'sourceTitle' => $this->nullableText($_POST['sourceTitle'] ?? null),
            'sourceUrl' => $this->nullableText($_POST['sourceUrl'] ?? null),
            'notes' => $this->splitTextarea($_POST['notes'] ?? ''),
            'status' => in_array($_POST['status'] ?? 'draft', ['draft', 'needs_verification', 'verified'], true) ? $_POST['status'] : 'draft',
        ];

        $seed[$key] = $this->upsertSeriesProduct($seed[$key] ?? [], $brand, $series, $model);
        $seed['generatedAt'] = date('Y-m-d');
        $this->dataStore->saveProductSeed($seed);

        $newSlug = slugify($brand . '-' . $series . '-' . $modelLabel);
        $this->logger->security('catalog_product_saved', $this->requestContext([
            'category' => $category,
            'slug' => $newSlug,
            'brand' => $brand,
            'series' => $series,
        ]));

        flash_set('products', 'Продуктът е записан успешно.', 'success');
        redirect_to('/admin/products/edit?category=' . $category . '&slug=' . urlencode($newSlug));
    }

    private function adminProductDelete(): void
    {
        if (!$this->auth->verifyCsrf($_POST['_csrf'] ?? null)) {
            flash_set('products', 'Невалидна заявка за изтриване.', 'error');
            redirect_to('/admin/products');
        }

        $category = ($_POST['category'] ?? 'airConditioners') === 'heatPumps' ? 'heatPumps' : 'airConditioners';
        $slug = trim((string) ($_POST['slug'] ?? ''));
        $seed = $this->dataStore->getProductSeed();
        $key = $category === 'heatPumps' ? 'heatPumps' : 'airConditioners';
        $seed[$key] = $this->removeProductFromSeries($seed[$key] ?? [], $slug);
        $this->dataStore->saveProductSeed($seed);

        $this->logger->security('catalog_product_deleted', $this->requestContext([
            'category' => $category,
            'slug' => $slug,
        ]), 'warn');

        flash_set('products', 'Продуктът беше изтрит.', 'success');
        redirect_to('/admin/products');
    }

    private function adminProductReorder(): void
    {
        if (!$this->auth->verifyCsrf($_POST['_csrf'] ?? null)) {
            flash_set('products', 'Невалидна заявка за пренареждане.', 'error');
            redirect_to('/admin/products');
        }

        $category = ($_POST['category'] ?? 'airConditioners') === 'heatPumps' ? 'heatPumps' : 'airConditioners';
        $direction = ($_POST['direction'] ?? '') === 'down' ? 'down' : 'up';
        $slug = trim((string) ($_POST['slug'] ?? ''));
        $key = $category === 'heatPumps' ? 'heatPumps' : 'airConditioners';
        $seed = $this->dataStore->getProductSeed();
        $items = $this->flattenRawProductOrder($seed[$key] ?? []);
        $index = null;

        foreach ($items as $itemIndex => $item) {
            if (($item['slug'] ?? null) === $slug) {
                $index = $itemIndex;
                break;
            }
        }

        $targetIndex = $index === null ? null : $index + ($direction === 'down' ? 1 : -1);
        if ($index === null || $targetIndex < 0 || $targetIndex >= count($items)) {
            flash_set('products', 'Продуктът не може да бъде преместен в тази посока.', 'error');
            redirect_to('/admin/products');
        }

        [$items[$index], $items[$targetIndex]] = [$items[$targetIndex], $items[$index]];
        $seed[$key] = $this->rebuildSeriesFromProductOrder($items);
        $seed['generatedAt'] = date('Y-m-d');
        $this->dataStore->saveProductSeed($seed);

        $this->logger->security('catalog_product_reordered', $this->requestContext([
            'category' => $category,
            'slug' => $slug,
            'direction' => $direction,
        ]));

        flash_set('products', 'Редът на продуктите е обновен.', 'success');
        redirect_to('/admin/products');
    }

    private function adminPromotionsList(): void
    {
        echo $this->view->render('admin/promotions-list', [
            'pageTitle' => 'Промоции',
            'company' => $this->catalog->getCompany(),
            'currentPath' => '/admin/promotions',
            'csrfToken' => $this->auth->csrfToken(),
            'flash' => flash_get('promotions'),
            'promotions' => $this->catalog->getPromotions(),
        ]);
    }

    private function adminPromotionForm(?string $id): void
    {
        $existing = null;
        if ($id !== null && $id !== '') {
            foreach ($this->catalog->getPromotions() as $item) {
                if (($item['id'] ?? null) === $id) {
                    $existing = $item;
                    break;
                }
            }
        }

        echo $this->view->render('admin/promotion-form', [
            'pageTitle' => $existing ? 'Редакция на промоция' : 'Нова промоция',
            'company' => $this->catalog->getCompany(),
            'currentPath' => '/admin/promotions',
            'csrfToken' => $this->auth->csrfToken(),
            'flash' => flash_get('promotions'),
            'existing' => $existing,
        ]);
    }

    private function adminPromotionSave(): void
    {
        if (!$this->auth->verifyCsrf($_POST['_csrf'] ?? null)) {
            flash_set('promotions', 'Невалидна заявка за запис.', 'error');
            redirect_to('/admin/promotions');
        }

        $currentId = trim((string) ($_POST['currentId'] ?? ''));
        $title = trim((string) ($_POST['title'] ?? ''));
        if ($title === '') {
            flash_set('promotions', 'Заглавието е задължително.', 'error');
            redirect_to('/admin/promotions');
        }

        $seed = $this->dataStore->getPromotions();
        $id = $currentId !== '' ? $currentId : slugify($title);
        $item = [
            'id' => $id,
            'title' => $title,
            'subtitle' => $this->nullableText($_POST['subtitle'] ?? null),
            'category' => in_array($_POST['category'] ?? 'general', ['airConditioners', 'heatPumps', 'general'], true) ? $_POST['category'] : 'general',
            'badge' => $this->nullableText($_POST['badge'] ?? null),
            'promoPriceBgn' => convert_eur_to_bgn($this->nullableFloat($_POST['promoPriceEur'] ?? null)),
            'oldPriceBgn' => convert_eur_to_bgn($this->nullableFloat($_POST['oldPriceEur'] ?? null)),
            'highlight' => $this->nullableText($_POST['highlight'] ?? null),
            'ctaLabel' => $this->nullableText($_POST['ctaLabel'] ?? null),
            'ctaHref' => $this->nullableText($_POST['ctaHref'] ?? null),
            'notes' => $this->splitTextarea($_POST['notes'] ?? ''),
            'isActive' => isset($_POST['isActive']),
            'sortOrder' => $this->nullableInt($_POST['sortOrder'] ?? null) ?? 99,
        ];

        $items = array_values(array_filter($seed['items'] ?? [], static fn ($promotion) => ($promotion['id'] ?? null) !== $currentId));
        $items[] = $item;
        usort($items, static fn (array $a, array $b) => ($a['sortOrder'] ?? 99) <=> ($b['sortOrder'] ?? 99));
        $seed['items'] = $items;
        $seed['updatedAt'] = date('Y-m-d');
        $this->dataStore->savePromotions($seed);

        $this->logger->security('promotion_saved', $this->requestContext([
            'promotionId' => $id,
            'active' => $item['isActive'],
        ]));

        flash_set('promotions', 'Промоцията е записана.', 'success');
        redirect_to('/admin/promotions/edit?id=' . urlencode($id));
    }

    private function adminPromotionDelete(): void
    {
        if (!$this->auth->verifyCsrf($_POST['_csrf'] ?? null)) {
            flash_set('promotions', 'Невалидна заявка за изтриване.', 'error');
            redirect_to('/admin/promotions');
        }

        $currentId = trim((string) ($_POST['currentId'] ?? ''));
        $seed = $this->dataStore->getPromotions();
        $seed['items'] = array_values(array_filter($seed['items'] ?? [], static fn ($item) => ($item['id'] ?? null) !== $currentId));
        $this->dataStore->savePromotions($seed);

        $this->logger->security('promotion_deleted', $this->requestContext([
            'promotionId' => $currentId,
        ]), 'warn');

        flash_set('promotions', 'Промоцията беше изтрита.', 'success');
        redirect_to('/admin/promotions');
    }

    private function adminSettingsPage(): void
    {
        echo $this->view->render('admin/settings', [
            'pageTitle' => 'Настройки',
            'company' => $this->catalog->getCompany(),
            'currentPath' => '/admin/settings',
            'csrfToken' => $this->auth->csrfToken(),
            'flash' => flash_get('settings'),
            'settings' => $this->catalog->getSettings(),
            'clientIpContext' => $this->auth->getClientIpContext(),
        ]);
    }

    private function adminSettingsSave(): void
    {
        if (!$this->auth->verifyCsrf($_POST['_csrf'] ?? null)) {
            flash_set('settings', 'Невалидна заявка за запис.', 'error');
            redirect_to('/admin/settings');
        }

        $settings = $this->dataStore->getAdminSettings();
        $company = $this->dataStore->getCompanyProfile();

        $accessMode = ($_POST['accessMode'] ?? 'open') === 'allowlist_only' ? 'allowlist_only' : 'open';
        $allowedIps = $this->splitTextarea($_POST['allowedIps'] ?? '');
        $clientIp = $this->auth->getClientIp();

        if ($accessMode === 'allowlist_only' && !$this->auth->isIpAllowedByEntries($clientIp, $allowedIps)) {
            $this->logger->security('admin_allowlist_save_failed_current_ip_not_allowed', $this->requestContext([
                'clientIp' => $clientIp,
                'allowedIpsCount' => count($allowedIps),
            ]), 'warn');
            flash_set('settings', 'Текущият IP трябва да присъства в allowlist-а. Можеш да използваш и CIDR запис като 192.168.1.0/24.', 'error');
            redirect_to('/admin/settings');
        }

        $settings['accessMode'] = $accessMode;
        $settings['allowedIps'] = $allowedIps;
        $settings['promo']['title'] = trim((string) ($_POST['promoTitle'] ?? $settings['promo']['title']));
        $settings['promo']['subtitle'] = trim((string) ($_POST['promoSubtitle'] ?? $settings['promo']['subtitle']));
        $settings['updatedAt'] = date('Y-m-d');

        $newCode = trim((string) ($_POST['adminCode'] ?? ''));
        $codeChanged = false;
        if ($newCode !== '') {
            $settings['adminSecurity']['codeHash'] = hash('sha256', $newCode);
            $settings['adminSecurity']['sessionVersion'] = (int) ($settings['adminSecurity']['sessionVersion'] ?? 1) + 1;
            $codeChanged = true;
        }

        $company['email'] = $this->nullableText($_POST['email'] ?? null);
        $company['website'] = $this->nullableText($_POST['website'] ?? null);
        $company['workingHours'] = $this->nullableText($_POST['workingHours'] ?? null);

        $this->dataStore->saveAdminSettings($settings);
        $this->dataStore->saveCompanyProfile($company);

        $this->logger->security($codeChanged ? 'admin_settings_saved_with_code_change' : 'admin_settings_saved', $this->requestContext([
            'accessMode' => $accessMode,
            'allowedIpsCount' => count($allowedIps),
        ]), $codeChanged ? 'warn' : 'info');

        if ($codeChanged) {
            $this->auth->logout();
            flash_set('login', 'Админ кодът е сменен. Влез наново с новия код.', 'success');
            redirect_to('/admin/login');
        }

        flash_set('settings', 'Настройките са записани.', 'success');
        redirect_to('/admin/settings');
    }

    private function contactTopics(): array
    {
        return [
            'general' => 'Общо запитване',
            'offer' => 'Оферта за климатик или термопомпа',
            'installation' => 'Монтаж или подмяна',
            'repair' => 'Ремонт и профилактика',
            'promotion' => 'Промоция',
        ];
    }

    private function contactRecipient(array $company): ?string
    {
        $recipient = getenv('CONTACT_FORM_TO');
        if (!is_string($recipient) || trim($recipient) === '') {
            $recipient = $company['email'] ?? null;
        }

        $recipient = is_string($recipient) ? trim($recipient) : '';

        return filter_var($recipient, FILTER_VALIDATE_EMAIL) !== false ? $recipient : null;
    }

    private function turnstileConfig(): array
    {
        if ($this->turnstileConfiguration !== null) {
            return $this->turnstileConfiguration;
        }

        $fileConfig = [];
        $configPath = $this->basePath . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'turnstile.php';
        if (is_file($configPath)) {
            $loaded = require $configPath;
            if (is_array($loaded)) {
                $fileConfig = $loaded;
            }
        }

        $siteKey = $this->environmentValue(['TURNSTILE_SITEKEY', 'TURNSTILE_SITE_KEY'])
            ?? trim((string) ($fileConfig['siteKey'] ?? ''));
        $secretKey = $this->environmentValue(['TURNSTILE_SECRET_KEY'])
            ?? trim((string) ($fileConfig['secretKey'] ?? ''));
        $enabled = $siteKey !== '' && $secretKey !== '';
        $testSiteKeys = [
            '1x00000000000000000000AA',
            '2x00000000000000000000AB',
            '3x00000000000000000000FF',
        ];

        return $this->turnstileConfiguration = [
            'enabled' => $enabled,
            'siteKey' => $siteKey,
            'secretKey' => $secretKey,
            'testMode' => in_array($siteKey, $testSiteKeys, true),
        ];
    }

    private function environmentValue(array $names): ?string
    {
        foreach ($names as $name) {
            $value = getenv($name);
            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return null;
    }

    private function contactMathCaptcha(): array
    {
        $challenge = $_SESSION['contact_math_captcha'] ?? null;
        if (is_array($challenge)
            && isset($challenge['id'], $challenge['question'], $challenge['answer'], $challenge['issuedAt'])
            && time() - (int) $challenge['issuedAt'] <= 600
        ) {
            return $challenge;
        }

        $left = random_int(2, 9);
        $right = random_int(1, 9);
        $challenge = [
            'id' => bin2hex(random_bytes(16)),
            'question' => $left . ' + ' . $right,
            'answer' => $left + $right,
            'issuedAt' => time(),
        ];
        $_SESSION['contact_math_captcha'] = $challenge;

        return $challenge;
    }

    private function verifyContactMathCaptcha(string $submittedId, string $submittedAnswer): bool
    {
        $challenge = $_SESSION['contact_math_captcha'] ?? null;
        unset($_SESSION['contact_math_captcha']);

        if (!is_array($challenge)
            || !isset($challenge['id'], $challenge['answer'], $challenge['issuedAt'])
            || time() - (int) $challenge['issuedAt'] > 600
            || !hash_equals((string) $challenge['id'], trim($submittedId))
            || preg_match('/^\d{1,3}$/', trim($submittedAnswer)) !== 1
        ) {
            return false;
        }

        return (int) trim($submittedAnswer) === (int) $challenge['answer'];
    }

    private function verifyTurnstileToken(string $token, array $config): array
    {
        $failure = static fn (array $codes, ?string $hostname = null): array => [
            'success' => false,
            'errorCodes' => array_slice(array_values(array_filter(array_map(
                static fn ($code) => preg_replace('/[^a-z0-9_-]/i', '', (string) $code),
                $codes
            ))), 0, 8),
            'hostname' => $hostname !== null ? mb_substr($hostname, 0, 255, 'UTF-8') : null,
        ];

        if ($token === '' || strlen($token) > 2048) {
            return $failure(['missing-or-invalid-token']);
        }

        $payload = [
            'secret' => $config['secretKey'],
            'response' => $token,
        ];
        $clientIp = detect_client_ip();
        if ($clientIp !== null && filter_var($clientIp, FILTER_VALIDATE_IP) !== false) {
            $payload['remoteip'] = $clientIp;
        }

        $response = $this->postTurnstileVerification($payload);
        if ($response === null) {
            return $failure(['verification-unavailable']);
        }

        $result = json_decode($response, true);
        if (!is_array($result)) {
            return $failure(['invalid-verification-response']);
        }

        $hostname = isset($result['hostname']) && is_string($result['hostname']) ? strtolower($result['hostname']) : null;
        $errorCodes = is_array($result['error-codes'] ?? null) ? $result['error-codes'] : [];
        if (($result['success'] ?? false) !== true) {
            return $failure($errorCodes !== [] ? $errorCodes : ['verification-failed'], $hostname);
        }

        if (!$config['testMode']) {
            $allowedHosts = ['kotupanovclima.eu', 'www.kotupanovclima.eu'];
            if ($hostname === null || !in_array($hostname, $allowedHosts, true)) {
                return $failure(['hostname-mismatch'], $hostname);
            }

            if (($result['action'] ?? null) !== 'contact') {
                return $failure(['action-mismatch'], $hostname);
            }
        }

        return [
            'success' => true,
            'errorCodes' => [],
            'hostname' => $hostname,
        ];
    }

    private function postTurnstileVerification(array $payload): ?string
    {
        $endpoint = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
        $encodedPayload = http_build_query($payload, '', '&', PHP_QUERY_RFC3986);

        if (function_exists('curl_init')) {
            $curl = curl_init($endpoint);
            if ($curl === false) {
                return null;
            }

            curl_setopt_array($curl, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $encodedPayload,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 3,
                CURLOPT_TIMEOUT => 8,
                CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            ]);
            $response = curl_exec($curl);
            $statusCode = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
            curl_close($curl);

            return is_string($response) && $statusCode === 200 ? $response : null;
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => $encodedPayload,
                'timeout' => 8,
                'ignore_errors' => true,
            ],
        ]);
        $response = @file_get_contents($endpoint, false, $context);
        $statusLine = $http_response_header[0] ?? '';

        return is_string($response) && str_contains($statusLine, ' 200 ') ? $response : null;
    }

    private function limitText(mixed $value, int $length): string
    {
        $value = trim(normalize_newlines(strip_tags((string) ($value ?? ''))));

        return mb_substr($value, 0, $length, 'UTF-8');
    }

    private function encodedMailSubject(string $subject): string
    {
        return '=?UTF-8?B?' . base64_encode($subject) . '?=';
    }

    private function nullableText(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' ? $value : null;
    }

    private function nullableFloat(mixed $value): ?float
    {
        $value = str_replace(',', '.', trim((string) ($value ?? '')));

        if ($value === '' || !is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }

    private function nullableInt(mixed $value): ?int
    {
        $value = trim((string) ($value ?? ''));

        if ($value === '' || !is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }

    private function splitTextarea(string $value): array
    {
        $items = array_map('trim', explode("\n", normalize_newlines($value)));
        $items = array_values(array_filter($items, static fn ($item) => $item !== ''));

        return $items;
    }

    private function removeProductFromSeries(array $seriesList, string $slug): array
    {
        $result = [];

        foreach ($seriesList as $series) {
            if (!is_array($series)) {
                continue;
            }

            $models = array_values(array_filter($series['models'] ?? [], function (array $model) use ($series, $slug): bool {
                $candidateSlug = slugify(($series['brand'] ?? '') . '-' . ($series['series'] ?? '') . '-' . ($model['modelLabel'] ?? ''));

                return $candidateSlug !== $slug;
            }));

            if ($models !== []) {
                $series['models'] = $models;
                $result[] = $series;
            }
        }

        return $result;
    }

    private function upsertSeriesProduct(array $seriesList, string $brand, string $seriesName, array $model): array
    {
        foreach ($seriesList as &$series) {
            if (($series['brand'] ?? null) === $brand && ($series['series'] ?? null) === $seriesName) {
                $series['models'][] = $model;
                return $seriesList;
            }
        }
        unset($series);

        $seriesList[] = [
            'brand' => $brand,
            'series' => $seriesName,
            'models' => [$model],
        ];

        return $seriesList;
    }

    private function flattenRawProductOrder(array $seriesList): array
    {
        $items = [];

        foreach ($seriesList as $series) {
            if (!is_array($series)) {
                continue;
            }

            $seriesData = $series;
            unset($seriesData['models']);

            foreach (($series['models'] ?? []) as $model) {
                if (!is_array($model)) {
                    continue;
                }

                $items[] = [
                    'slug' => slugify(($series['brand'] ?? '') . '-' . ($series['series'] ?? '') . '-' . ($model['modelLabel'] ?? '')),
                    'series' => $seriesData,
                    'model' => $model,
                ];
            }
        }

        return $items;
    }

    private function rebuildSeriesFromProductOrder(array $items): array
    {
        $seriesList = [];

        foreach ($items as $item) {
            if (!is_array($item['series'] ?? null) || !is_array($item['model'] ?? null)) {
                continue;
            }

            $seriesData = $item['series'];
            $lastIndex = count($seriesList) - 1;
            if ($lastIndex >= 0) {
                $lastSeriesData = $seriesList[$lastIndex];
                unset($lastSeriesData['models']);

                if ($lastSeriesData == $seriesData) {
                    $seriesList[$lastIndex]['models'][] = $item['model'];
                    continue;
                }
            }

            $seriesData['models'] = [$item['model']];
            $seriesList[] = $seriesData;
        }

        return $seriesList;
    }

    private function handleProductUpload(string $brand, string $series, string $modelLabel): ?string
    {
        if (!isset($_FILES['imageFile']) || !is_array($_FILES['imageFile'])) {
            return null;
        }

        $file = $_FILES['imageFile'];
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            flash_set('products', 'Неуспешно качване на изображение.', 'error');
            redirect_to('/admin/products');
        }

        if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
            $this->logger->security('product_image_upload_rejected', $this->requestContext([
                'reason' => 'too_large',
                'fileSize' => $file['size'] ?? null,
            ]), 'warn');
            flash_set('products', 'Изображението е по-голямо от 5 MB.', 'error');
            redirect_to('/admin/products');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        $extensions = [
            'image/jpeg' => '.jpg',
            'image/png' => '.png',
            'image/webp' => '.webp',
        ];

        if (!isset($extensions[$mimeType])) {
            $this->logger->security('product_image_upload_rejected', $this->requestContext([
                'reason' => 'invalid_type',
                'mimeType' => $mimeType,
            ]), 'warn');
            flash_set('products', 'Позволени са само JPG, PNG и WEBP файлове.', 'error');
            redirect_to('/admin/products');
        }

        $fileName = slugify($brand . '-' . $series . '-' . $modelLabel) . '-' . time() . $extensions[$mimeType];
        $targetDirectory = $this->publicUploadsDirectory();

        if (!is_dir($targetDirectory)) {
            mkdir($targetDirectory, 0775, true);
        }

        $targetPath = $targetDirectory . DIRECTORY_SEPARATOR . $fileName;
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            flash_set('products', 'Неуспешно записване на качения файл.', 'error');
            redirect_to('/admin/products');
        }

        return '/uploads/' . $fileName;
    }

    private function publicUploadsDirectory(): string
    {
        $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? null;
        if (is_string($documentRoot) && trim($documentRoot) !== '') {
            return rtrim($documentRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads';
        }

        return $this->basePath . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads';
    }

    /**
     * Base https://schema.org/HVACBusiness node for the company, reused across pages.
     */
    private function businessSchema(array $company, bool $detailed = false): array
    {
        $baseUrl = $this->currentSeoBaseUrl();
        $phones = array_values(array_filter($company['phones'] ?? [], static fn ($phone) => is_string($phone) && $phone !== ''));

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'HVACBusiness',
            'name' => $company['companyName'] ?? 'Котупановклима ЕООД',
            'url' => $baseUrl . '/',
            'image' => $baseUrl . '/images/site-og-image.png',
            'logo' => $baseUrl . '/images/kotupanovclima-logo.png',
            'priceRange' => '$$',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => 'ул. Китка 3',
                'addressLocality' => 'Перник',
                'postalCode' => '2300',
                'addressCountry' => 'BG',
            ],
            'areaServed' => ['@type' => 'City', 'name' => 'Перник'],
        ];

        if ($phones !== []) {
            $schema['telephone'] = $phones[0];
        }

        if (!empty($company['email'])) {
            $schema['email'] = $company['email'];
        }

        if (!empty($company['vatNumber'])) {
            $schema['vatID'] = $company['vatNumber'];
        }

        if ($detailed) {
            $schema['geo'] = [
                '@type' => 'GeoCoordinates',
                'latitude' => '42.6050',
                'longitude' => '23.0378',
            ];
            $schema['openingHoursSpecification'] = [
                '@type' => 'OpeningHoursSpecification',
                'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
                'opens' => '09:00',
                'closes' => '18:00',
            ];
        }

        return $schema;
    }

    /**
     * @param array<int, array{name: string, path: string}> $trail
     */
    private function breadcrumbSchema(array $trail): array
    {
        $baseUrl = $this->currentSeoBaseUrl();
        $items = [];
        $position = 1;

        foreach ($trail as $crumb) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $crumb['name'],
                'item' => $baseUrl . $crumb['path'],
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }

    /**
     * @param array<int, array{question: string, answer: string}> $entries
     */
    private function faqSchema(array $entries): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_map(static fn (array $entry) => [
                '@type' => 'Question',
                'name' => $entry['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $entry['answer'],
                ],
            ], $entries),
        ];
    }

    private function productSchema(array $product, array $company): array
    {
        $baseUrl = $this->currentSeoBaseUrl();
        $categoryPath = $product['category'] === 'heatPumps' ? 'termopompi' : 'klimatici';
        $productUrl = $baseUrl . '/produkti/' . $categoryPath . '/' . $product['slug'];

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product['title'],
            'brand' => ['@type' => 'Brand', 'name' => $product['brand']],
            'category' => $product['category'] === 'heatPumps' ? 'Термопомпи' : 'Климатици',
            'url' => $productUrl,
        ];

        if (!empty($product['description'])) {
            $schema['description'] = $product['description'];
        }

        if (!empty($product['imagePath'])) {
            $schema['image'] = str_starts_with((string) $product['imagePath'], 'http')
                ? $product['imagePath']
                : $baseUrl . $product['imagePath'];
        }

        if (!empty($product['officialModelCode'])) {
            $schema['mpn'] = $product['officialModelCode'];
        }

        if (!empty($product['priceEur'])) {
            $schema['offers'] = [
                '@type' => 'Offer',
                'priceCurrency' => 'EUR',
                'price' => number_format((float) $product['priceEur'], 2, '.', ''),
                'availability' => 'https://schema.org/InStock',
                'url' => $productUrl,
                'seller' => ['@type' => 'Organization', 'name' => $company['companyName'] ?? 'Котупановклима ЕООД'],
            ];
        }

        return $schema;
    }

    /**
     * Shared FAQ used on the home page and repair page (also emitted as FAQPage schema).
     *
     * @return array<int, array{question: string, answer: string}>
     */
    private function faqEntries(): array
    {
        return [
            [
                'question' => 'Колко струва монтаж на климатик в Перник?',
                'answer' => 'Обхватът и цената на монтажа зависят от модела, дължината на трасето и особеностите на обекта. Свържете се за точна оферта.',
            ],
            [
                'question' => 'На колко време се прави профилактика на климатик?',
                'answer' => 'Препоръчва се профилактика поне веднъж годишно, най-добре преди активния сезон, за по-тиха работа и по-нисък разход.',
            ],
            [
                'question' => 'Каква мощност климатик ми трябва според квадратурата?',
                'answer' => 'За стандартна стая до 20 кв.м обикновено е достатъчен модел 9000 BTU, за 20–35 кв.м — 12000 BTU, а над 35 кв.м — 18000 BTU и нагоре. Височината, изложението и остъкляването също влияят, затова уточняваме избора при консултация.',
            ],
            [
                'question' => 'Работите ли с термопомпи въздух-вода за отопление?',
                'answer' => 'Да. Предлагаме доставка и монтаж на термопомпи въздух-вода за отопление и охлаждане в Перник и региона, с подбор според дома и нуждите.',
            ],
        ];
    }

    private function requestContext(array $extra = []): array
    {
        $clientIpContext = $this->auth->getClientIpContext();

        return array_merge([
            'requestId' => $this->requestId,
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'GET',
            'path' => request_path(),
            'host' => $_SERVER['HTTP_HOST'] ?? null,
            'clientIp' => $clientIpContext['clientIp'] ?? null,
            'clientIpSource' => $clientIpContext['clientIpSource'] ?? null,
            'remoteAddr' => $clientIpContext['remoteAddr'] ?? null,
            'trustedProxy' => $clientIpContext['trustedProxy'] ?? false,
            'xForwardedFor' => $clientIpContext['xForwardedFor'] ?? null,
            'xForwardedChain' => $clientIpContext['xForwardedChain'] ?? [],
            'xRealIp' => $clientIpContext['xRealIp'] ?? null,
            'userAgent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'referer' => $_SERVER['HTTP_REFERER'] ?? null,
        ], $extra);
    }

    private function isSuspiciousPath(string $path): bool
    {
        $value = strtolower($path);
        foreach (['../', '%2e%2e', '.env', '.git', '/wp-admin', 'phpmyadmin', 'etc/passwd', 'boaform'] as $needle) {
            if (str_contains($value, $needle)) {
                return true;
            }
        }

        return false;
    }

    public function logAccess(): void
    {
        $durationMs = round((microtime(true) - $this->startedAt) * 1000, 2);
        $this->logger->access($this->requestContext([
            'statusCode' => http_response_code(),
            'durationMs' => $durationMs,
        ]));
    }

    private function sendSecurityHeaders(string $path): void
    {
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Permissions-Policy: accelerometer=(), camera=(), geolocation=(), gyroscope=(), microphone=(), payment=(), usb=()');
        header('Cross-Origin-Opener-Policy: same-origin');
        $turnstileSources = $path === '/kontakti' && $this->turnstileConfig()['enabled'];
        $scriptSources = "'self'" . ($turnstileSources ? ' https://challenges.cloudflare.com' : '');
        $frameSources = $turnstileSources ? ' frame-src https://challenges.cloudflare.com;' : '';
        header("Content-Security-Policy: default-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'; object-src 'none'; script-src {$scriptSources}; style-src 'self'; img-src 'self' data: https:;{$frameSources}");
    }
}
