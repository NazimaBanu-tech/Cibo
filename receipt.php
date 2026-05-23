<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/orders.php';
require_once __DIR__ . '/includes/receipt-pdf.php';
require_once __DIR__ . '/includes/catalog.php';

cibo_start_user_session();

$orderNumber = trim((string) ($_GET['order'] ?? ($_GET['order_number'] ?? ($_SESSION['last_order_number'] ?? ''))));
$receiptToken = trim((string) ($_GET['token'] ?? ''));
$format = strtolower(trim((string) ($_GET['format'] ?? 'html')));
$context = cibo_fetch_receipt_context($orderNumber, $receiptToken);

if (!$context) {
    http_response_code(404);
}

if ($context && $format === 'pdf') {
    $receiptNumber = (string) ($context['receipt']['receipt_number'] ?? ('receipt-' . $orderNumber));
    $filename = preg_replace('/[^A-Za-z0-9._-]+/', '-', strtolower($receiptNumber)) ?: ('cibo-receipt-' . strtolower($orderNumber));

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '.pdf"');
    header('Cache-Control: private, max-age=0, no-store, no-cache, must-revalidate');
    echo cibo_receipt_pdf_bytes($context);
    exit;
}

$order = is_array($context['order'] ?? null) ? $context['order'] : [];
$receipt = is_array($context['receipt'] ?? null) ? $context['receipt'] : [];
$summary = is_array($context['summary'] ?? null) ? $context['summary'] : [];
$links = is_array($context['links'] ?? null) ? $context['links'] : [];
$items = is_array($order['items'] ?? null) ? $order['items'] : [];

function cibo_receipt_h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function cibo_receipt_money(float $amount): string
{
    return "\u{20B9}" . number_format($amount, 2);
}

function cibo_receipt_date(string $value): string
{
    $timestamp = strtotime($value);

    if ($timestamp === false) {
        return $value !== '' ? $value : '--';
    }

    return date('d M Y, h:i A', $timestamp);
}

function cibo_receipt_placeholder_data_uri(string $label, string $accent = '#6b8a3c', string $background = '#edf3e4'): string
{
    $safeLabel = strtoupper(substr(trim($label), 0, 1) ?: 'C');
    $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 96 96" role="img" aria-label="{$safeLabel}">
  <rect width="96" height="96" rx="24" fill="{$background}"/>
  <circle cx="48" cy="48" r="28" fill="#fff8ed"/>
  <text x="48" y="56" text-anchor="middle" font-family="Arial, sans-serif" font-size="34" font-weight="700" fill="{$accent}">{$safeLabel}</text>
</svg>
SVG;

    return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode($svg);
}

function cibo_receipt_food_placeholder_uri(): string
{
    $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 96 96" role="img" aria-label="Food item">
  <rect width="96" height="96" rx="24" fill="#edf3e4"/>
  <rect x="18" y="18" width="60" height="60" rx="18" fill="#fff8ed"/>
  <path d="M32 35h32M36 43h24M40 51h16M44 59h8" stroke="#6b8a3c" stroke-width="5" stroke-linecap="round"/>
</svg>
SVG;

    return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode($svg);
}

function cibo_receipt_asset_exists(string $path): bool
{
    $path = trim($path);

    if ($path === '') {
        return false;
    }

    if (str_starts_with($path, 'data:')) {
        return true;
    }

    $cleanPath = trim((string) parse_url($path, PHP_URL_PATH));

    if ($cleanPath === '' || preg_match('#^https?://#i', $path)) {
        return false;
    }

    $normalized = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($cleanPath, '/\\'));

    return is_file(__DIR__ . DIRECTORY_SEPARATOR . $normalized);
}

function cibo_receipt_image_or_fallback(string $path, string $fallback): string
{
    return cibo_receipt_asset_exists($path) ? trim($path) : $fallback;
}

function cibo_receipt_normalize_key(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';

    return trim($value, '-');
}

function cibo_receipt_find_restaurant_image(array $order, string $fallback): string
{
    $restaurantId = (int) ($order['restaurant_id'] ?? 0);
    $restaurantName = trim((string) ($order['restaurant_name'] ?? ''));
    $restaurantKey = cibo_receipt_normalize_key($restaurantName);

    foreach (cibo_catalog_fetch_restaurants() as $restaurant) {
        $candidateId = (int) ($restaurant['id'] ?? 0);
        $candidateName = trim((string) ($restaurant['name'] ?? ''));
        $candidateKey = cibo_receipt_normalize_key((string) ($restaurant['slug'] ?? $candidateName));

        if (
            ($restaurantId > 0 && $candidateId === $restaurantId)
            || ($restaurantKey !== '' && $candidateKey === $restaurantKey)
            || ($restaurantName !== '' && strcasecmp($candidateName, $restaurantName) === 0)
        ) {
            $restaurantImage = trim((string) ($restaurant['image'] ?? ''));
            $restaurantHeroImage = trim((string) ($restaurant['heroImage'] ?? ''));

            return cibo_receipt_image_or_fallback($restaurantImage !== '' ? $restaurantImage : $restaurantHeroImage, $fallback);
        }
    }

    return $fallback;
}

