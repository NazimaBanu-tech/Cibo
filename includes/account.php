<?php

declare(strict_types=1);

require_once __DIR__ . '/user-auth.php';

function cibo_require_current_user(): array
{
    $user = cibo_current_user();

    if (!is_array($user) || (int) ($user['id'] ?? 0) <= 0) {
        throw new CiboHttpException('Not authenticated.', 401);
    }

    return $user;
}

function cibo_current_user_profile(): array
{
    $sessionUser = cibo_require_current_user();
    $db = cibo_app_db();

    if (!$db) {
        throw new CiboHttpException('User database is not ready yet. Please verify the cibo_db_v2 connection.', 500);
    }

    $record = cibo_find_user_by_id((int) $sessionUser['id']);

    if (!$record) {
        cibo_user_logout();
        throw new CiboHttpException('Not authenticated.', 401);
    }

    $payload = cibo_user_session_payload($record);
    $_SESSION['user'] = $payload;

    return $payload;
}

function cibo_update_current_user_profile(array $input): array
{
    $sessionUser = cibo_require_current_user();
    $db = cibo_app_db();

    if (!$db) {
        throw new CiboHttpException('User database is not ready yet. Please verify the cibo_db_v2 connection.', 500);
    }

    $userId = (int) $sessionUser['id'];
    $name = cibo_normalize_single_line((string) ($input['name'] ?? ''), 120);
    $email = strtolower(trim((string) ($input['email'] ?? '')));
    $phone = cibo_normalize_phone_value((string) ($input['phone'] ?? ''));

    if ($name === '' || $email === '') {
        throw new CiboHttpException('Name and email are required.', 422);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new CiboHttpException('Please enter a valid email address.', 422);
    }

    if ($phone !== '' && strlen($phone) !== 10) {
        throw new CiboHttpException('Phone number must be 10 digits.', 422);
    }

    $duplicateStatement = $db->prepare('SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1');

    if (!$duplicateStatement) {
        throw new CiboHttpException('Unable to validate the email address.', 500);
    }

    $duplicateStatement->bind_param('si', $email, $userId);
    $duplicateStatement->execute();
    $duplicate = $duplicateStatement->get_result()?->fetch_assoc();
    $duplicateStatement->close();

    if ($duplicate) {
        throw new CiboHttpException('An account with this email already exists.', 422);
    }

    $statement = $db->prepare('UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?');

    if (!$statement) {
        throw new CiboHttpException('Unable to update the account.', 500);
    }

    $statement->bind_param('sssi', $name, $email, $phone, $userId);
    $statement->execute();
    $statement->close();

    $updatedUser = cibo_current_user_profile();
    $_SESSION['user'] = $updatedUser;

    return $updatedUser;
}

function cibo_address_full_text(array $payload): string
{
    $address = cibo_normalize_multiline_text((string) ($payload['address'] ?? $payload['full_address'] ?? ''), 500);
    $landmark = cibo_normalize_single_line((string) ($payload['landmark'] ?? ''), 120);
    $name = cibo_normalize_single_line((string) ($payload['name'] ?? ''), 120);
    $phone = cibo_normalize_phone_value((string) ($payload['phone'] ?? ''));

    $lines = [];

    if ($address !== '') {
        $lines[] = $address;
    }

    if ($landmark !== '') {
        $lines[] = 'Landmark: ' . $landmark;
    }

    if ($name !== '') {
        $lines[] = 'Recipient: ' . $name;
    }

    if ($phone !== '') {
        $lines[] = 'Phone: ' . $phone;
    }

    return trim(implode("\n", $lines));
}

function cibo_normalize_address_match_value(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[.,-]+/', ' ', $value) ?? $value;
    $value = preg_replace('/\s+/', ' ', $value) ?? $value;
    return trim($value);
}

