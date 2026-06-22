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
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $this->sendSecurityHeaders();
        header('X-Request-Id: ' . $this->requestId);
        register_shutdown_function(fn () => $this->logAccess());

        $path = request_path();
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

        if ($path === '/') {
            $this->home();
            return;
        }

        if ($path === '/promocii') {
            $this->promotionsPage();
            return;
        }

        if ($path === '/kontakti') {
            $this->contactsPage();
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
        echo $this->view->render('public/home', [
            'pageTitle' => 'Котупановклима ЕООД',
            'company' => $this->catalog->getCompany(),
            'settings' => $this->catalog->getSettings(),
            'promotions' => array_slice($this->catalog->getPromotions(true), 0, 4),
            'brandShowcase' => $this->catalog->getBrandShowcase(),
            'currentPath' => '/',
        ]);
    }

    private function promotionsPage(): void
    {
        echo $this->view->render('public/promotions', [
            'pageTitle' => 'Промоции',
            'company' => $this->catalog->getCompany(),
            'promotions' => $this->catalog->getPromotions(true),
            'currentPath' => '/promocii',
        ]);
    }

    private function contactsPage(): void
    {
        echo $this->view->render('public/contacts', [
            'pageTitle' => 'Контакти',
            'company' => $this->catalog->getCompany(),
            'currentPath' => '/kontakti',
        ]);
    }

    private function catalogPage(string $category): void
    {
        $path = $category === 'heatPumps' ? '/produkti/termopompi' : '/produkti/klimatici';
        $products = $this->catalog->getProductsByCategory($category);
        $title = $category === 'heatPumps'
            ? 'Подбрани въздух-вода решения за модерни инсталации'
            : 'Каталог с по-ясен избор по марка, серия и цена';

        echo $this->view->render('public/catalog', [
            'pageTitle' => $category === 'heatPumps' ? 'Термопомпи' : 'Климатици',
            'company' => $this->catalog->getCompany(),
            'currentPath' => $path,
            'products' => $products,
            'category' => $category,
            'title' => $title,
            'description' => $category === 'heatPumps'
                ? 'Структурата е подготвена за по-ясно сравнение между серии, мощности и типове системи.'
                : 'Подредихме моделите така, че клиентът да стига лесно до подходящата мощност, технология и ценови диапазон.',
            'officialLinks' => $this->catalog->getOfficialBrandLinks(),
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

        echo $this->view->render('public/product', [
            'pageTitle' => $product['title'],
            'company' => $this->catalog->getCompany(),
            'currentPath' => request_path(),
            'product' => $product,
        ]);
    }

    private function adminLoginPage(): void
    {
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
        $targetDirectory = $this->basePath . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads';

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

    private function sendSecurityHeaders(): void
    {
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Permissions-Policy: accelerometer=(), camera=(), geolocation=(), gyroscope=(), microphone=(), payment=(), usb=()');
        header('Cross-Origin-Opener-Policy: same-origin');
        header("Content-Security-Policy: default-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'; object-src 'none'; script-src 'self'; style-src 'self'; img-src 'self' data: https:;");
    }
}
