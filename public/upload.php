<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';

function is_valid_dna($seq) {
    return preg_match('/^[ATGC]+$/i', $seq);
}

function clean_seq_local($s) {
    return strtoupper(trim($s));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sequences = [];

    /* ========= MANUAL SEQUENCE ========= */
    if (!empty($_POST['sequence'])) {
        $raw = clean_seq_local($_POST['sequence']);

        if (!is_valid_dna($raw)) {
            $message = "Invalid DNA sequence. Only characters A, T, G, C are allowed.";
        } else {
            $sequences[] = $raw;
            $upload_type = "manual";
        }
    }

    /* ========= CSV UPLOAD ========= */
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['name'] !== "" && !$message) {
        $file = $_FILES['csv_file']['tmp_name'];

        if (($handle = fopen($file, 'r')) !== false) {
            while (($data = fgetcsv($handle)) !== false) {
                if (!isset($data[0])) continue;

                $raw = clean_seq_local($data[0]);

                if (!is_valid_dna($raw)) {
                    $message = "CSV contains invalid DNA sequence. Only A, T, G, C are allowed.";
                    break;
                }
                $sequences[] = $raw;
            }
            fclose($handle);
        }

        if (!empty($sequences)) {
            $upload_type = "csv";
        }
    }

    /* ========= FINAL CHECK ========= */
    if (!$message && count($sequences) > 0) {
        $sequences = array_values(array_unique($sequences));
        $_SESSION['upload_sequences'] = $sequences;
        header("Location: process_api.php");
        exit();
    }

    if (!$message) {
        $message = "Please enter a valid DNA sequence or upload a valid CSV file.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Upload DNA Sequence | eDNA Biodiversity</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body { background-color: #f8f9fa; }
.container-box {
    margin-top:50px;
    max-width:800px;
    background:white;
    padding:30px;
    border-radius:10px;
    box-shadow:0 4px 20px rgba(0,0,0,0.1);
}
textarea { resize: none; }
</style>

<script>
function validateForm() {
    const seq = document.querySelector("textarea[name='sequence']").value.trim();
    const regex = /^[ATGCatgc]+$/;

    if (seq !== "" && !regex.test(seq)) {
        alert("Only A, T, G, C characters are allowed in DNA sequence.");
        return false;
    }
    return true;
}
</script>

</head>
<body>

<div class="container container-box">
    <h2 class="text-center mb-4">🔬 eDNA Sequence Analyzer</h2>

    <?php if ($message): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" onsubmit="return validateForm();">

        <div class="mb-3">
            <label class="form-label">Enter a DNA Sequence Manually</label>
            <textarea class="form-control" name="sequence" rows="5"
                placeholder="Only A, T, G, C allowed (e.g., ATCGGATTAC)"></textarea>
        </div>

        <div class="text-center mb-3">OR</div>

        <div class="mb-3">
            <label class="form-label">Upload CSV File</label>
            <input type="file" class="form-control" name="csv_file" accept=".csv">
            <small class="form-text text-muted">
                CSV must contain only A, T, G, C sequences
            </small>
        </div>

        <button type="submit" class="btn btn-success w-100">
            🔍 Analyze DNA
        </button>
    </form>
</div>

</body>
</html>
