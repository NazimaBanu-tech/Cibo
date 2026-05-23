<?php

declare(strict_types=1);

function cibo_receipt_pdf_safe_text(string $text): string
{
    $normalized = trim($text);

    if ($normalized === '') {
        return '';
    }

    $converted = @iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $normalized);
    $safe = $converted !== false ? $converted : $normalized;
    $safe = preg_replace('/[^\x20-\x7E\xA0-\xFF]/', '', $safe) ?? $safe;

    return str_replace(
        ['\\', '(', ')'],
        ['\\\\', '\\(', '\\)'],
        $safe
    );
}

function cibo_receipt_pdf_money(float $amount): string
{
    return 'Rs. ' . number_format($amount, 2);
}

function cibo_receipt_pdf_date(string $value): string
{
    $timestamp = strtotime($value);

    if ($timestamp === false) {
        return $value !== '' ? $value : '--';
    }

    return date('d M Y, h:i A', $timestamp);
}

function cibo_receipt_pdf_multiline(string $prefix, string $value, int $limit = 60): array
{
    $text = trim($prefix . $value);

    if ($text === '') {
        return [];
    }

    $chunks = wordwrap($text, $limit, "\n", true);
    return preg_split('/\r?\n/', $chunks) ?: [$text];
}

function cibo_receipt_pdf_bytes(array $context): string
{
    $order = is_array($context['order'] ?? null) ? $context['order'] : [];
    $receipt = is_array($context['receipt'] ?? null) ? $context['receipt'] : [];
    $summary = is_array($context['summary'] ?? null) ? $context['summary'] : [];
    $items = is_array($order['items'] ?? null) ? $order['items'] : [];

    $pages = [];
    $currentPage = -1;
    $y = 804.0;

    $push = static function (string $command) use (&$pages, &$currentPage): void {
        $pages[$currentPage][] = $command;
    };

    $addLine = static function (string $text, float $x, float $yPos, int $size = 11) use ($push): void {
        $safeText = cibo_receipt_pdf_safe_text($text);
        $push(sprintf('BT /F1 %d Tf 1 0 0 1 %.2F %.2F Tm (%s) Tj ET', $size, $x, $yPos, $safeText));
    };

    $rule = static function (float $x1, float $y1, float $x2, float $y2) use ($push): void {
        $push(sprintf('%.2F %.2F m %.2F %.2F l S', $x1, $y1, $x2, $y2));
    };

    $startPage = static function (bool $continued = false) use (&$pages, &$currentPage, &$y, $addLine, $rule, $receipt, $order): void {
        $pages[] = [];
        $currentPage = count($pages) - 1;
        $y = 804.0;

        $addLine($continued ? 'Cibo Receipt (continued)' : 'Cibo Receipt', 48, $y, 22);
        $addLine('Fresh food order summary', 48, $y - 18, 11);
        $addLine('Receipt No: ' . (string) ($receipt['receipt_number'] ?? '--'), 360, $y, 10);
        $addLine('Order ID: ' . (string) ($order['order_number'] ?? '--'), 360, $y - 16, 10);
        $rule(48, $y - 28, 548, $y - 28);
        $y -= 52;
    };

    $ensureSpace = static function (float $requiredHeight) use (&$y, $startPage): void {
        if (($y - $requiredHeight) < 72) {
            $startPage(true);
        }
    };

    $writeWrapped = static function (float $x, string $text, int $limit, int $size, float $lineHeight) use (&$y, $addLine, $ensureSpace): void {
        $lines = cibo_receipt_pdf_multiline('', $text, $limit);

        foreach ($lines as $line) {
            $ensureSpace($lineHeight + 8);
            $addLine($line, $x, $y, $size);
            $y -= $lineHeight;
        }
    };

    $startPage(false);

    $addLine('Restaurant', 48, $y, 10);
    $writeWrapped(130, (string) ($order['restaurant_name'] ?? 'Cibo Order'), 30, 12, 14);
    $metaTopY = $y + 14;
    $addLine('Order Date', 330, $metaTopY, 10);
    $writeWrapped(410, cibo_receipt_pdf_date((string) ($order['placed_at'] ?? $order['created_at'] ?? '--')), 20, 11, 14);

    $ensureSpace(34);
    $y -= 8;
    $addLine('Payment', 48, $y, 10);
    $writeWrapped(130, cibo_payment_method_label((string) ($order['payment_method'] ?? '')), 28, 12, 14);
    $metaTopY = $y + 14;
    $addLine('Payment Status', 330, $metaTopY, 10);
    $writeWrapped(430, (string) ($order['payment_status_label'] ?? $order['payment_status'] ?? '--'), 16, 11, 14);

    $ensureSpace(28);
    $y -= 8;
    $addLine('Order Status', 48, $y, 10);
    $writeWrapped(130, (string) ($order['order_status_label'] ?? $order['order_status'] ?? '--'), 24, 12, 14);

    $ensureSpace(36);
    $y -= 10;
    $rule(48, $y, 548, $y);

    $ensureSpace(72);
    $y -= 20;
    $addLine('Delivery Details', 48, $y, 12);
    $y -= 18;

    foreach (cibo_receipt_pdf_multiline('Customer: ', (string) ($order['user_name'] ?? '--'), 78) as $textLine) {
        $ensureSpace(20);
        $addLine($textLine, 48, $y, 10);
        $y -= 14;
    }

    foreach (cibo_receipt_pdf_multiline('Phone: ', (string) ($order['customer_phone'] ?? '--'), 78) as $textLine) {
        $ensureSpace(20);
        $addLine($textLine, 48, $y, 10);
        $y -= 14;
    }

    foreach (cibo_receipt_pdf_multiline('Address: ', (string) ($order['delivery_address'] ?? '--'), 78) as $textLine) {
        $ensureSpace(20);
        $addLine($textLine, 48, $y, 10);
        $y -= 14;
    }

    $ensureSpace(36);
    $y -= 8;
    $rule(48, $y, 548, $y);

    $ensureSpace(48);
    $y -= 20;
    $addLine('Item', 48, $y, 11);
    $addLine('Qty', 316, $y, 11);
    $addLine('Price', 380, $y, 11);
    $addLine('Subtotal', 470, $y, 11);
    $y -= 10;
    $rule(48, $y, 548, $y);
    $y -= 18;

    foreach ($items as $item) {
        $nameLines = cibo_receipt_pdf_multiline('', (string) ($item['name'] ?? 'Item'), 42);
        $quantity = (int) ($item['quantity'] ?? 1);
        $price = (float) ($item['price'] ?? 0);
        $lineTotal = (float) ($item['line_total'] ?? ($price * $quantity));
        $requiredHeight = (count($nameLines) * 14) + 14;

        $ensureSpace($requiredHeight + 10);

        foreach ($nameLines as $index => $textLine) {
            $addLine($textLine, 48, $y, 10);

            if ($index === 0) {
                $addLine((string) $quantity, 320, $y, 10);
                $addLine(cibo_receipt_pdf_money($price), 380, $y, 10);
                $addLine(cibo_receipt_pdf_money($lineTotal), 470, $y, 10);
            }

            $y -= 14;
        }

        $y -= 4;
    }

    $ensureSpace(120);
    $y -= 8;
    $rule(316, $y, 548, $y);
    $y -= 18;

    $addLine('Subtotal', 360, $y, 10);
    $addLine(cibo_receipt_pdf_money((float) ($summary['subtotal'] ?? 0)), 470, $y, 10);
    $y -= 16;

    $addLine('Delivery Fee', 360, $y, 10);
    $addLine(cibo_receipt_pdf_money((float) ($summary['delivery_fee'] ?? 0)), 470, $y, 10);
    $y -= 16;

    $addLine('Tax', 360, $y, 10);
    $addLine(cibo_receipt_pdf_money((float) ($summary['tax_amount'] ?? 0)), 470, $y, 10);
    $y -= 16;

    $discountAmount = (float) ($summary['discount_amount'] ?? 0);
    if ($discountAmount > 0) {
        $addLine('Discount', 360, $y, 10);
        $addLine('-' . cibo_receipt_pdf_money($discountAmount), 470, $y, 10);
        $y -= 16;
    }

    $rule(316, $y, 548, $y);
    $y -= 18;
    $addLine('Total', 360, $y, 12);
    $addLine(cibo_receipt_pdf_money((float) ($summary['total_amount'] ?? 0)), 470, $y, 12);
    $y -= 30;

    $ensureSpace(32);
    $addLine('Thank you for ordering with Cibo.', 48, $y, 11);
    $addLine('This receipt is generated from the live order record.', 48, $y - 16, 10);

    $objects = [];
    $objects[] = '1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj';

    $pageObjectNumbers = [];
    $contentObjectNumbers = [];

    $nextObjectNumber = 3;

    foreach ($pages as $pageCommands) {
        $pageObjectNumbers[] = $nextObjectNumber;
        $contentObjectNumbers[] = $nextObjectNumber + 1;
        $nextObjectNumber += 2;
    }

    $kids = implode(' ', array_map(static fn (int $number): string => $number . ' 0 R', $pageObjectNumbers));
    $objects[] = '2 0 obj << /Type /Pages /Kids [' . $kids . '] /Count ' . count($pageObjectNumbers) . ' >> endobj';

    foreach ($pages as $index => $pageCommands) {
        $pageNumber = $pageObjectNumbers[$index];
        $contentNumber = $contentObjectNumbers[$index];
        $stream = "0.85 w\n" . implode("\n", $pageCommands) . "\n";
        $streamLength = strlen($stream);

        $objects[] = $pageNumber . ' 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents ' . $contentNumber . ' 0 R /Resources << /Font << /F1 ' . ($nextObjectNumber) . ' 0 R >> >> >> endobj';
        $objects[] = $contentNumber . " 0 obj << /Length {$streamLength} >> stream\n{$stream}endstream endobj";
    }

    $objects[] = $nextObjectNumber . ' 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj';

    $pdf = "%PDF-1.4\n";
    $offsets = [0];

    foreach ($objects as $object) {
        $offsets[] = strlen($pdf);
        $pdf .= $object . "\n";
    }

    $xrefOffset = strlen($pdf);
    $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
    $pdf .= "0000000000 65535 f \n";

    for ($index = 1; $index <= count($objects); $index += 1) {
        $pdf .= sprintf("%010d 00000 n \n", $offsets[$index]);
    }

    $pdf .= 'trailer << /Size ' . (count($objects) + 1) . ' /Root 1 0 R >>' . "\n";
    $pdf .= "startxref\n{$xrefOffset}\n%%EOF";

    return $pdf;
}
