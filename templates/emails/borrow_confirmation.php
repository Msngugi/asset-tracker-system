<div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto;'>
    <div style='background: linear-gradient(135deg, #6366f1 0%, #818cf8 100%); padding: 30px; text-align: center; color: white; border-radius: 8px 8px 0 0;'>
        <h2 style='margin: 0; font-size: 24px;'>✓ Asset Borrowed Successfully</h2>
    </div>
    
    <div style='background: white; padding: 30px; border: 1px solid #e5e7eb;'>
        <p style='font-size: 16px; margin: 0 0 20px 0;'>Hi <strong><?php echo htmlspecialchars($user_name ?? 'User'); ?></strong>,</p>
        
        <p style='margin: 0 0 20px 0;'>You have successfully borrowed the following asset:</p>
        
        <div style='background: #f3f4f6; padding: 20px; border-radius: 8px; margin: 20px 0;'>
            <table style='width: 100%; border-collapse: collapse;'>
                <tr>
                    <td style='padding: 10px 0; width: 40%; font-weight: bold;'>Asset Name:</td>
                    <td style='padding: 10px 0;'><?php echo htmlspecialchars($asset_name ?? 'N/A'); ?></td>
                </tr>
                <tr>
                    <td style='padding: 10px 0; font-weight: bold;'>Model:</td>
                    <td style='padding: 10px 0;'><?php echo htmlspecialchars($asset_model ?? 'N/A'); ?></td>
                </tr>
                <tr>
                    <td style='padding: 10px 0; font-weight: bold;'>Serial Number:</td>
                    <td style='padding: 10px 0;'><?php echo htmlspecialchars($serial_number ?? 'N/A'); ?></td>
                </tr>
                <tr>
                    <td style='padding: 10px 0; font-weight: bold;'>Borrow Date:</td>
                    <td style='padding: 10px 0;'><?php echo date('F d, Y', strtotime($borrow_date ?? 'now')); ?></td>
                </tr>
                <tr style='background: #fef3c7;'>
                    <td style='padding: 10px 0; font-weight: bold; color: #dc2626;'>Return By:</td>
                    <td style='padding: 10px 0; color: #dc2626; font-weight: bold;'><?php echo date('F d, Y', strtotime($expected_return_date ?? 'now')); ?></td>
                </tr>
                <tr>
                    <td style='padding: 10px 0; font-weight: bold;'>Duration:</td>
                    <td style='padding: 10px 0;'><?php echo htmlspecialchars($borrow_days ?? 0); ?> days</td>
                </tr>
            </table>
        </div>
        
        <div style='background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; border-radius: 4px; margin: 20px 0;'>
            <p style='margin: 0; font-weight: bold;'>⚠️ Important Reminders:</p>
            <ul style='margin: 10px 0; padding-left: 20px;'>
                <li>Return the asset in good condition by the due date</li>
                <li>Failure to return on time will result in penalties</li>
                <li>Penalty: <?php echo PENALTY_PER_DAY; ?> <?php echo PENALTY_CURRENCY; ?> per day</li>
            </ul>
        </div>
        
        <p style='color: #6b7280; font-size: 14px; margin: 20px 0 0 0;'>
            If you have any questions, contact the admin at <?php echo htmlspecialchars(ADMIN_EMAIL); ?>
        </p>
    </div>
    
    <div style='background: #f9fafb; padding: 20px; text-align: center; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 8px 8px; font-size: 12px; color: #6b7280;'>
        <p style='margin: 0;'>This is an automated email from Asset Tracker System</p>
        <p style='margin: 5px 0 0 0;'>Please do not reply to this message</p>
    </div>
</div>
