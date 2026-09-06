<!-- overdue_notification.php -->
<div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto;'>
    <div style='background: linear-gradient(135deg, #ef4444 0%, #f87171 100%); padding: 30px; text-align: center; color: white; border-radius: 8px 8px 0 0;'>
        <h2 style='margin: 0; font-size: 24px;'>🚨 Overdue Asset Alert</h2>
    </div>
    
    <div style='background: white; padding: 30px; border: 1px solid #e5e7eb;'>
        <p style='font-size: 16px; margin: 0 0 20px 0;'>Hi <strong><?php echo htmlspecialchars($user_name ?? 'User'); ?></strong>,</p>
        
        <p style='margin: 0 0 20px 0;'><strong>⚠️ URGENT:</strong> The asset you borrowed is now overdue and penalties are being applied daily.</p>
        
        <div style='background: #fef2f2; border-left: 4px solid #dc2626; padding: 20px; border-radius: 4px; margin: 20px 0;'>
            <table style='width: 100%; border-collapse: collapse;'>
                <tr>
                    <td style='padding: 10px 0; width: 40%; font-weight: bold;'>Asset:</td>
                    <td style='padding: 10px 0;'><?php echo htmlspecialchars($asset_name ?? 'N/A'); ?></td>
                </tr>
                <tr>
                    <td style='padding: 10px 0; font-weight: bold;'>Expected Return:</td>
                    <td style='padding: 10px 0;'><?php echo date('F d, Y', strtotime($expected_return_date ?? 'now')); ?></td>
                </tr>
                <tr style='background: #fecaca;'>
                    <td style='padding: 10px 0; font-weight: bold; color: #991b1b;'>Days Overdue:</td>
                    <td style='padding: 10px 0; font-weight: bold; color: #991b1b;'><?php echo htmlspecialchars($overdue_days ?? 0); ?> days</td>
                </tr>
                <tr>
                    <td style='padding: 10px 0; font-weight: bold;'>Daily Rate:</td>
                    <td style='padding: 10px 0;'><?php echo htmlspecialchars(PENALTY_PER_DAY); ?> <?php echo htmlspecialchars(PENALTY_CURRENCY); ?>/day</td>
                </tr>
                <tr style='background: #fecaca; border-top: 2px solid #dc2626;'>
                    <td style='padding: 15px 0; font-weight: bold; color: #991b1b; font-size: 16px;'>Current Penalty:</td>
                    <td style='padding: 15px 0; font-weight: bold; color: #991b1b; font-size: 18px;'>
                        <?php echo number_format($penalty_amount ?? 0, 2); ?> <?php echo htmlspecialchars(PENALTY_CURRENCY); ?>
                    </td>
                </tr>
            </table>
        </div>
        
        <div style='background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; border-radius: 4px; margin: 20px 0;'>
            <p style='margin: 0 0 10px 0; font-weight: bold;'>⏰ Action Required Immediately:</p>
            <ul style='margin: 10px 0; padding-left: 20px;'>
                <li><strong>Return the asset immediately</strong> to stop additional penalties</li>
                <li>Contact the admin if you need an extension</li>
                <li>Resolve any outstanding penalties as soon as possible</li>
            </ul>
        </div>
        
        <p style='color: #6b7280; font-size: 14px;'>
            <strong>Admin Contact:</strong> <a href='mailto:<?php echo htmlspecialchars(ADMIN_EMAIL); ?>' style='color: #2563eb; text-decoration: none;'><?php echo htmlspecialchars(ADMIN_EMAIL); ?></a>
        </p>
    </div>
    
    <div style='background: #f9fafb; padding: 20px; text-align: center; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 8px 8px; font-size: 12px; color: #6b7280;'>
        <p style='margin: 0;'>This is an automated email from Asset Tracker System</p>
        <p style='margin: 5px 0 0 0;'>Please do not reply to this message</p>
    </div>
</div>