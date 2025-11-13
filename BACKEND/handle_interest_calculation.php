<!-- handle_interest_calculation.php -->
<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $scheme = $_POST['scheme'] ?? '';
    $amount = $_POST['amount'] ?? 0;
    $rate = $_POST['rate'] ?? 0;
    $time = $_POST['time'] ?? 0;

    $calculated_value = 0;
    $error = '';

    if (!is_numeric($amount) || $amount <= 0 || !is_numeric($rate) || $rate < 0 || !is_numeric($time) || $time <= 0) {
        $error = "Please enter valid positive numbers for Amount, Rate, and Time.";
    } else {
        $rate_decimal = $rate / 100;

        switch ($scheme) {
            case 'fd': // Simple Interest
            case 'policy': // Simple Interest
                $calculated_value = $amount * (1 + ($rate_decimal * $time));
                break;
            case 'mutual': // Compound Interest (assuming annual compounding)
                $calculated_value = $amount * pow((1 + $rate_decimal), $time);
                break;
            default:
                $error = "Invalid scheme selected.";
                break;
        }
    }

    // Store result in session to display on the HTML page
    if ($error) {
        $_SESSION['calc_error'] = $error;
    } else {
        $_SESSION['calculated_value'] = $calculated_value;
        $_SESSION['calc_inputs'] = [
            'scheme' => $scheme,
            'amount' => $amount,
            'rate' => $rate,
            'time' => $time
        ];
    }
    redirect(FRONTEND_PAGES_URL . '15Savingschemes.html');
} else {
    redirect(FRONTEND_PAGES_URL . '15Savingschemes.html');
}
?>
