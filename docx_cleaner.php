<?php
// docx_cleaner.php
// Purpose: Remove label patterns like [!b:$(... )$] from HA4.docx and output HA4_clean.docx
// Usage: Place this file in the same folder as HA4.docx, then open in browser or run via CLI: php docx_cleaner.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$baseDir = __DIR__;
$source = $baseDir . DIRECTORY_SEPARATOR . 'HA4.docx';
$target = $baseDir . DIRECTORY_SEPARATOR . 'HA4_clean.docx';

header('Content-Type: text/plain; charset=utf-8');

echo "DOCX Cleaner\n";

echo "Source: $source\n";
if (!file_exists($source)) {
    echo "ERROR: Source file not found.\n";
    exit(1);
}

$zip = new ZipArchive();
if ($zip->open($source) !== true) {
    echo "ERROR: Cannot open DOCX as zip.\n";
    exit(1);
}

echo "Reading word/document.xml ...\n";
$xml = $zip->getFromName('word/document.xml');
$zip->close();

if ($xml === false) {
    echo "ERROR: word/document.xml not found in DOCX.\n";
    exit(1);
}

$originalLength = strlen($xml);

// We will try multiple regex passes to remove [!b:$( ... )$] patterns.
// 1) Straight text (no tags inside)
$patterns = [
    // Generic lazy match: [!b:$( ... )$]
    '/\[\s*!\s*b\s*:\s*\$\s*\(\s*[\s\S]*?\s*\)\s*\$\s*\]/u',
];

$replacementsCount = 0;
foreach ($patterns as $rx) {
    $xml = preg_replace($rx, '', $xml, -1, $count);
    $replacementsCount += (int)$count;
}

// 2) Handle case where the sequence is split across runs/tags.
// We attempt a normalization that joins adjacent text runs so bracketed markers become contiguous.
// This is conservative and only removes tag boundaries BETWEEN closing and next opening text tags.
$xmlNorm = $xml;
$joinPasses = 0;
// Try a few passes to join scattered <w:t>..</w:t> pairs that are only separated by tag soup
while ($joinPasses < 3) {
    $before = $xmlNorm;
    // Join text content spread over sequences like </w:t> ... <w:t ...>
    $xmlNorm = preg_replace('/<\/w:t>\s*<\/w:r>\s*<w:r[^>]*>\s*<w:t[^>]*>/u', '', $xmlNorm);
    $xmlNorm = preg_replace('/<\/w:t>\s*(?:<[^>]+>\s*)+<w:t[^>]*>/u', '', $xmlNorm);

    if ($xmlNorm === null) { // regex error
        break;
    }
    if ($xmlNorm === $before) {
        break; // no more changes
    }
    $joinPasses++;
}

if ($xmlNorm !== $xml) {
    // Re-run removal on normalized xml
    foreach ($patterns as $rx) {
        $xmlNorm = preg_replace($rx, '', $xmlNorm, -1, $count2);
        $replacementsCount += (int)$count2;
    }
}

$finalXml = $xmlNorm;
$finalLength = strlen($finalXml);

// Create the cleaned docx by copying original and replacing the XML entry
if (file_exists($target)) {
    @unlink($target);
}

$in = new ZipArchive();
if ($in->open($source) !== true) {
    echo "ERROR: Cannot reopen source zip.\n";
    exit(1);
}

$tmp = $baseDir . DIRECTORY_SEPARATOR . 'HA4_clean_tmp.zip';
if (file_exists($tmp)) {
    @unlink($tmp);
}

$out = new ZipArchive();
if ($out->open($tmp, ZipArchive::CREATE) !== true) {
    echo "ERROR: Cannot create temp zip.\n";
    $in->close();
    exit(1);
}

// Copy all entries, replacing word/document.xml
for ($i = 0; $i < $in->numFiles; $i++) {
    $stat = $in->statIndex($i);
    if (!$stat) continue;
    $name = $stat['name'];
    if ($name === 'word/document.xml') {
        $out->addFromString($name, $finalXml);
    } else {
        $data = $in->getFromIndex($i);
        if ($data === false) $data = '';
        $out->addFromString($name, $data);
    }
}
$in->close();
$out->close();

// Rename temp zip to target docx
if (file_exists($target)) {
    @unlink($target);
}
if (!rename($tmp, $target)) {
    // Fallback: copy
    if (!copy($tmp, $target)) {
        echo "ERROR: Failed to move temp to target.\n";
        exit(1);
    }
    @unlink($tmp);
}

echo "Done.\n";
echo "Replacements: $replacementsCount\n";
echo "Original XML length: $originalLength, Final XML length: $finalLength\n";
echo "Output: $target\n";

// Quick verification: check if any pattern remnants remain
$leftover = preg_match('/\[\s*!\s*b\s*:\s*\$\s*\(/u', $finalXml) ? 'FOUND' : 'NOT FOUND';
echo "Leftover marker token '[!b:$(' presence: $leftover\n";

?>