function cibo_address_from_record(array $record): array
{
    $address = trim((string) ($record['address_line'] ?? ''));
    $landmark = trim((string) ($record['landmark'] ?? ''));
    $name = trim((string) ($record['recipient_name'] ?? ''));
    $phone = trim((string) ($record['recipient_phone'] ?? ''));
    $fullAddress = $address;

    if ($landmark !== '') {
        $fullAddress .= "\nLandmark: " . $landmark;
    }

    if ($name !== '') {
        $fullAddress .= "\nRecipient: " . $name;
    }

    if ($phone !== '') {
        $fullAddress .= "\nPhone: " . $phone;
    }

    return [
        'id' => (int) ($record['id'] ?? 0),
        'type' => trim((string) ($record['label'] ?? '')) ?: 'Home',
        'label' => trim((string) ($record['label'] ?? '')) ?: 'Home',
        'name' => $name,
        'phone' => $phone,
        'address' => $address,
        'landmark' => $landmark,
        'city' => trim((string) ($record['city'] ?? '')),
        'state' => trim((string) ($record['state'] ?? '')),
        'pincode' => trim((string) ($record['pincode'] ?? '')),
        'postal_code' => trim((string) ($record['pincode'] ?? '')),
        'full_address' => $fullAddress,
        'is_default' => (bool) ($record['is_default'] ?? false),
        'created_at' => $record['created_at'] ?? null,
    ];
}