function cibo_receipt_menu_image_map(): array
{
    static $map = null;

    if (is_array($map)) {
        return $map;
    }

    $map = [];

    foreach (cibo_catalog_fetch_menu_items() as $menuItem) {
        $menuItemId = (int) ($menuItem['menuItemId'] ?? 0);
        $restaurantId = (int) ($menuItem['restaurantId'] ?? 0);
        $itemName = trim((string) ($menuItem['name'] ?? ''));
        $image = trim((string) ($menuItem['image'] ?? ''));

        if ($menuItemId > 0) {
            $map['id:' . $menuItemId] = $image;
        }

        if ($restaurantId > 0 && $itemName !== '') {
            $map['restaurant:' . $restaurantId . ':' . cibo_receipt_normalize_key($itemName)] = $image;
        }
    }

    return $map;
}

function cibo_receipt_find_item_image(array $item, array $order, string $fallback): string
{
    $directImage = trim((string) ($item['image'] ?? ''));

    if ($directImage !== '') {
        return cibo_receipt_image_or_fallback($directImage, $fallback);
    }

    $menuImageMap = cibo_receipt_menu_image_map();
    $menuItemId = (int) ($item['menu_item_id'] ?? 0);

    if ($menuItemId > 0 && !empty($menuImageMap['id:' . $menuItemId])) {
        return cibo_receipt_image_or_fallback((string) $menuImageMap['id:' . $menuItemId], $fallback);
    }

    $restaurantId = (int) ($order['restaurant_id'] ?? 0);
    $itemName = trim((string) ($item['name'] ?? ''));
    $nameKey = 'restaurant:' . $restaurantId . ':' . cibo_receipt_normalize_key($itemName);

    if ($restaurantId > 0 && $itemName !== '' && !empty($menuImageMap[$nameKey])) {
        return cibo_receipt_image_or_fallback((string) $menuImageMap[$nameKey], $fallback);
    }

    return $fallback;
}

$receiptPageTitle = $context ? 'Receipt - Cibo' : 'Receipt Not Found - Cibo';
$receiptViewUrl = (string) ($links['view'] ?? '#');
$receiptDownloadUrl = (string) ($links['download'] ?? '#');
$orderStatusLabel = (string) ($order['order_status_label'] ?? $order['order_status'] ?? '--');
$paymentStatusLabel = (string) ($order['payment_status_label'] ?? $order['payment_status'] ?? '--');
$restaurantPlaceholderImage = cibo_receipt_placeholder_data_uri((string) ($order['restaurant_name'] ?? 'Cibo Order'));
$foodPlaceholderImage = cibo_receipt_food_placeholder_uri();
$restaurantImage = cibo_receipt_find_restaurant_image($order, $restaurantPlaceholderImage);
$receiptLogoImage = cibo_receipt_asset_exists('images/logo.png') ? 'images/logo.png' : '';

