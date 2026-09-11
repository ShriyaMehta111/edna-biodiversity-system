<?php
session_start();
require 'db.php'; // expects $pdo = new PDO(...)

if (!isset($_SESSION['user_id']) || !isset($_SESSION['upload_sequences'])) {
    header("Location: upload.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$sequences = $_SESSION['upload_sequences'];
unset($_SESSION['upload_sequences']);

// Function to calculate similarity percentage
function dna_similarity($seq1, $seq2) {
    $len1 = strlen($seq1);
    $len2 = strlen($seq2);
    $len = min($len1, $len2);
    if ($len == 0) return 0;

    $matches = 0;
    for ($i = 0; $i < $len; $i++) {
        if ($seq1[$i] === $seq2[$i]) $matches++;
    }
    return ($matches / $len) * 100;
}

// Detect source of each sequence for upload_type
$manual_sequences = [];
$csv_sequences = [];

if (!empty($_POST['sequence'])) {
    $manual_sequences[] = strtoupper(trim($_POST['sequence']));
}
if (isset($_FILES['csv_file']) && $_FILES['csv_file']['name'] !== "") {
    if (($handle = fopen($_FILES['csv_file']['tmp_name'], 'r')) !== false) {
        while (($data = fgetcsv($handle)) !== false) {
            if (!isset($data[0])) continue;
            $csv_sequences[] = strtoupper(trim($data[0]));
        }
        fclose($handle);
    }
}

foreach ($sequences as $seq) {
    // Determine upload_type
    if (in_array($seq, $manual_sequences)) {
        $upload_type = 'manual';
    } else {
        $upload_type = 'csv';
    }

    // Insert into dna_samples
    $stmt_sample = $pdo->prepare("
        INSERT INTO dna_samples (user_id, sequence, upload_type) 
        VALUES (:uid, :seq, :type)
    ");
    $stmt_sample->execute([
        ':uid' => $user_id,
        ':seq' => $seq,
        ':type' => $upload_type
    ]);
    $sample_id = $pdo->lastInsertId();

    // Initialize best match
    $best_match = ['taxid' => null, 'similarity' => 0, 'dataset_id' => null];

    // Compare with taxonomy table
    $stmt = $pdo->query("SELECT taxid, sequence FROM taxonomy");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $sim = dna_similarity($seq, strtoupper($row['sequence']));
        if ($sim > $best_match['similarity']) {
            $best_match = ['taxid' => $row['taxid'], 'similarity' => $sim, 'dataset_id' => null];
        }
    }

    // Compare with local_dataset table
    $stmt = $pdo->query("SELECT dataset_id, sequence, taxid FROM local_dataset");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $sim = dna_similarity($seq, strtoupper($row['sequence']));
        if ($sim > $best_match['similarity']) {
            $best_match = ['taxid' => $row['taxid'], 'similarity' => $sim, 'dataset_id' => $row['dataset_id']];
        }
    }

    // Insert into analysis_results
    $stmt = $pdo->prepare("
        INSERT INTO analysis_results (sample_id, taxid, similarity_matched, processed_at)
        VALUES (:sample_id, :taxid, :sim, NOW())
    ");
    $stmt->execute([
        ':sample_id' => $sample_id,
        ':taxid' => $best_match['taxid'],
        ':sim' => $best_match['similarity']
    ]);
}

// Redirect to results page
header("Location: result.php");
exit();
?>
