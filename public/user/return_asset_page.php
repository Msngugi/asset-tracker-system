<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Asset - Asset Tracker</title>
    <link href='https://cdn.boxicons.com/3.0.6/fonts/basic/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="css/user_dashboard.css">
    <script src="https://unpkg.com/html5-qrcode@2.3.8"></script>
    <style>
        .borrowed-assets-section {
            margin-bottom: 30px;
        }

        .assets-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .asset-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
            border: 1px solid #e5e7eb;
        }

        .asset-card:hover {
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            transform: translateY(-4px);
        }

        .asset-card-image {
            width: 100%;
            height: 200px;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            font-size: 48px;
            color: #9ca3af;
        }

        .asset-card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .asset-card-content {
            padding: 16px;
        }

        .asset-card-category {
            display: inline-block;
            background: rgba(99, 102, 241, 0.1);
            color: #6366f1;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .asset-card-name {
            font-size: 16px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 8px;
        }

        .asset-card-code {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 12px;
            font-family: monospace;
        }

        .asset-card-details {
            margin-bottom: 12px;
        }

        .asset-card-detail {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 6px;
        }

        .asset-card-condition {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 16px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 12px;
        }

        .condition-good {
            background: rgba(34, 197, 94, 0.2);
            color: #16a34a;
        }

        .asset-card-btn {
            width: 100%;
            padding: 10px;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 13px;
        }

        .asset-card-btn:hover {
            background: linear-gradient(135deg, #d97706, #b45309);
        }

        .no-assets {
            text-align: center;
            padding: 40px;
            color: #6b7280;
        }

        .no-assets i {
            font-size: 48px;
            margin-bottom: 15px;
            color: #d1d5db;
        }

        /* ✅ MODAL STYLES */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            overflow-y: auto;
            padding: 20px 0;
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal.show {
            display: block;
        }

        .modal-content {
            background: white;
            margin: auto;
            padding: 40px;
            border-radius: 16px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            animation: slideUp 0.3s ease-out;
        }

        @keyframes slideUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e5e7eb;
        }

        .modal-header h2 {
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 32px;
            color: #6b7280;
            cursor: pointer;
            transition: all 0.3s;
            padding: 0;
            width: 40px;
            height: 40px;
        }

        .modal-close:hover {
            color: #1f2937;
        }

        .modal-body {
            margin-bottom: 30px;
        }

        /* ✅ CAMERA SCANNER */
        #qr-scanner-return {
            width: 100%;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 15px;
            min-height: 350px;
            background: #000;
        }

        .scanner-info {
            background: #dbeafe;
            border: 1px solid #2563eb;
            color: #1e40af;
            padding: 12px;
            border-radius: 6px;
            font-size: 13px;
            text-align: center;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .scanner-controls {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-bottom: 15px;
        }

        .stop-scanner-btn {
            padding: 10px 20px;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 13px;
        }

        .stop-scanner-btn:hover {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            transform: translateY(-2px);
        }

        /* ✅ VALIDATION MESSAGES */
        .validation-message {
            display: none !important;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            animation: slideDown 0.3s ease-out;
            font-weight: 500;
            align-items: flex-start;
            gap: 12px;
        }

        .validation-message.active {
            display: flex !important;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .validation-message.error {
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        .validation-message.error i {
            font-size: 20px;
            color: #dc2626;
            flex-shrink: 0;
        }

        .validation-message.success {
            background: #dcfce7;
            border: 1px solid #bbf7d0;
            color: #166534;
        }

        .validation-message.success i {
            font-size: 20px;
            color: #16a34a;
            flex-shrink: 0;
        }

        .validation-content {
            flex: 1;
        }

        .validation-title {
            font-weight: 700;
            margin-bottom: 4px;
            font-size: 15px;
        }

        .validation-text {
            font-size: 13px;
            line-height: 1.5;
        }

        /* ✅ ASSET DETAILS BOX */
        .asset-details-box {
            display: none;
            background: #f0fdf4;
            border: 1px solid #86efac;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 25px;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .asset-details-box h3 {
            margin: 0 0 12px 0;
            font-size: 14px;
            color: #1f2937;
            font-weight: 600;
        }

        .asset-details-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            color: #166534;
            font-size: 13px;
            border-bottom: 1px solid #bbf7d0;
        }

        .asset-details-row:last-child {
            border-bottom: none;
        }

        .asset-details-label {
            color: #166534;
            font-weight: 600;
        }

        .asset-details-value {
            color: #15803d;
            font-weight: 500;
        }

        /* ✅ MODAL FIELDS */
        .modal-field {
            margin-bottom: 20px;
        }

        .modal-field label {
            display: block;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .modal-field input,
        .modal-field select,
        .modal-field textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.3s;
            box-sizing: border-box;
        }

        .modal-field input[readonly] {
            background: #f9fafb;
            cursor: not-allowed;
            color: #1f2937;
        }

        .modal-field input:focus,
        .modal-field select:focus,
        .modal-field textarea:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .condition-group {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .condition-radio {
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }

        .condition-radio input[type="radio"] {
            width: 16px;
            height: 16px;
            cursor: pointer;
            margin: 0;
            accent-color: #2563eb;
        }

        .condition-radio label {
            margin: 0;
            font-weight: 500;
            color: #1f2937;
            cursor: pointer;
            font-size: 13px;
        }

        .borrow-info-box {
            background: #fef3c7;
            border: 1px solid #fde047;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .borrow-info-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            color: #92400e;
        }

        .borrow-info-label {
            font-weight: 600;
            color: #b45309;
        }

        .modal-footer {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }

        .modal-btn {
            padding: 10px 24px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 14px;
        }

        .modal-btn-cancel {
            background: #e5e7eb;
            color: #1f2937;
        }

        .modal-btn-cancel:hover {
            background: #d1d5db;
        }

        .modal-btn-confirm {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
        }

        .modal-btn-confirm:hover:not(:disabled) {
            background: linear-gradient(135deg, #d97706, #b45309);
        }

        .modal-btn-confirm:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        @media (max-width: 600px) {
            .modal-content {
                padding: 25px;
                width: 95%;
            }

            #qr-scanner-return {
                min-height: 300px;
            }

            .modal-footer {
                flex-direction: column;
            }

            .modal-btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="user-container">
        <?php include 'includes/user_sidebar.php'; ?>
        
        <div class="user-main">
            <?php include 'includes/user_header.php'; ?>

            <main class="user-content">
                <div class="page-header">
                    <h1><i class='bx bx-reply'></i> Return Asset</h1>
                    <p>Click "Return Now" to scan QR code and process return</p>
                </div>

                <div class="borrowed-assets-section">
                    <?php if (isset($borrowed_assets_result) && $borrowed_assets_result->num_rows > 0): ?>
                        <div class="assets-grid">
                            <?php while ($asset = $borrowed_assets_result->fetch_assoc()): ?>
                                <div class="asset-card">
                                    <div class="asset-card-image">
                                        <?php if ($asset['image_path']): ?>
                                            <img src="<?php echo htmlspecialchars($asset['image_path']); ?>" alt="<?php echo htmlspecialchars($asset['asset_name']); ?>">
                                        <?php else: ?>
                                            <i class='bx bx-package'></i>
                                        <?php endif; ?>
                                    </div>

                                    <div class="asset-card-content">
                                        <span class="asset-card-category"><?php echo htmlspecialchars($asset['category_name'] ?? 'General'); ?></span>
                                        <div class="asset-card-name"><?php echo htmlspecialchars($asset['asset_name']); ?></div>
                                        <div class="asset-card-code"><?php echo htmlspecialchars($asset['asset_code']); ?></div>

                                        <div class="asset-card-details">
                                            <div class="asset-card-detail">
                                                <i class='bx bx-calendar'></i>
                                                <span>Borrowed: <?php echo date('M d, Y', strtotime($asset['borrow_date'] ?? 'now')); ?></span>
                                            </div>
                                            <div class="asset-card-detail">
                                                <i class='bx bx-calendar-check'></i>
                                                <span>Due: <?php echo date('M d, Y', strtotime($asset['expected_return_date'] ?? 'now')); ?></span>
                                            </div>
                                        </div>

                                        <span class="asset-card-condition condition-<?php echo strtolower($asset['asset_condition_on_borrow'] ?? 'good'); ?>">
                                            <?php echo htmlspecialchars($asset['asset_condition_on_borrow'] ?? 'Good'); ?>
                                        </span>

                                        <button type="button" class="asset-card-btn" 
                                            data-asset-name="<?php echo htmlspecialchars($asset['asset_name']); ?>" 
                                            data-asset-code="<?php echo htmlspecialchars($asset['asset_code']); ?>" 
                                            data-qr-code="<?php echo htmlspecialchars($asset['qr_code'] ?? ''); ?>"
                                            data-category="<?php echo htmlspecialchars($asset['category_name'] ?? 'N/A'); ?>"
                                            data-borrow-id="<?php echo htmlspecialchars($asset['borrow_id'] ?? ''); ?>"
                                            data-borrow-date="<?php echo htmlspecialchars($asset['borrow_date'] ?? ''); ?>"
                                            data-due-date="<?php echo htmlspecialchars($asset['expected_return_date'] ?? ''); ?>">
                                            <i class='bx bx-upload'></i> Return Now
                                        </button>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-assets">
                            <i class='bx bx-package'></i>
                            <h3>No Borrowed Assets</h3>
                            <p>You have no borrowed assets to return.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <!-- ✅ RETURN MODAL -->
    <div id="returnModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class='bx bx-qr-scan'></i> Return Asset</h2>
                <button type="button" class="modal-close" onclick="closeReturnModal()">&times;</button>
            </div>

            <div class="modal-body">
                <!-- Asset Info -->
                <div class="modal-field">
                    <label>Asset Name</label>
                    <input type="text" id="modalAssetName" readonly>
                </div>

                <div class="modal-field">
                    <label>Asset Code</label>
                    <input type="text" id="modalAssetCode" readonly>
                </div>

                <!-- Borrow Info -->
                <div class="borrow-info-box">
                    <div class="borrow-info-row">
                        <span class="borrow-info-label">Borrowed:</span>
                        <span id="borrowDate">-</span>
                    </div>
                    <div class="borrow-info-row">
                        <span class="borrow-info-label">Due Date:</span>
                        <span id="dueDate">-</span>
                    </div>
                </div>

                <!-- ✅ CAMERA SCANNER -->
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 700; color: #1f2937; margin-bottom: 8px; font-size: 14px;">
                        <i class='bx bx-camera'></i> QR Code Scanner
                    </label>
                    <div id="qr-scanner-return"></div>
                    
                    <div class="scanner-info">
                        <i class='bx bx-info-circle'></i>
                        <span>Point camera at QR code to verify return</span>
                    </div>

                    <div class="scanner-controls">
                        <button type="button" class="stop-scanner-btn" id="stopScanBtn" onclick="stopReturnScanner()">
                            <i class='bx bx-stop'></i> Stop Scanner
                        </button>
                    </div>
                </div>

                <!-- ✅ VALIDATION MESSAGES -->
                <div class="validation-message error" id="errorMessage">
                    <i class='bx bx-x-circle'></i>
                    <div class="validation-content">
                        <div class="validation-title" id="errorTitle">Error</div>
                        <div class="validation-text" id="errorText"></div>
                    </div>
                </div>

                <div class="validation-message success" id="successMessage">
                    <i class='bx bx-check-circle'></i>
                    <div class="validation-content">
                        <div class="validation-title">QR Code Verified!</div>
                        <div class="validation-text">Asset identity confirmed. Fill in the details below.</div>
                    </div>
                </div>

                <!-- ✅ ASSET DETAILS -->
                <div id="assetDetailsBox" class="asset-details-box">
                    <h3><i class='bx bx-check-circle'></i> Asset Verified</h3>
                    <div class="asset-details-row">
                        <span class="asset-details-label">Asset Name:</span>
                        <span class="asset-details-value" id="detailAssetName"></span>
                    </div>
                    <div class="asset-details-row">
                        <span class="asset-details-label">Asset Code:</span>
                        <span class="asset-details-value" id="detailAssetCode"></span>
                    </div>
                    <div class="asset-details-row">
                        <span class="asset-details-label">Category:</span>
                        <span class="asset-details-value" id="detailCategory"></span>
                    </div>
                </div>

                <!-- Condition on Return -->
                <div class="modal-field">
                    <label>Asset Condition on Return</label>
                    <div class="condition-group">
                        <div class="condition-radio">
                            <input type="radio" id="condExcellent" name="assetCondition" value="Excellent">
                            <label for="condExcellent">Excellent</label>
                        </div>
                        <div class="condition-radio">
                            <input type="radio" id="condGood" name="assetCondition" value="Good" checked>
                            <label for="condGood">Good</label>
                        </div>
                        <div class="condition-radio">
                            <input type="radio" id="condFair" name="assetCondition" value="Fair">
                            <label for="condFair">Fair</label>
                        </div>
                        <div class="condition-radio">
                            <input type="radio" id="condPoor" name="assetCondition" value="Poor">
                            <label for="condPoor">Poor</label>
                        </div>
                        <div class="condition-radio">
                            <input type="radio" id="condDamaged" name="assetCondition" value="Damaged">
                            <label for="condDamaged">Damaged</label>
                        </div>
                    </div>
                </div>

                <!-- Return Notes -->
                <div class="modal-field">
                    <label>Return Notes (Optional)</label>
                    <textarea id="returnNotes" placeholder="Add any notes about the return..." style="min-height: 80px; resize: vertical;"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="modal-btn modal-btn-cancel" onclick="closeReturnModal()">Cancel</button>
                <button type="button" class="modal-btn modal-btn-confirm" id="confirmReturnBtn" onclick="submitReturn()" disabled>
                    Confirm Return
                </button>
            </div>
        </div>
    </div>

    <?php include 'includes/user_footer.php'; ?>
    <script src="js/user_dashboard.js"></script>
    
    <script>
        // ✅ GLOBAL VARIABLES
        let html5QrcodeScannerReturn = null;
        let scanningActiveReturn = false;
        let scannedQRCodeReturn = '';
        let currentReturnAsset = null;

        // ✅ INITIALIZE
        document.addEventListener('DOMContentLoaded', function() {
            const returnButtons = document.querySelectorAll('.asset-card-btn');
            returnButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    openReturnModal(this);
                });
            });
        });

        // ✅ OPEN RETURN MODAL
        function openReturnModal(button) {
            currentReturnAsset = {
                name: button.getAttribute('data-asset-name'),
                code: button.getAttribute('data-asset-code'),
                expectedQR: button.getAttribute('data-qr-code'),
                category: button.getAttribute('data-category'),
                borrowId: button.getAttribute('data-borrow-id'),
                borrowDate: button.getAttribute('data-borrow-date'),
                dueDate: button.getAttribute('data-due-date')
            };

            document.getElementById('modalAssetName').value = currentReturnAsset.name;
            document.getElementById('modalAssetCode').value = currentReturnAsset.code;
            document.getElementById('borrowDate').textContent = formatDate(currentReturnAsset.borrowDate);
            document.getElementById('dueDate').textContent = formatDate(currentReturnAsset.dueDate);
            
            // Reset state
            scannedQRCodeReturn = '';
            document.getElementById('assetDetailsBox').style.display = 'none';
            document.getElementById('confirmReturnBtn').disabled = true;
            document.getElementById('errorMessage').classList.remove('active');
            document.getElementById('successMessage').classList.remove('active');
            
            document.getElementById('returnNotes').value = '';
            document.getElementById('condGood').checked = true;

            document.getElementById('returnModal').classList.add('show');
            
            // ✅ START SCANNER IMMEDIATELY
            setTimeout(() => {
                startReturnScanner();
            }, 300);
        }

        // ✅ FORMAT DATE
        function formatDate(dateStr) {
            if (!dateStr || dateStr === 'null') return 'N/A';
            try {
                const date = new Date(dateStr);
                if (isNaN(date)) return 'N/A';
                return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
            } catch (e) {
                return 'N/A';
            }
        }

        // ✅ INITIALIZE SCANNER
        function initializeReturnScanner() {
            if (html5QrcodeScannerReturn) return;
            try {
                html5QrcodeScannerReturn = new Html5Qrcode("qr-scanner-return");
            } catch (error) {
                showReturnErrorMessage('Scanner Error', 'Failed to initialize camera: ' + error.message);
            }
        }

        // ✅ START SCANNER
        function startReturnScanner() {
            if (scanningActiveReturn) return;
            
            initializeReturnScanner();

            html5QrcodeScannerReturn.start(
                { facingMode: "environment" },
                {
                    fps: 15,
                    qrbox: { width: 250, height: 250 },
                    aspectRatio: 1.0
                },
                onReturnScanSuccess,
                onReturnScanFailure
            ).then(() => {
                scanningActiveReturn = true;
                console.log('✓ Return scanner started');
            }).catch(err => {
                showReturnErrorMessage('Camera Error', 'Please allow camera access: ' + err.message);
            });
        }

        // ✅ ON SCAN SUCCESS
        function onReturnScanSuccess(decodedText) {
            const scannedValue = decodedText.trim();
            console.log('Return Scanned:', scannedValue, 'Expected:', currentReturnAsset.expectedQR);

            if (scannedValue !== currentReturnAsset.expectedQR) {
                showReturnErrorMessage(
                    'Wrong QR Code',
                    `This QR code does not match this asset.<br><br><strong>Expected:</strong> ${currentReturnAsset.expectedQR}<br><strong>Scanned:</strong> ${scannedValue}`
                );
                return;
            }

            // ✅ CORRECT QR
            scannedQRCodeReturn = scannedValue;
            stopReturnScanner();
            
            document.getElementById('errorMessage').classList.remove('active');
            document.getElementById('successMessage').classList.add('active');
            
            displayReturnAssetDetails();
            document.getElementById('confirmReturnBtn').disabled = false;
        }

        // ✅ ON SCAN FAILURE
        function onReturnScanFailure(error) {
            // Silently ignore
        }

        // ✅ STOP SCANNER
        function stopReturnScanner() {
            if (!html5QrcodeScannerReturn || !scanningActiveReturn) return;

            html5QrcodeScannerReturn.stop().then(() => {
                html5QrcodeScannerReturn.clear();
                scanningActiveReturn = false;
                console.log('✓ Return scanner stopped');
            }).catch(err => {
                console.error('Error stopping scanner:', err);
            });
        }

        // ✅ DISPLAY ASSET DETAILS
        function displayReturnAssetDetails() {
            document.getElementById('detailAssetName').textContent = currentReturnAsset.name;
            document.getElementById('detailAssetCode').textContent = currentReturnAsset.code;
            document.getElementById('detailCategory').textContent = currentReturnAsset.category;
            document.getElementById('assetDetailsBox').style.display = 'block';
        }

        // ✅ SHOW ERROR MESSAGE
        function showReturnErrorMessage(title, message) {
            document.getElementById('errorTitle').textContent = title;
            document.getElementById('errorText').innerHTML = message;
            document.getElementById('errorMessage').classList.add('active');
            document.getElementById('successMessage').classList.remove('active');
        }

        // ✅ CLOSE MODAL
        function closeReturnModal() {
            stopReturnScanner();
            document.getElementById('returnModal').classList.remove('show');
        }

        // ✅ SUBMIT RETURN
        function submitReturn() {
            if (!scannedQRCodeReturn) {
                alert('Please scan QR code first');
                return;
            }

            const selectedCondition = document.querySelector('input[name="assetCondition"]:checked');
            if (!selectedCondition) {
                alert('Please select asset condition');
                return;
            }

            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="scan_return">
                <input type="hidden" name="qr_code" value="${scannedQRCodeReturn}">
                <input type="hidden" name="borrow_id" value="${currentReturnAsset.borrowId}">
                <input type="hidden" name="asset_condition_on_return" value="${selectedCondition.value}">
                <input type="hidden" name="return_notes" value="${document.getElementById('returnNotes').value}">
            `;
            document.body.appendChild(form);
            form.submit();
        }

        // ✅ CLOSE ON OUTSIDE CLICK
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('returnModal');
            if (event.target === modal) {
                closeReturnModal();
            }
        });
    </script>
</body>
</html>