<?php
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Capture and sanitize incoming values
    $post_data = [
        'date_val'       => filter_input(INPUT_POST, 'date_val', FILTER_SANITIZE_SPECIAL_CHARS),
        'rank'           => filter_input(INPUT_POST, 'rank', FILTER_SANITIZE_SPECIAL_CHARS),
        'full_name'      => filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_SPECIAL_CHARS),
        'service_number' => filter_input(INPUT_POST, 'service_number', FILTER_SANITIZE_SPECIAL_CHARS),
        'uniformity'     => filter_input(INPUT_POST, 'uniformity', FILTER_SANITIZE_NUMBER_INT),
        'punctuality'    => filter_input(INPUT_POST, 'punctuality', FILTER_SANITIZE_NUMBER_INT),
        'discipline'     => filter_input(INPUT_POST, 'discipline', FILTER_SANITIZE_NUMBER_INT),
        'participation'  => filter_input(INPUT_POST, 'participation', FILTER_SANITIZE_NUMBER_INT),
        'quality_of_work'=> filter_input(INPUT_POST, 'quality_of_work', FILTER_SANITIZE_NUMBER_INT),
        'supervisor'     => filter_input(INPUT_POST, 'supervisor', FILTER_SANITIZE_SPECIAL_CHARS),
        'total'          => filter_input(INPUT_POST, 'total', FILTER_SANITIZE_NUMBER_INT),
        'percentage'     => filter_input(INPUT_POST, 'percentage', FILTER_SANITIZE_SPECIAL_CHARS)
    ];

    // CONFIGURATION: Replace with your actual deployed Google Apps Script Web App URL
    $google_script_url = "https://script.google.com/macros/s/AKfycbyyI0yGTxBcJaKLJXBkqv8wTwWy0nM5QrXDD5zntncxlsmX5TOqJ17b5g3x3QNT725m/exec";

    // 2. Server-side guard: Reject form submission if employee details are left blank
    if (empty($post_data['rank']) || empty($post_data['full_name'])) {
        $message = "<div style='color: #c62828; background: #ffebee; padding: 12px; border-radius: 4px; font-weight: bold; margin-bottom: 20px;'>Submission Failed: You cannot submit an appraisal for an unverified or blank Service Number.</div>";
    } else {
        // 3. Dispatch to Google Sheets API bridge via cURL
        $ch = curl_init($google_script_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        
        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);
        if (isset($result['result']) && $result['result'] === 'success') {
            $message = "<div style='color: #2e7d32; background: #e8f5e9; padding: 12px; border-radius: 4px; font-weight: bold; margin-bottom: 20px;'>Appraisal submitted successfully!</div>";
        } else {
            $message = "<div style='color: #c62828; background: #ffebee; padding: 12px; border-radius: 4px; font-weight: bold; margin-bottom: 20px;'>Error saving records to spreadsheet. Please try again.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performance Appraisal System</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f0f2f5; padding: 30px; color: #333; }
        .form-container { max-width: 650px; background: #fff; padding: 35px; margin: 0 auto; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        h2 { margin-top: 0; color: #1a73e8; border-bottom: 2px solid #e8f0fe; padding-bottom: 10px; }
        h3 { margin: 25px 0 10px 0; color: #5f6368; font-size: 16px; text-transform: uppercase; letter-spacing: 0.5px;}
        .form-group { margin-bottom: 18px; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 14px; color: #444; }
        input[type="text"], input[type="date"], select { width: 100%; padding: 10px; border: 1px solid #dadce0; border-radius: 4px; box-sizing: border-box; font-size: 14px; }
        input[readonly] { background-color: #f1f3f4; color: #5f6368; cursor: not-allowed; }
        .calculated-fields input[readonly] { background-color: #e8f0fe; font-weight: bold; color: #1a73e8; }
        button { background-color: #1a73e8; color: white; padding: 14px 20px; border: none; border-radius: 4px; cursor: pointer; width: 100%; font-size: 16px; font-weight: bold; margin-top: 20px;}
        button:hover { background-color: #1557b0; }
        #lookup-status { font-size: 13px; font-weight: bold; margin-top: 5px; display: block; }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Performance Appraisal Form</h2>
    
    <?php if (!empty($message)) echo $message; ?>

    <form action="" method="POST">
        
        <h3>Personnel Information</h3>
        <div class="form-group">
            <label for="date_val">Date</label>
            <input type="date" id="date_val" name="date_val" max="<?php echo date('Y-m-d'); ?>" required>
        </div>

        <div class="form-group">
            <label for="service_number">Service Number</label>
            <input type="text" id="service_number" name="service_number" placeholder="Enter Service No. and press Tab or click outside" autocomplete="off" required>
            <span id="lookup-status"></span>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label for="rank">Rank</label>
                <input type="text" id="rank" name="rank" readonly placeholder="Awaiting Service Number lookup" required>
            </div>
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" readonly placeholder="Awaiting Service Number lookup" required>
            </div>
        </div>

        <h3>Performance Metrics (Score 1 - 5)</h3>
        <div class="grid-2">
            <div class="form-group">
                <label for="uniformity">Uniformity</label>
                <select class="score-input" id="uniformity" name="uniformity" required>
                    <option value="0">Select Score</option>
                    <option value="5">5 - Outstanding</option>
                    <option value="4">4 - Good</option>
                    <option value="3">3 - Satisfactory</option>
                    <option value="2">2 - Marginal</option>
                    <option value="1">1 - Unsatisfactory</option>
                </select>
            </div>
            <div class="form-group">
                <label for="punctuality">Punctuality</label>
                <select class="score-input" id="punctuality" name="punctuality" required>
                    <option value="0">Select Score</option>
                    <option value="5">5 - Outstanding</option>
                    <option value="4">4 - Good</option>
                    <option value="3">3 - Satisfactory</option>
                    <option value="2">2 - Marginal</option>
                    <option value="1">1 - Unsatisfactory</option>
                </select>
            </div>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label for="discipline">Discipline and Respect</label>
                <select class="score-input" id="discipline" name="discipline" required>
                    <option value="0">Select Score</option>
                    <option value="5">5 - Outstanding</option>
                    <option value="4">4 - Good</option>
                    <option value="3">3 - Satisfactory</option>
                    <option value="2">2 - Marginal</option>
                    <option value="1">1 - Unsatisfactory</option>
                </select>
            </div>
            <div class="form-group">
                <label for="participation">Participation</label>
                <select class="score-input" id="participation" name="participation" required>
                    <option value="0">Select Score</option>
                    <option value="5">5 - Outstanding</option>
                    <option value="4">4 - Good</option>
                    <option value="3">3 - Satisfactory</option>
                    <option value="2">2 - Marginal</option>
                    <option value="1">1 - Unsatisfactory</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="quality_of_work">Quality of Work</label>
            <select class="score-input" id="quality_of_work" name="quality_of_work" required>
                <option value="0">Select Score</option>
                <option value="5">5 - Outstanding</option>
                <option value="4">4 - Good</option>
                <option value="3">3 - Satisfactory</option>
                <option value="2">2 - Marginal</option>
                <option value="1">1 - Unsatisfactory</option>
            </select>
        </div>

        <h3>Reviewing Officer</h3>
        <div class="form-group">
            <label for="supervisor">Supervisor's Rank and Name</label>
            <select id="supervisor" name="supervisor" required>
                <option value="">Select Supervisor</option>
                <option value="DSP Ahmed Jaadhullah">DSP Ahmed Jaadhullah</option>
                <option value="DSP Ibrahim Afeef">DSP Ibrahim Afeef</option>
                <option value="ASP Shakeel Shareef">ASP Shakeel Shareef</option>
                <option value="ASP Ahmed Shahid">ASP Ahmed Shahid</option>
            </select>
        </div>

        <h3>Calculated Results</h3>
        <div class="grid-2 calculated-fields">
            <div class="form-group">
                <label for="total">Total Score (Out of 25)</label>
                <input type="text" id="total" name="total" readonly value="0">
            </div>
            <div class="form-group">
                <label for="percentage">Percentage</label>
                <input type="text" id="percentage" name="percentage" readonly value="0%">
            </div>
        </div>

        <button type="submit">Submit Performance Review</button>
    </form>
</div>

<script>
    // CONFIGURATION: Replace with your deployed Google Apps Script Web App URL
    const SCRIPT_URL = "https://script.google.com/macros/s/AKfycbyyI0yGTxBcJaKLJXBkqv8wTwWy0nM5QrXDD5zntncxlsmX5TOqJ17b5g3x3QNT725m/exec";

    // 1. Staff Lookup Listener via blur event
    document.getElementById('service_number').addEventListener('blur', function() {
        const serviceNo = this.value.trim();
        const statusSpan = document.getElementById('lookup-status');
        const rankField = document.getElementById('rank');
        const nameField = document.getElementById('full_name');

        if (!serviceNo) return;

        statusSpan.textContent = "Searching local directory...";
        statusSpan.style.color = "#1a73e8";

        fetch(`${SCRIPT_URL}?service_number=${encodeURIComponent(serviceNo)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    rankField.value = data.rank;
                    nameField.value = data.full_name;
                    statusSpan.textContent = "Staff member verified ✓";
                    statusSpan.style.color = "#2e7d32";
                } else {
                    rankField.value = "";
                    nameField.value = "";
                    statusSpan.textContent = "Verification Failed: Staff number not found.";
                    statusSpan.style.color = "#c62828";
                }
            })
            .catch(error => {
                console.error("Fetch lookup error:", error);
                statusSpan.textContent = "Connection lookup error.";
                statusSpan.style.color = "#c62828";
            });
    });

    // 2. Real-Time Math Metrics Calculator
    const scoreInputs = document.querySelectorAll('.score-input');
    const totalField = document.getElementById('total');
    const percentageField = document.getElementById('percentage');

    scoreInputs.forEach(input => {
        input.addEventListener('change', calculateMetrics);
    });

    function calculateMetrics() {
        let total = 0;
        const maxScore = 25; 

        scoreInputs.forEach(input => {
            total += parseInt(input.value) || 0;
        });

        let percentage = (total / maxScore) * 100;

        totalField.value = total;
        percentageField.value = percentage.toFixed(1) + "%";
    }
</script>
</body>
</html>