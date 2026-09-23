<?php
require_once __DIR__ . '/functions.php';

/**
 * Calculates a 0-100 similarity score between a lost item and a found item.
 * Weighting:
 *   - Category match        -> 30 pts (required-ish, big weight)
 *   - Name / keyword overlap -> 30 pts
 *   - Color match            -> 10 pts
 *   - Brand match             -> 10 pts
 *   - Location similarity     -> 10 pts
 *   - Date logic (found after lost, within 30 days) -> 10 pts
 */
function calculate_match_score(array $lost, array $found) {
    $score = 0;

    // 1) Category (30)
    if (strcasecmp($lost['category'], $found['category']) === 0) {
        $score += 30;
    }

    // 2) Name / description keyword overlap (30)
    $lostWords  = extract_keywords($lost['item_name'] . ' ' . $lost['description']);
    $foundWords = extract_keywords($found['item_name'] . ' ' . $found['description']);
    if (count($lostWords) > 0) {
        $common   = array_intersect($lostWords, $foundWords);
        $overlap  = count($common) / max(count($lostWords), 1);
        $score   += round($overlap * 30);
    }

    // 3) Color (10)
    if (!empty($lost['color']) && !empty($found['color']) &&
        strcasecmp(trim($lost['color']), trim($found['color'])) === 0) {
        $score += 10;
    }

    // 4) Brand (10)
    if (!empty($lost['brand']) && !empty($found['brand']) &&
        strcasecmp(trim($lost['brand']), trim($found['brand'])) === 0) {
        $score += 10;
    }

    // 5) Location similarity - simple substring / word overlap (10)
    $lostLoc  = extract_keywords($lost['location']);
    $foundLoc = extract_keywords($found['location']);
    if (count($lostLoc) > 0) {
        $commonLoc = array_intersect($lostLoc, $foundLoc);
        if (count($commonLoc) > 0) {
            $score += 10;
        }
    }

    // 6) Date logic (10) - found date should be on/after lost date, within 30 days
    $lostDate  = strtotime($lost['date_lost']);
    $foundDate = strtotime($found['date_found']);
    if ($foundDate >= $lostDate) {
        $diffDays = ($foundDate - $lostDate) / 86400;
        if ($diffDays <= 30) {
            $score += 10;
        } elseif ($diffDays <= 60) {
            $score += 5;
        }
    }

    return min($score, 100);
}

function extract_keywords($text) {
    $text  = strtolower($text);
    $text  = preg_replace('/[^a-z0-9\s]/', ' ', $text);
    $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);

    $stopwords = ['the','a','an','is','was','my','i','it','in','on','at','of','with','and','to','for','lost','found','near'];
    return array_values(array_diff($words, $stopwords));
}

/**
 * Runs when a NEW LOST item is submitted: check it against all open found items.
 */
function match_new_lost_item($pdo, $lostItemId) {
    $stmt = $pdo->prepare("SELECT * FROM lost_items WHERE id = ?");
    $stmt->execute([$lostItemId]);
    $lost = $stmt->fetch();
    if (!$lost) return;

    $foundItems = $pdo->query("SELECT * FROM found_items WHERE status != 'resolved'")->fetchAll();

    foreach ($foundItems as $found) {
        evaluate_and_store_match($pdo, $lost, $found);
    }
}

/**
 * Runs when a NEW FOUND item is submitted: check it against all open lost items.
 */
function match_new_found_item($pdo, $foundItemId) {
    $stmt = $pdo->prepare("SELECT * FROM found_items WHERE id = ?");
    $stmt->execute([$foundItemId]);
    $found = $stmt->fetch();
    if (!$found) return;

    $lostItems = $pdo->query("SELECT * FROM lost_items WHERE status != 'resolved'")->fetchAll();

    foreach ($lostItems as $lost) {
        evaluate_and_store_match($pdo, $lost, $found);
    }
}

function evaluate_and_store_match($pdo, $lost, $found) {
    $score = calculate_match_score($lost, $found);

    if ($score >= MATCH_THRESHOLD) {
        // avoid duplicate match rows
        $check = $pdo->prepare(
            "SELECT id FROM matches WHERE lost_item_id = ? AND found_item_id = ?"
        );
        $check->execute([$lost['id'], $found['id']]);

        if (!$check->fetch()) {
            $insert = $pdo->prepare(
                "INSERT INTO matches (lost_item_id, found_item_id, match_score) VALUES (?, ?, ?)"
            );
            $insert->execute([$lost['id'], $found['id'], $score]);

            $pdo->prepare("UPDATE lost_items SET status = 'matched' WHERE id = ? AND status = 'open'")
                ->execute([$lost['id']]);
            $pdo->prepare("UPDATE found_items SET status = 'matched' WHERE id = ? AND status = 'open'")
                ->execute([$found['id']]);

            notify_match_found($pdo, $lost, $found, $score);
        }
    }
}