foreach ($items as $index => $item) {
    $items[$index]['receipt_image'] = cibo_receipt_find_item_image($item, $order, $foodPlaceholderImage);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= cibo_receipt_h($receiptPageTitle) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="global.css">
  <style>
    :root {
      --receipt-bg: #f1ece4;
      --receipt-surface: #fffdf9;
      --receipt-surface-strong: #fbf8f3;
      --receipt-border: #e7dfd3;
      --receipt-text: #1f1f1b;
      --receipt-muted: #6f685f;
      --receipt-soft: #f6f1e8;
      --receipt-soft-strong: #eee6da;
      --receipt-ink: #171715;
      --receipt-accent: #5f7c3a;
      --receipt-accent-soft: #eef4e7;
      --receipt-accent-border: #d8e4c4;
      --receipt-olive-text: #4f6732;
      --receipt-ambient: rgba(95, 124, 58, 0.12);
      --receipt-dark: #23201c;
      --receipt-green: #5f7c3a;
      --receipt-green-deep: #4f6732;
      --receipt-shadow: 0 18px 40px rgba(31, 31, 27, 0.08);
      --receipt-ring: inset 0 0 0 1px rgba(95, 124, 58, 0.08);
    }

    body {
      min-height: 100vh;
      color: var(--receipt-text);
      background:
        radial-gradient(circle at top left, rgba(255, 255, 255, 0.88), transparent 30%),
        radial-gradient(circle at top right, rgba(238, 244, 231, 0.82), transparent 34%),
        linear-gradient(180deg, #f7f2ea 0%, #f1ece4 50%, #ebe4d8 100%);
    }

    .receipt-page {
      min-height: 100vh;
      max-width: 100%;
      margin: 0 auto;
      padding: 36px 20px 56px;
      display: flex;
      align-items: flex-start;
      justify-content: center;
      position: relative;
      overflow: hidden;
    }

    .receipt-page::before,
    .receipt-page::after {
      content: "";
      position: absolute;
      border-radius: 999px;
      pointer-events: none;
      opacity: 0.7;
      filter: blur(6px);
    }

    .receipt-page::before {
      width: 180px;
      height: 180px;
      top: 32px;
      left: max(16px, calc(50% - 360px));
      background:
        radial-gradient(circle at 30% 30%, rgba(255, 255, 255, 0.84), rgba(255, 255, 255, 0.2) 56%, transparent 72%),
        radial-gradient(circle at center, rgba(199, 213, 180, 0.4), transparent 72%);
    }

    .receipt-page::after {
      width: 140px;
      height: 140px;
      right: max(16px, calc(50% - 350px));
      bottom: 54px;
      background:
        radial-gradient(circle at 50% 50%, rgba(255, 250, 242, 0.82), rgba(255, 250, 242, 0.16) 56%, transparent 68%),
        radial-gradient(circle at center, rgba(166, 186, 140, 0.32), transparent 72%);
    }

    .receipt-stage {
      width: min(100%, 680px);
      max-width: 680px;
      margin: 0 auto;
      display: grid;
      gap: 18px;
      justify-items: center;
      position: relative;
      z-index: 1;
    }

    .receipt-stage > * {
      width: 100%;
    }

    .receipt-shell {
      max-width: 680px;
      margin: 0 auto;
      width: 100%;
      background:
        linear-gradient(180deg, rgba(255, 255, 255, 0.76), rgba(255, 255, 255, 0.28)),
        var(--receipt-surface);
      border: 1px solid rgba(231, 223, 211, 0.96);
      border-radius: 30px;
      box-shadow:
        var(--receipt-ring),
        var(--receipt-shadow);
      overflow: hidden;
      position: relative;
    }

    .receipt-shell::after {
      content: "";
      position: absolute;
      inset: auto 0 0 0;
      height: 18px;
      background:
        radial-gradient(circle at 9px 0, transparent 9px, rgba(231, 215, 189, 0.85) 9.5px, rgba(231, 215, 189, 0.85) 10px, transparent 10.5px) repeat-x;
      background-size: 18px 18px;
      opacity: 0.7;
      pointer-events: none;
    }

    .receipt-topbar {
      display: grid;
      gap: 14px;
      padding: 24px 24px 20px;
      background:
        radial-gradient(circle at top right, rgba(238, 244, 231, 0.92), transparent 34%),
        linear-gradient(180deg, #fbf8f3, #fffdf9 100%);
      border-bottom: 1px solid rgba(231, 223, 211, 0.96);
    }

    .receipt-brand {
      display: flex;
      align-items: flex-start;
      gap: 16px;
    }

    .receipt-brand img {
      width: 60px;
      height: 60px;
      border-radius: 20px;
      object-fit: cover;
      background: var(--receipt-soft);
      box-shadow: inset 0 0 0 1px rgba(231, 223, 211, 0.96);
      flex: 0 0 auto;
    }

    .receipt-brand-logo {
      width: 60px;
      height: 60px;
      border-radius: 20px;
      object-fit: contain;
      padding: 9px;
      background: var(--receipt-soft);
      box-shadow: inset 0 0 0 1px rgba(231, 223, 211, 0.96);
      flex: 0 0 auto;
    }

    .receipt-brand-copy {
      min-width: 0;
    }

    .receipt-hero {
      display: grid;
      gap: 12px;
      grid-template-columns: minmax(0, 1fr);
    }

    .receipt-hero-main {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 14px;
      flex-wrap: wrap;
    }

    .receipt-brand h1 {
      font-size: clamp(2.15rem, 3vw, 3rem);
      font-weight: 800;
      letter-spacing: -0.05em;
      color: var(--receipt-ink);
      margin: 0;
      text-transform: none;
      line-height: 0.96;
    }

    .receipt-brand p {
      margin: 6px 0 0;
      color: var(--receipt-muted);
      font-size: 15px;
      line-height: 1.65;
      max-width: 480px;
    }

    .receipt-badge {
      display: grid;
      gap: 10px;
      padding: 0;
    }

    .receipt-badge span {
      display: inline-flex;
      align-items: center;
      width: fit-content;
      max-width: 100%;
      padding: 10px 16px;
      border-radius: 999px;
      background: var(--receipt-accent-soft);
      color: var(--receipt-olive-text);
      font-size: 13px;
      font-weight: 800;
      border: 1px solid var(--receipt-accent-border);
      overflow-wrap: anywhere;
      word-break: break-word;
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.6);
    }

    .receipt-badge p,
    .receipt-badge strong {
      margin: 0;
      color: #5f584f;
      font-size: 15px;
      line-height: 1.6;
    }

    .receipt-badge strong {
      display: block;
      color: var(--receipt-ink);
      font-size: 18px;
      font-weight: 800;
    }

    .receipt-body {
      padding: 20px;
      display: grid;
      gap: 18px;
    }

    .receipt-actions {
      display: grid;
      gap: 12px;
      width: 100%;
      max-width: 680px;
      margin: 0 auto;
      padding: 0;
      border-radius: 0;
      background: transparent;
      border: none;
      box-shadow: none;
      align-items: stretch;
      box-sizing: border-box;
    }

    .receipt-actions-row {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 14px;
      width: 100%;
      align-items: stretch;
    }

    .receipt-actions-back {
      display: flex;
      justify-content: center;
      width: 100%;
    }

    .receipt-btn {
      min-height: 56px;
      width: 100%;
      min-width: 0;
      max-width: 100%;
      padding: 0 18px;
      box-sizing: border-box;
      border-radius: 999px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      text-decoration: none;
      font-weight: 800;
      font-size: 15px;
      border: 1px solid var(--receipt-border);
      color: var(--receipt-accent);
      background: linear-gradient(180deg, rgba(255, 253, 249, 0.98), rgba(246, 241, 232, 0.98));
      transition: background-color 0.22s ease, color 0.22s ease, border-color 0.22s ease, transform 0.22s ease, box-shadow 0.22s ease;
      box-shadow: 0 10px 24px rgba(31, 31, 27, 0.06);
    }

    .receipt-btn.primary {
      background: linear-gradient(180deg, color-mix(in srgb, var(--receipt-accent) 88%, #8aa35c 12%), var(--receipt-accent));
      border-color: var(--receipt-accent);
      color: #fdfbf6;
      box-shadow: 0 14px 28px rgba(95, 124, 58, 0.18);
    }

    .receipt-btn:hover {
      transform: translateY(-2px);
      background: linear-gradient(180deg, #fffdf9, #f8f2ea);
      border-color: #d4dcca;
      box-shadow: 0 14px 28px rgba(31, 31, 27, 0.08);
    }

    .receipt-btn.primary:hover {
      background: linear-gradient(180deg, #6a8642, #587437);
      border-color: #587437;
      box-shadow: 0 16px 30px rgba(95, 124, 58, 0.22);
    }

    .receipt-btn:focus-visible {
      outline: 3px solid rgba(111, 132, 88, 0.2);
      outline-offset: 3px;
    }

    .receipt-btn-icon {
      width: 22px;
      height: 22px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      flex: 0 0 auto;
    }

    .receipt-btn-icon svg {
      width: 100%;
      height: 100%;
      stroke: currentColor;
      fill: none;
      stroke-width: 2.2;
      stroke-linecap: round;
      stroke-linejoin: round;
    }

    .receipt-btn.action-tertiary {
      width: min(320px, 100%);
    }

    .receipt-link-row {
      display: flex;
      justify-content: center;
      margin-top: 0;
    }

    .receipt-link-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 6px 2px;
      color: #6a624f;
      text-decoration: none;
      font-size: 13px;
      font-weight: 700;
    }

    .receipt-link-btn:hover {
      color: var(--accent);
    }

    .receipt-stack {
      display: grid;
      gap: 18px;
      grid-template-columns: 1fr;
    }

    .receipt-card {
      border: 1px solid var(--receipt-border);
      border-radius: 26px;
      padding: 22px 22px 20px;
      background: linear-gradient(180deg, rgba(255, 253, 249, 0.98), rgba(250, 246, 239, 0.94));
      box-shadow: var(--receipt-ring), 0 12px 26px rgba(31, 31, 27, 0.045);
    }

    .receipt-card h3 {
      margin: 0;
      font-size: 19px;
      font-weight: 800;
      color: var(--receipt-ink);
      letter-spacing: -0.02em;
      text-transform: none;
      line-height: 1.15;
    }

    .receipt-card-header {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 16px;
      margin-bottom: 18px;
    }

    .receipt-card-header p {
      margin: 6px 0 0;
      color: var(--receipt-muted);
      font-size: 14px;
      line-height: 1.65;
      max-width: 520px;
    }

    .receipt-card-title {
      display: flex;
      align-items: flex-start;
      gap: 12px;
    }

    .receipt-card-title-text {
      display: grid;
      gap: 0;
    }

    .receipt-icon-box {
      width: 46px;
      height: 46px;
      flex: 0 0 46px;
      border-radius: 15px;
      background: linear-gradient(180deg, #f1f6ea 0%, #e8f0de 100%);
      border: 1px solid var(--receipt-accent-border);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      color: #35652f;
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.72);
    }

    .receipt-icon-box svg {
      width: 23px;
      height: 23px;
      stroke: currentColor;
      fill: none;
      stroke-width: 2;
      stroke-linecap: round;
      stroke-linejoin: round;
    }

    .receipt-card-tag {
      flex: 0 0 auto;
      padding: 9px 14px;
      border-radius: 999px;
      background: linear-gradient(180deg, #eef4e7 0%, #e7efdb 100%);
      color: var(--receipt-accent);
      font-size: 11px;
      font-weight: 800;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.72);
      border: 1px solid #d5e3c6;
    }

    .receipt-meta-row {
      display: grid;
      grid-template-columns: minmax(220px, 1fr) minmax(0, auto);
      gap: 16px;
      padding: 16px 0;
      border-bottom: 1px dashed #e7dfd3;
      font-size: 16px;
      align-items: center;
    }

    .receipt-meta-row:last-child {
      border-bottom: none;
      padding-bottom: 0;
    }

    .receipt-meta-row strong {
      color: #5f584f;
      font-weight: 700;
      display: inline-flex;
      align-items: center;
      gap: 12px;
      font-size: 16px;
      min-width: 0;
    }

    .receipt-meta-key {
      display: inline-flex;
      align-items: center;
      gap: 12px;
      min-width: 0;
    }

    .receipt-meta-key > span:last-child {
      white-space: nowrap;
    }

    .receipt-mini-icon {
      width: 36px;
      height: 36px;
      flex: 0 0 36px;
      border-radius: 999px;
      background: linear-gradient(180deg, #eff5e8 0%, #e8f0de 100%);
      border: 1px solid var(--receipt-accent-border);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      color: #38682e;
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
    }

    .receipt-mini-icon svg {
      width: 18px;
      height: 18px;
      stroke: currentColor;
      fill: none;
      stroke-width: 2;
      stroke-linecap: round;
      stroke-linejoin: round;
    }

    .receipt-mini-image {
      width: 36px;
      height: 36px;
      flex: 0 0 36px;
      border-radius: 12px;
      object-fit: cover;
      display: block;
      background: #f4ede2;
      border: 1px solid rgba(231, 215, 189, 0.92);
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.68);
    }

    .receipt-meta-row span {
      color: var(--receipt-ink);
      font-weight: 800;
      text-align: right;
      justify-self: end;
      max-width: none;
      overflow-wrap: anywhere;
      word-break: break-word;
      line-height: 1.45;
      font-size: 16px;
    }

    .receipt-meta-value {
      display: inline-flex;
      align-items: center;
      justify-content: flex-end;
      gap: 12px;
      min-width: 0;
      justify-self: end;
    }

    .receipt-meta-value img {
      width: 46px;
      height: 46px;
      border-radius: 16px;
      object-fit: cover;
      display: block;
      flex: 0 0 46px;
      background: #f4ede2;
      border: 1px solid rgba(231, 215, 189, 0.92);
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.68);
    }

    .receipt-meta-value strong {
      color: var(--receipt-ink);
      font-size: 16px;
      font-weight: 800;
      line-height: 1.4;
      overflow-wrap: anywhere;
      word-break: break-word;
    }

    .receipt-status-chip {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 9px 14px;
      border-radius: 999px;
      background: linear-gradient(180deg, #eef4e7 0%, #e6efda 100%);
      border: 1px solid var(--receipt-accent-border);
      color: var(--receipt-accent);
      font-size: 12px;
      font-weight: 800;
      line-height: 1.3;
      max-width: 100%;
      white-space: nowrap;
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.72);
    }

    .receipt-items {
      border: 1px solid var(--receipt-border);
      border-radius: 26px;
      padding: 0;
      background: linear-gradient(180deg, rgba(255, 253, 249, 0.98), rgba(250, 246, 239, 0.94));
      overflow: hidden;
      box-shadow: var(--receipt-ring), 0 12px 26px rgba(31, 31, 27, 0.045);
    }

    .receipt-items-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      padding: 18px 22px;
      border-bottom: 1px solid rgba(231, 223, 211, 0.96);
      background:
        radial-gradient(circle at top right, rgba(238, 244, 231, 0.9), transparent 38%),
        linear-gradient(180deg, #f7f3eb 0%, #f2ece2 100%);
      color: var(--receipt-ink);
    }

    .receipt-items-title {
      display: inline-flex;
      align-items: center;
      gap: 12px;
      min-width: 0;
    }

    .receipt-items-title h3 {
      margin: 0;
      color: inherit;
      font-size: 18px;
      letter-spacing: 0;
    }

    .receipt-items-title .receipt-mini-icon {
      width: 36px;
      height: 36px;
      flex-basis: 36px;
      background: linear-gradient(180deg, #eef4e7 0%, #e8f0de 100%);
      border-color: var(--receipt-accent-border);
      color: var(--receipt-accent);
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.72);
    }

    .receipt-items-count {
      color: var(--receipt-accent);
      background: rgba(238, 244, 231, 0.92);
      border: 1px solid var(--receipt-accent-border);
      padding: 8px 14px;
      border-radius: 999px;
      font-size: 13px;
      font-weight: 800;
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.4);
    }

    .receipt-item-row {
      display: grid;
      grid-template-columns: minmax(0, 1fr) auto;
      gap: 8px 16px;
      padding: 18px 22px;
      border-bottom: 1px dashed #e7dfd3;
      align-items: center;
    }

    .receipt-item-row:last-child {
      border-bottom: none;
    }

    .receipt-item-main {
      display: flex;
      align-items: center;
      gap: 14px;
      min-width: 0;
    }

    .receipt-item-media {
      width: 56px;
      height: 56px;
      flex: 0 0 56px;
      border-radius: 16px;
      overflow: hidden;
      background: linear-gradient(180deg, #eff6e7 0%, #e9f0de 100%);
      border: 1px solid var(--receipt-accent-border);
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.72);
    }

    .receipt-item-media img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }

    .receipt-item-copy {
      min-width: 0;
      display: grid;
      gap: 5px;
    }

    .receipt-item-name {
      font-weight: 800;
      color: var(--receipt-ink);
      font-size: 16px;
      line-height: 1.4;
      overflow-wrap: anywhere;
      word-break: break-word;
    }

    .receipt-item-meta {
      color: var(--receipt-muted);
      font-size: 14px;
      line-height: 1.6;
      overflow-wrap: anywhere;
      word-break: break-word;
    }

    .receipt-item-total {
      font-weight: 800;
      color: var(--receipt-ink);
      font-size: 16px;
      line-height: 1.2;
      text-align: right;
      white-space: nowrap;
    }

    .receipt-summary {
      width: 100%;
      border: 1px solid var(--receipt-border);
      border-radius: 26px;
      padding: 20px 22px 22px;
      background: linear-gradient(180deg, rgba(255, 253, 249, 0.98), rgba(250, 246, 239, 0.94));
      box-sizing: border-box;
      box-shadow: var(--receipt-ring), 0 12px 26px rgba(31, 31, 27, 0.045);
    }

    .receipt-summary-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 16px;
      padding: 15px 0;
      border-bottom: 1px dashed #e7dfd3;
      font-size: 16px;
      color: #5f584f;
    }

    .receipt-summary-label {
      display: inline-flex;
      align-items: center;
      gap: 12px;
      min-width: 0;
    }

    .receipt-summary-row span,
    .receipt-summary-row strong {
      line-height: 1.5;
    }

    .receipt-summary-row:last-child {
      border-bottom: none;
      padding-bottom: 0;
    }

    .receipt-summary-row.total {
      font-size: 18px;
      font-weight: 800;
      color: var(--receipt-ink);
      margin-top: 14px;
      padding: 17px 20px;
      border-radius: 22px;
      background: linear-gradient(180deg, color-mix(in srgb, var(--receipt-green) 90%, #8aa35c 10%), var(--receipt-green-deep));
      border-bottom: none;
      box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.08),
        0 16px 28px rgba(95, 124, 58, 0.18);
    }

    .receipt-summary-row.total span,
    .receipt-summary-row.total strong {
      color: #fffaf1;
    }

    .receipt-empty {
      padding: 38px 24px;
      text-align: center;
    }

    .receipt-empty h1 {
      margin: 0 0 10px;
      font-size: 28px;
      font-weight: 800;
      color: var(--receipt-text);
    }

    .receipt-empty p {
      margin: 0 0 18px;
      color: var(--receipt-muted);
      line-height: 1.7;
    }

    @media (max-width: 560px) {
      .receipt-page {
        min-height: 100vh;
        padding: 18px 12px 36px;
        display: flex;
      }

      .receipt-page::before {
        width: 128px;
        height: 128px;
        top: 12px;
        left: -18px;
      }

      .receipt-page::after {
        width: 104px;
        height: 104px;
        right: -10px;
        bottom: 34px;
      }

      .receipt-stage {
        max-width: 100%;
        gap: 18px;
      }

      .receipt-shell {
        border-radius: 24px;
      }

      .receipt-actions {
        border-radius: 0;
        padding: 0;
        gap: 12px;
      }

      .receipt-actions-row {
        grid-template-columns: 1fr;
      }

      .receipt-topbar,
      .receipt-body {
        padding-left: 16px;
        padding-right: 16px;
      }

      .receipt-topbar {
        padding-top: 20px;
        padding-bottom: 18px;
      }

      .receipt-body {
        padding-top: 16px;
        padding-bottom: 20px;
        gap: 18px;
      }

      .receipt-brand {
        align-items: flex-start;
      }

      .receipt-brand img {
        width: 54px;
        height: 54px;
        border-radius: 16px;
      }

      .receipt-brand-logo {
        width: 54px;
        height: 54px;
        border-radius: 16px;
      }

      .receipt-brand h1 {
        font-size: 32px;
      }

      .receipt-brand p {
        font-size: 14px;
      }

      .receipt-hero-main {
        gap: 12px;
      }

      .receipt-meta-row {
        grid-template-columns: 1fr;
        gap: 8px;
        align-items: flex-start;
      }

      .receipt-meta-row strong,
      .receipt-meta-row span {
        max-width: 100%;
        text-align: left;
        justify-self: start;
      }

      .receipt-meta-value {
        justify-self: start;
        justify-content: flex-start;
      }

      .receipt-card {
        padding: 18px 16px 16px;
        border-radius: 22px;
      }

      .receipt-card-header {
        flex-direction: column;
        align-items: flex-start;
        margin-bottom: 18px;
      }

      .receipt-card-title {
        gap: 12px;
      }

      .receipt-icon-box {
        width: 46px;
        height: 46px;
        flex-basis: 46px;
        border-radius: 15px;
      }

      .receipt-card h3 {
        font-size: 17px;
      }

      .receipt-card-header p {
        font-size: 14px;
      }

      .receipt-meta-key {
        gap: 10px;
      }

      .receipt-mini-icon {
        width: 36px;
        height: 36px;
        flex-basis: 36px;
      }

      .receipt-mini-image {
        width: 36px;
        height: 36px;
      }

      .receipt-meta-row span {
        font-size: 15px;
      }

      .receipt-btn {
        min-height: 56px;
        border-radius: 18px;
        font-size: 15px;
      }

      .receipt-btn.action-tertiary {
        grid-column: auto;
        width: 100%;
      }

      .receipt-item-row {
        grid-template-columns: 1fr;
        gap: 10px;
        padding: 18px 16px;
      }

      .receipt-item-main {
        align-items: flex-start;
        gap: 12px;
      }

      .receipt-item-media {
        width: 52px;
        height: 52px;
      }

      .receipt-items-header {
        padding: 18px 16px;
      }

      .receipt-items-title h3 {
        font-size: 17px;
      }

      .receipt-item-total {
        text-align: left;
        white-space: normal;
      }

      .receipt-summary {
        padding: 16px 16px 18px;
      }

      .receipt-summary-row.total {
        padding: 16px 18px;
      }
    }
  </style>
</head>
<body>
  <main class="receipt-page">
    <?php if (!$context): ?>
      <div class="receipt-stage">
        <section class="receipt-shell receipt-empty">
          <h1>Receipt Not Found</h1>
          <p>We could not open that receipt right now. Please return to your latest order and try again from a valid receipt link.</p>
          <a class="receipt-btn primary" href="index.php">Back to Home</a>
        </section>
      </div>
    <?php else: ?>
      <div class="receipt-stage">
        <div class="receipt-actions">
          <div class="receipt-actions-row">
            <a class="receipt-btn primary action-main" href="<?= cibo_receipt_h($receiptDownloadUrl) ?>">
              <span class="receipt-btn-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M12 3v12"></path><path d="m7 10 5 5 5-5"></path><path d="M5 21h14"></path></svg>
              </span>
              <span>Download PDF</span>
            </a>
            <a class="receipt-btn action-secondary" href="track.php?order=<?= rawurlencode($orderNumber) ?>">
              <span class="receipt-btn-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M12 21s7-4.35 7-11a7 7 0 1 0-14 0c0 6.65 7 11 7 11Z"></path><circle cx="12" cy="10" r="2.5"></circle></svg>
              </span>
              <span>Track Order</span>
            </a>
          </div>
          <div class="receipt-actions-back">
            <a class="receipt-btn action-tertiary" href="myaccount.php#orders">
              <span class="receipt-btn-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"></path><path d="M9 12h12"></path></svg>
              </span>
              <span>Back to Orders</span>
            </a>
          </div>
        </div>

        <section class="receipt-shell">
          <div class="receipt-topbar">
            <div class="receipt-hero">
              <div class="receipt-hero-main">
                <div class="receipt-brand">
                  <?php if ($receiptLogoImage !== ''): ?>
                    <img class="receipt-brand-logo" src="<?= cibo_receipt_h($receiptLogoImage) ?>" alt="Cibo logo">
                  <?php endif; ?>
                  <div class="receipt-brand-copy">
                    <h1>Receipt</h1>
                    <p>Compact live order invoice from your Cibo purchase.</p>
                  </div>
                </div>

                <div class="receipt-badge">
                  <span><?= cibo_receipt_h((string) ($receipt['receipt_number'] ?? '--')) ?></span>
                </div>
              </div>

              <div>
                <strong>Order #<?= cibo_receipt_h((string) ($order['order_number'] ?? '--')) ?></strong>
                <p>Generated: <?= cibo_receipt_h(cibo_receipt_date((string) ($receipt['generated_at'] ?? ''))) ?></p>
              </div>
            </div>
          </div>

          <div class="receipt-body">
            <div class="receipt-stack">
              <article class="receipt-card">
                <div class="receipt-card-header">
                  <div class="receipt-card-title">
                    <span class="receipt-icon-box" aria-hidden="true">
                      <svg viewBox="0 0 24 24"><path d="M9 3h6"></path><path d="M10 8h4"></path><rect x="5" y="3" width="14" height="18" rx="2"></rect><path d="M9 13h6"></path><path d="M9 17h6"></path></svg>
                    </span>
                    <div class="receipt-card-title-text">
                      <h3>Order Snapshot</h3>
                      <p>Status and payment details from the backend order record.</p>
                    </div>
                  </div>
                  <span class="receipt-card-tag">Live</span>
                </div>
                <div class="receipt-meta-row">
                  <strong class="receipt-meta-key"><img class="receipt-mini-image" src="<?= cibo_receipt_h($restaurantImage) ?>" alt="<?= cibo_receipt_h((string) ($order['restaurant_name'] ?? 'Restaurant')) ?> thumbnail" onerror="this.onerror=null;this.src='<?= cibo_receipt_h($restaurantPlaceholderImage) ?>';"><span>Restaurant</span></strong>
                  <span><?= cibo_receipt_h((string) ($order['restaurant_name'] ?? 'Cibo Order')) ?></span>
                </div>
                <div class="receipt-meta-row">
                  <strong class="receipt-meta-key"><span class="receipt-mini-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M8 2v4"></path><path d="M16 2v4"></path><rect x="3" y="5" width="18" height="16" rx="2"></rect><path d="M3 10h18"></path></svg></span><span>Order Date</span></strong>
                  <span><?= cibo_receipt_h(cibo_receipt_date((string) ($order['placed_at'] ?? $order['created_at'] ?? ''))) ?></span>
                </div>
                <div class="receipt-meta-row">
                  <strong class="receipt-meta-key"><span class="receipt-mini-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M8 7h8"></path><path d="M8 12h8"></path><path d="M8 17h5"></path><rect x="4" y="3" width="16" height="18" rx="2"></rect></svg></span><span>Order Status</span></strong>
                  <span class="receipt-status-chip"><?= cibo_receipt_h($orderStatusLabel) ?></span>
                </div>
                <div class="receipt-meta-row">
                  <strong class="receipt-meta-key"><span class="receipt-mini-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="12" rx="2"></rect><path d="M7 12h.01"></path><path d="M17 12h.01"></path></svg></span><span>Payment Method</span></strong>
                  <span><?= cibo_receipt_h(cibo_payment_method_label((string) ($order['payment_method'] ?? ''))) ?></span>
                </div>
                <div class="receipt-meta-row">
                  <strong class="receipt-meta-key"><span class="receipt-mini-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="7" width="18" height="10" rx="2"></rect><path d="M7 12h10"></path></svg></span><span>Payment Status</span></strong>
                  <span class="receipt-status-chip"><?= cibo_receipt_h($paymentStatusLabel) ?></span>
                </div>
              </article>

              <article class="receipt-card">
                <div class="receipt-card-header">
                  <div class="receipt-card-title">
                    <span class="receipt-icon-box" aria-hidden="true">
                      <svg viewBox="0 0 24 24"><path d="M12 21s7-4.35 7-11a7 7 0 1 0-14 0c0 6.65 7 11 7 11Z"></path><circle cx="12" cy="10" r="2.5"></circle></svg>
                    </span>
                    <div class="receipt-card-title-text">
                      <h3>Delivery Details</h3>
                      <p>Customer and destination details exactly as stored on the order.</p>
                    </div>
                  </div>
                  <span class="receipt-card-tag">Dropoff</span>
                </div>
                <div class="receipt-meta-row">
                  <strong class="receipt-meta-key"><span class="receipt-mini-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M20 21a8 8 0 0 0-16 0"></path><circle cx="12" cy="7" r="4"></circle></svg></span><span>Customer</span></strong>
                  <span><?= cibo_receipt_h((string) ($order['user_name'] ?? '--')) ?></span>
                </div>
                <div class="receipt-meta-row">
                  <strong class="receipt-meta-key"><span class="receipt-mini-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.9.32 1.78.59 2.62a2 2 0 0 1-.45 2.11L8 9.91a16 16 0 0 0 6.09 6.09l1.46-1.25a2 2 0 0 1 2.11-.45c.84.27 1.72.47 2.62.59A2 2 0 0 1 22 16.92Z"></path></svg></span><span>Phone</span></strong>
                  <span><?= cibo_receipt_h((string) ($order['customer_phone'] ?? '--')) ?></span>
                </div>
                <div class="receipt-meta-row">
                  <strong class="receipt-meta-key"><span class="receipt-mini-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M3 10.5 12 3l9 7.5"></path><path d="M5 9.5V21h14V9.5"></path><path d="M9 21v-6h6v6"></path></svg></span><span>Address</span></strong>
                  <span><?= cibo_receipt_h((string) ($order['delivery_address'] ?? '--')) ?></span>
                </div>
              </article>
            </div>

            <section class="receipt-items" aria-labelledby="receipt-items-title">
              <div class="receipt-items-header">
                <div class="receipt-items-title">
                  <span class="receipt-mini-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M6 7h12l-1 13H7L6 7Z"></path><path d="M9 7a3 3 0 0 1 6 0"></path></svg>
                  </span>
                  <h3 id="receipt-items-title">Items</h3>
                </div>
                <span class="receipt-items-count"><?= cibo_receipt_h((string) count($items)) ?> item<?= count($items) === 1 ? '' : 's' ?></span>
              </div>
              <?php foreach ($items as $item): ?>
                <?php
                  $quantity = (int) ($item['quantity'] ?? 1);
                  $price = (float) ($item['price'] ?? 0);
                  $lineTotal = (float) ($item['line_total'] ?? ($price * $quantity));
                ?>
                <div class="receipt-item-row">
                  <div class="receipt-item-main">
                    <div class="receipt-item-media">
                      <img src="<?= cibo_receipt_h((string) ($item['receipt_image'] ?? $foodPlaceholderImage)) ?>" alt="<?= cibo_receipt_h((string) ($item['name'] ?? 'Food item')) ?> image" loading="lazy" onerror="this.onerror=null;this.src='<?= cibo_receipt_h($foodPlaceholderImage) ?>';">
                    </div>
                    <div class="receipt-item-copy">
                      <div class="receipt-item-name"><?= cibo_receipt_h((string) ($item['name'] ?? 'Item')) ?></div>
                      <div class="receipt-item-meta">Qty <?= cibo_receipt_h((string) $quantity) ?> x <?= cibo_receipt_h(cibo_receipt_money($price)) ?></div>
                    </div>
                  </div>
                  <div class="receipt-item-total"><?= cibo_receipt_h(cibo_receipt_money($lineTotal)) ?></div>
                </div>
              <?php endforeach; ?>
            </section>

            <section class="receipt-summary">
              <div class="receipt-summary-row">
                <span class="receipt-summary-label"><span class="receipt-mini-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M8 7h8"></path><path d="M8 12h8"></path><path d="M8 17h5"></path><rect x="4" y="3" width="16" height="18" rx="2"></rect></svg></span><span>Subtotal</span></span>
                <strong><?= cibo_receipt_h(cibo_receipt_money((float) ($summary['subtotal'] ?? 0))) ?></strong>
              </div>
              <div class="receipt-summary-row">
                <span class="receipt-summary-label"><span class="receipt-mini-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="6" r="2"></circle><circle cx="6" cy="18" r="2"></circle><circle cx="18" cy="18" r="2"></circle><path d="M12 8v4"></path><path d="M10.2 13 7.8 16"></path><path d="M13.8 13 16.2 16"></path></svg></span><span>Delivery Fee</span></span>
                <strong><?= cibo_receipt_h(cibo_receipt_money((float) ($summary['delivery_fee'] ?? 0))) ?></strong>
              </div>
              <div class="receipt-summary-row">
                <span class="receipt-summary-label"><span class="receipt-mini-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M19 5 5 19"></path><circle cx="7" cy="7" r="2"></circle><circle cx="17" cy="17" r="2"></circle></svg></span><span>Tax</span></span>
                <strong><?= cibo_receipt_h(cibo_receipt_money((float) ($summary['tax_amount'] ?? 0))) ?></strong>
              </div>
              <?php if ((float) ($summary['discount_amount'] ?? 0) > 0): ?>
                <div class="receipt-summary-row">
                  <span class="receipt-summary-label"><span class="receipt-mini-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m9 12 2 2 4-4"></path><circle cx="12" cy="12" r="9"></circle></svg></span><span>Discount</span></span>
                  <strong>-<?= cibo_receipt_h(cibo_receipt_money((float) ($summary['discount_amount'] ?? 0))) ?></strong>
                </div>
              <?php endif; ?>
              <div class="receipt-summary-row total"><span>Total</span><strong><?= cibo_receipt_h(cibo_receipt_money((float) ($summary['total_amount'] ?? 0))) ?></strong></div>
            </section>
          </div>
        </section>
      </div>
    <?php endif; ?>
  </main>
</body>
</html>
