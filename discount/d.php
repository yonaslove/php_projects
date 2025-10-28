<?php
function calculateDiscount($userType, $totalAmount, $isSeasonalPromo) {
    $discount = 0;

    // Apply discount only if ALL conditions are met
    if ($userType == "member" && $totalAmount >= 500 && $isSeasonalPromo == true) {
        $discount = 0.15; // 15% discount
    }

    $discountAmount = $totalAmount * $discount;
    $finalPrice = $totalAmount - $discountAmount;

    // Display output
    echo "User Type: $userType<br>";
    echo "Total Amount: $" . number_format($totalAmount, 2) . "<br>";
    echo "Seasonal Promotion: " . ($isSeasonalPromo ? "Yes" : "No") . "<br>";
    echo "Discount Applied: " . ($discount * 100) . "%<br>";
    echo "Discount Amount: $" . number_format($discountAmount, 2) . "<br>";
    echo "Final Price to Pay: $" . number_format($finalPrice, 2) . "<br>";
}

// Example usage
$userType = "guest";          // "guest" or "member"
$totalAmount = 600;            // total shopping amount
$isSeasonalPromo = true;       // true = promotion active, false = not active

calculateDiscount($userType, $totalAmount, $isSeasonalPromo);
?>