function cibo_fetch_current_user_addresses(): array
{
    $user = cibo_require_current_user();
    $db = cibo_app_db();

    if (!$db) {
        return [];
    }

    $statement = $db->prepare('
        SELECT id, label, recipient_name, recipient_phone, address_line, landmark, city, state, pincode, is_default, created_at
        FROM addresses
        WHERE user_id = ?
        ORDER BY created_at DESC, id DESC
    ');

    if (!$statement) {
        return [];
    }

    $userId = (int) $user['id'];
    $statement->bind_param('i', $userId);
    $statement->execute();
    $rows = $statement->get_result()?->fetch_all(MYSQLI_ASSOC) ?? [];
    $statement->close();

    return array_map('cibo_address_from_record', $rows);
}

function cibo_save_current_user_address(array $input): array
{
    $user = cibo_require_current_user();
    $db = cibo_app_db();

    if (!$db) {
        throw new RuntimeException('Address database is not ready yet. Import database/schema.sql first.');
    }

    $userId = (int) $user['id'];
    $addressId = (int) ($input['id'] ?? 0);
    $label = cibo_normalize_single_line((string) ($input['type'] ?? $input['label'] ?? 'Home'), 40) ?: 'Home';
    $recipientName = cibo_normalize_single_line((string) ($input['name'] ?? ''), 120);
    $recipientPhone = cibo_normalize_phone_value((string) ($input['phone'] ?? ''));
    $address = cibo_normalize_multiline_text((string) ($input['address'] ?? ''), 255);
    $landmark = cibo_normalize_single_line((string) ($input['landmark'] ?? ''), 120);
    $city = cibo_normalize_single_line((string) ($input['city'] ?? ''), 80);
    $state = cibo_normalize_single_line((string) ($input['state'] ?? ''), 80);
    $postalCode = cibo_normalize_postal_code_value((string) ($input['pincode'] ?? $input['postal_code'] ?? ''));
    $isDefault = (bool) ($input['is_default'] ?? false);

    if ($address === '') {
        throw new RuntimeException('Address is required.');
    }

    if ($city === '') {
        throw new RuntimeException('City is required.');
    }

    if ($recipientPhone !== '' && strlen($recipientPhone) !== 10) {
        throw new RuntimeException('Phone number must be 10 digits.');
    }

    if ($postalCode === '') {
        throw new RuntimeException('Pincode is required.');
    }

    if ($postalCode !== '' && strlen($postalCode) !== 6) {
        throw new RuntimeException('Pincode must be 6 digits.');
    }

    if ($isDefault) {
        $resetDefaultStatement = $db->prepare('UPDATE addresses SET is_default = 0 WHERE user_id = ?');

        if ($resetDefaultStatement) {
            $resetDefaultStatement->bind_param('i', $userId);
            $resetDefaultStatement->execute();
            $resetDefaultStatement->close();
        }
    }

    if ($addressId > 0) {
        $statement = $db->prepare('
            UPDATE addresses
            SET label = ?, recipient_name = ?, recipient_phone = ?, address_line = ?, landmark = ?, city = ?, state = ?, pincode = ?, is_default = ?
            WHERE id = ? AND user_id = ?
        ');

        if (!$statement) {
            throw new RuntimeException('Unable to update the address.');
        }

        $defaultFlag = $isDefault ? 1 : 0;
        $statement->bind_param('ssssssssiii', $label, $recipientName, $recipientPhone, $address, $landmark, $city, $state, $postalCode, $defaultFlag, $addressId, $userId);
        $statement->execute();
        $statement->close();
    } else {
        $statement = $db->prepare('
            INSERT INTO addresses (user_id, label, recipient_name, recipient_phone, address_line, landmark, city, state, pincode, is_default)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');

        if (!$statement) {
            throw new RuntimeException('Unable to save the address.');
        }

        $defaultFlag = $isDefault ? 1 : 0;
        $statement->bind_param('issssssssi', $userId, $label, $recipientName, $recipientPhone, $address, $landmark, $city, $state, $postalCode, $defaultFlag);
        $statement->execute();
        $addressId = (int) $statement->insert_id;
        $statement->close();
    }

    $lookupStatement = $db->prepare('
        SELECT id, label, recipient_name, recipient_phone, address_line, landmark, city, state, pincode, is_default, created_at
        FROM addresses
        WHERE id = ? AND user_id = ?
        LIMIT 1
    ');

    if (!$lookupStatement) {
        throw new RuntimeException('Address was saved, but it could not be loaded again.');
    }

    $lookupStatement->bind_param('ii', $addressId, $userId);
    $lookupStatement->execute();
    $record = $lookupStatement->get_result()?->fetch_assoc();
    $lookupStatement->close();

    if (!$record) {
        throw new RuntimeException('Address was saved, but it could not be loaded again.');
    }

    return cibo_address_from_record($record);
}

function cibo_delete_current_user_address(int $addressId): void
{
    $user = cibo_require_current_user();
    $db = cibo_app_db();

    if (!$db) {
        throw new RuntimeException('Address database is not ready yet. Import database/schema.sql first.');
    }

    if ($addressId <= 0) {
        throw new RuntimeException('Please select a valid address.');
    }

    $statement = $db->prepare('DELETE FROM addresses WHERE id = ? AND user_id = ?');

    if (!$statement) {
        throw new RuntimeException('Unable to delete the address.');
    }

    $userId = (int) $user['id'];
    $statement->bind_param('ii', $addressId, $userId);
    $statement->execute();
    $statement->close();
}

function cibo_find_matching_user_addresses(mysqli $db, int $userId, array $customer): array
{
    if ($userId <= 0) {
        return [];
    }

    $address = cibo_normalize_address_match_value((string) ($customer['address'] ?? ''));
    $city = cibo_normalize_address_match_value((string) ($customer['city'] ?? ''));
    $postalCode = preg_replace('/\D+/', '', (string) ($customer['pincode'] ?? $customer['postal_code'] ?? '')) ?? '';

    if ($address === '' || $postalCode === '') {
        return [];
    }

    $statement = $db->prepare('
        SELECT id, label, recipient_name, recipient_phone, address_line, landmark, city, state, pincode, is_default, created_at
        FROM addresses
        WHERE user_id = ?
        ORDER BY created_at DESC, id DESC
    ');

    if (!$statement) {
        return [];
    }

    $statement->bind_param('i', $userId);
    $statement->execute();
    $rows = $statement->get_result()?->fetch_all(MYSQLI_ASSOC) ?? [];
    $statement->close();

    $matches = [];

    foreach ($rows as $row) {
        $record = cibo_address_from_record($row);
        $recordAddress = cibo_normalize_address_match_value((string) ($record['address'] ?? $record['full_address'] ?? ''));
        $recordCity = cibo_normalize_address_match_value((string) ($record['city'] ?? ''));
        $recordPostalCode = preg_replace('/\D+/', '', (string) ($record['pincode'] ?? $record['postal_code'] ?? '')) ?? '';

        if ($recordAddress !== $address || $recordCity !== $city || $recordPostalCode !== $postalCode) {
            continue;
        }

        $matches[] = $row;
    }

    return $matches;
}

function cibo_sync_order_address_for_user(mysqli $db, int $userId, array $customer): void
{
    if ($userId <= 0) {
        return;
    }

    $address = trim((string) ($customer['address'] ?? ''));
    $city = trim((string) ($customer['city'] ?? ''));
    $postalCode = trim((string) ($customer['pincode'] ?? $customer['postal_code'] ?? ''));

    if ($address === '') {
        return;
    }

    $matches = cibo_find_matching_user_addresses($db, $userId, $customer);
    $state = trim((string) ($customer['state'] ?? ''));
    $recipientName = trim((string) ($customer['name'] ?? ''));
    $recipientPhone = trim((string) ($customer['phone'] ?? ''));
    $landmark = trim((string) ($customer['landmark'] ?? ''));

    if ($matches) {
        $primaryMatch = null;
        $duplicateRecentIds = [];

        foreach ($matches as $match) {
            $label = trim((string) ($match['label'] ?? ''));

            if ($label === 'Recent') {
                if ($primaryMatch === null) {
                    $primaryMatch = $match;
                } else {
                    $duplicateRecentIds[] = (int) ($match['id'] ?? 0);
                }
            } elseif ($primaryMatch === null) {
                $primaryMatch = $match;
            }
        }

        if ($primaryMatch !== null && trim((string) ($primaryMatch['label'] ?? '')) === 'Recent') {
            $recentId = (int) ($primaryMatch['id'] ?? 0);

            if ($recentId > 0) {
                $updateStatement = $db->prepare('
                    UPDATE addresses
                    SET recipient_name = ?, recipient_phone = ?, address_line = ?, landmark = ?, city = ?, state = ?, pincode = ?
                    WHERE id = ? AND user_id = ?
                ');

                if ($updateStatement) {
                    $updateStatement->bind_param('sssssssii', $recipientName, $recipientPhone, $address, $landmark, $city, $state, $postalCode, $recentId, $userId);
                    $updateStatement->execute();
                    $updateStatement->close();
                }
            }
        } else {
            foreach ($matches as $match) {
                if (trim((string) ($match['label'] ?? '')) === 'Recent') {
                    $duplicateRecentIds[] = (int) ($match['id'] ?? 0);
                }
            }
        }

        $duplicateRecentIds = array_values(array_filter(array_unique($duplicateRecentIds)));

        if ($duplicateRecentIds) {
            $placeholders = implode(', ', array_fill(0, count($duplicateRecentIds), '?'));
            $deleteStatement = $db->prepare("
                DELETE FROM addresses
                WHERE user_id = ?
                  AND label = 'Recent'
                  AND id IN ({$placeholders})
            ");

            if ($deleteStatement) {
                $bindTypes = 'i' . str_repeat('i', count($duplicateRecentIds));
                $bindValues = array_merge([$userId], $duplicateRecentIds);
                $deleteStatement->bind_param($bindTypes, ...$bindValues);
                $deleteStatement->execute();
                $deleteStatement->close();
            }
        }

        return;
    }

    $insertStatement = $db->prepare('
        INSERT INTO addresses (user_id, label, recipient_name, recipient_phone, address_line, landmark, city, state, pincode, is_default)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0)
    ');

    if (!$insertStatement) {
        throw new RuntimeException('Unable to save the delivery address.');
    }

    $label = 'Recent';
    $insertStatement->bind_param('issssssss', $userId, $label, $recipientName, $recipientPhone, $address, $landmark, $city, $state, $postalCode);
    $insertStatement->execute();
    $insertStatement->close();
}
