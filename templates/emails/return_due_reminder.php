<!-- return_due_reminder.php -->
<div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto;'>
    <div style='background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%); padding: 30px; text-align: center; color: white; border-radius: 8px 8px 0 0;'>
        <h2 style='margin: 0; font-size: 24px;'>📅 Asset Return Reminder</h2>
    </div>
    
    <div style='background: white; padding: 30px; border: 1px solid #e5e7eb;'>
        <p style='font-size: 16px; margin: 0 0 20px 0;'>Hi <strong><?php echo htmlspecialchars($user_name ?? 'User'); ?></strong>,</p>
        
        <p style='margin: 0 0 20px 0;'>This is a friendly reminder that your borrowed asset is due for return:</p>
        
        <div style='background: #fffbeb; border-left: 4px solid #f59e0b; padding: 20px; border-radius: 4px; margin: 20px 0;'>
            <table style='width: 100%; border-collapse: collapse;'>
                <tr>
                    <td style='padding: 10px 0; width: 40%; font-weight: bold;'>Asset:</td>
                    <td style='padding: 10px 0;'><?php echo htmlspecialchars($asset_name ?? 'N/A'); ?></td>
                </tr>
                <tr style='background: #fef3c7;'>
                    <td style='padding: 10px 0; font-weight: bold; color: #d97706;'>Due Date:</td>
                    <td style='padding: 10px 0; color: #d97706; font-weight: bold;'><?php echo date('F d, Y', strtotime($return_date ?? 'now')); ?></td>
                </tr>
                <tr>
                    <td style='padding: 10px 0; font-weight: bold;'>Days Remaining:</td>
                    <td style='padding: 10px 0;'><span style='background: #fcd34d; padding: 5px 10px; border-radius: 20px; font-weight: bold;'><?php echo htmlspecialchars($days_remaining ?? 0); ?> day(s)</span></td>
                </tr>
            </table>
        </div>
        
        <div style='background: #f0fdf4; border-left: 4px solid #16a34a; padding: 15px; border-radius: 4px; margin: 20px 0;'>
            <p style='margin: 0;'><strong>✓ Tip:</strong> Plan your return logistics now to avoid any delays and penalties.</p>
        </div>
        
        <p style='color: #6b7280; font-size: 14px; margin: 20px 0 0 0;'>
            Need help? Contact admin at <?php echo htmlspecialchars(ADMIN_EMAIL); ?>
        </p>
    </div>
    
    <div style='background: #f9fafb; padding: 20px; text-align: center; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 8px 8px; font-size: 12px; color: #6b7280;'>
        <p style='margin: 0;'>This is an automated email from Asset Tracker System</p>
        <p style='margin: 5px 0 0 0;'>Please do not reply to this message</p>
    </div>
</div>
