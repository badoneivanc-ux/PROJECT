<?php

declare(strict_types=1);

use Project\Core\Routeur;

define('BASE_URL', '/preview');
require_once __DIR__ . '/../Views/Autoloader.php';

set_error_handler(static function (int $severity, string $message, string $file, int $line): never {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

function zoneCheck(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function zoneRender(string $action): DOMXPath
{
    $_GET = ['controller' => 'home', 'action' => $action];
    $_SERVER['QUERY_STRING'] = http_build_query($_GET);
    ob_start();
    (new Routeur())->dispatch();
    $html = ob_get_clean();
    zoneCheck(is_string($html), 'Missing rendered HTML');
    $document = new DOMDocument();
    $previous = libxml_use_internal_errors(true);
    $document->loadHTML($html);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);
    return new DOMXPath($document);
}

function zoneMarkup(DOMXPath $xpath, string $selector): string
{
    $node = $xpath->query($selector)->item(0);
    zoneCheck($node !== null, "Missing element: $selector");
    return $node->ownerDocument->saveHTML($node);
}

$zoneUrl = 'https://latelierdumuseau.fr/index.php?controller=home&action=infosPratiques';
$expectedCommunes = [
    'Vincennes', 'Montreuil', 'Saint-Mandé', 'Fontenay-sous-Bois',
    'Saint-Maurice', 'Charenton-le-Pont', 'Paris 12e (75012)',
    'Nogent-sur-Marne', 'Joinville-le-Pont',
    'Saint-Maur-des-Fossés',
];

foreach ([
    'visitor' => [[], '/index.php?controller=user&action=register'],
    'client' => [['user_id' => 2, 'user_role' => 'user', 'user_prenom' => 'Test'], '/index.php?controller=reservation&action=create'],
    'admin' => [['user_id' => 1, 'user_role' => 'admin', 'user_prenom' => 'Admin'], '/index.php?controller=admin&action=dashboard'],
] as $role => [$session, $cta]) {
    $_SESSION = $session;
    $zone = zoneRender('infosPratiques');
    zoneCheck($zone->query('//nav//a[@href="' . BASE_URL . '/index.php?controller=home&action=infosPratiques"]')->length === 1, "Missing navigation link for $role");
    zoneCheck($zone->query('//main[@class="zone-page"]')->length === 1, "Missing zone page for $role");
    zoneCheck($zone->query('//main//h1')->length === 1, 'Expected exactly one H1');
    zoneCheck($zone->query('//div[@class="zone-faq"]/details/summary')->length === 4, 'Missing native FAQ');
    zoneCheck($zone->query('//link[contains(@href, "/style.css?v=")]')->length === 1, 'Missing main stylesheet');
    zoneCheck($zone->evaluate('string(//link[@rel="canonical"]/@href)') === $zoneUrl, 'Wrong canonical');
    zoneCheck($zone->evaluate('string(//meta[@property="og:url"]/@content)') === $zoneUrl, 'Wrong social URL');
    zoneCheck($zone->evaluate('string(//meta[@property="og:title"]/@content)') === $zone->evaluate('string(//title)'), 'Wrong social title');
    $actualCommunes = [];
    foreach ($zone->query('//div[@id="communes"]/details/ul/li') as $node) {
        $actualCommunes[] = $node->textContent;
    }
    zoneCheck($actualCommunes === $expectedCommunes, 'Wrong or duplicate communes');
    $mapCommunes = [];
    foreach ($zone->query('//*[@id="zone-map-areas"]/path') as $area) {
        $mapCommunes[] = $area->getAttribute('data-commune');
    }
    zoneCheck($mapCommunes === $expectedCommunes, 'Map areas differ from the served communes');
    zoneCheck($zone->query('//svg[@role="img"][@aria-labelledby="zone-map-title zone-map-description"]')->length === 1, 'Missing accessible geographic map');
    $html = $zone->document->saveHTML();
    zoneCheck(!str_contains($html, '75020') && !str_contains($html, '20e'), 'Paris 20e still advertised in the page or metadata');
    $ctas = $zone->query('//main//a[@href="' . BASE_URL . $cta . '"]');
    zoneCheck($ctas->length === 1, "Wrong CTA for $role");
    foreach ($zone->query('//main//a[starts-with(@href, "#")]') as $anchor) {
        $id = substr($anchor->getAttribute('href'), 1);
        zoneCheck($zone->query('//*[@id="' . $id . '"]')->length === 1, "Broken anchor: $id");
    }
    foreach ($zone->query('//main//img') as $image) {
        $src = $image->getAttribute('src');
        zoneCheck(str_starts_with($src, BASE_URL . '/uploads/images-zoneIntervention/'), 'Image missing deployment-safe path');
        $file = __DIR__ . '/../public' . substr($src, strlen(BASE_URL));
        zoneCheck(is_file($file), "Missing image: $src");
        $dimensions = getimagesize($file);
        zoneCheck($dimensions !== false && $dimensions[0] > 0 && $dimensions[1] > 0, "Invalid image: $src");
        zoneCheck((int) $image->getAttribute('width') > 0 && (int) $image->getAttribute('height') > 0, "Missing display dimensions: $src");
    }
    foreach (['index', 'tarifs'] as $action) {
        $other = zoneRender($action);
        zoneCheck($other->query('//nav//a[@href="' . BASE_URL . '/index.php?controller=home&action=infosPratiques"]')->length === 1, "Missing navigation link on $action");
        zoneCheck(zoneMarkup($zone, '//nav') === zoneMarkup($other, '//nav'), "Menu changed for $role");
        zoneCheck(zoneMarkup($zone, '//footer') === zoneMarkup($other, '//footer'), "Footer changed for $role");
    }
}

$map = simplexml_load_file(__DIR__ . '/../public/uploads/images-zoneIntervention/communes.svg');
zoneCheck($map !== false, 'Invalid map SVG');
$map->registerXPathNamespace('s', 'http://www.w3.org/2000/svg');
zoneCheck(count($map->xpath('//s:script | //s:image | //s:foreignObject')) === 0, 'Map must not load scripts or external content');
zoneCheck(count($map->xpath('//s:path[@data-insee="94080"][@fill="#b28b3b"]')) === 1, 'Vincennes must be highlighted');

$sitemap = simplexml_load_file(__DIR__ . '/../public/sitemap-pages.xml');
zoneCheck($sitemap !== false, 'Invalid sitemap');
$sitemap->registerXPathNamespace('s', 'http://www.sitemaps.org/schemas/sitemap/0.9');
zoneCheck(count($sitemap->xpath('//s:loc[text()="' . $zoneUrl . '"]')) === 1, 'Missing zone sitemap entry');

echo "Infos pratiques: all checks passed (visitor, client, admin).\n";
