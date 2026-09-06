<!-- return_confirmation.php -->
<?php
// Determine penalty message
$penalty_info = '';
if ($penalty_amount > 0) {
    $penalty_info = "
        <div style='background: #fef2f2; border-left: 4px solid #dc2626; padding: 15px; border-radius: 4px; margin: 20px 0;'>
            <p style='margin: 0 0 10px 0; color: #dc2626; font-weight: bold;'>⚠️ Late Return - Penalty Applied</p>
            <p style='margin: 5px 0;'>Rate: " . htmlspecialchars(PENALTY_PER_DAY) . " " . htmlspecialchars(PENALTY_CURRENCY) . " per day</p>
            <p style='margin: 5px 0;'>Overdue Days: " . htmlspecialchars($overdue_days ?? 0) . "</p>
            <p style='margin: 5px 0; font-size: 18px; color: #dc2626; font-weight: bold;'>
                Total Penalty: " . number_format($penalty_amount, 2) . " " . htmlspecialchars(PENALTY_CURRENCY) . "
            </p>
        </div>
    ";
} else {
    $penalty_info = "
        <div style='background: #f0fdf4; border-left: 4px solid #16a34a; padding: 15px; border-radius: 4px; margin: 20px 0;'>
            <p style='margin: 0; color: #059669; font-weight: bold;'>✓ No Penalties - Returned on Time!</p>
        </div>
    ";
}
?>

<div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto;'>
    <div style='background: linear-gradient(135deg, #10b981 0%, #34d399 100%); padding: 30px; text-align: center; color: white; border-radius: 8px 8px 0 0;'>
        <h2 style='margin: 0; font-size: 24px;'>✓ Asset Return Confirmed</h2>
    </div>
    
    <div style='background: white; padding: 30px; border: 1px solid #e5e7eb;'>
        <p style='font-size: 16px; margin: 0 0 20px 0;'>Hi <strong><?php echo htmlspecialchars($user_name ?? 'User'); ?></strong>,</p>
        
        <p style='margin: 0 0 20px 0;'>Your asset return has been recorded successfully:</p>
        
        <div style='background: #f3f4f6; padding: 20px; border-radius: 8px; margin: 20px 0;'>
            <table style='width: 100%; border-collapse: collapse;'>
                <tr>
                    <td style='padding: 10px 0; width: 40%; font-weight: bold;'>Asset Name:</td>
                    <td style='padding: 10px 0;'><?php echo htmlspecialchars($asset_name ?? 'N/A'); ?></td>
                </tr>
                <tr>
                    <td style='padding: 10px 0; font-weight: bold;'>Return Date:</td>
                    <td style='padding: 10px 0;'><?php echo date('F d, Y', strtotime($return_date ?? 'now')); ?></td>
                </tr>
                <tr>
                    <td style='padding: 10px 0; font-weight: bold;'>Days Borrowed:</td>
                    <td style='padding: 10px 0;'><?php echo htmlspecialchars($days_borrowed ?? 0); ?> days</td>
                </tr>
            </table>
        </div>
        
        <?php echo $penalty_info; ?>
        
        <p style='color: #6b7280; font-size: 14px; margin: 20px 0 0 0;'>
            Thank you for returning the asset. We appreciate your cooperation!
        </p>
    </div>
    
    <div style='background: #f9fafb; padding: 20px; text-align: center; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 8px 8px; font-size: 12px; color: #6b7280;'>
        <p style='margin: 0;'>This is an automated email from Asset Tracker System</p>
        <p style='margin: 5px 0 0 0;'>Please do not reply to this message</p>
    </div>
</div>