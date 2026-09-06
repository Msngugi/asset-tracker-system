<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrow Asset - Asset Tracker</title>
    <link href='https://cdn.boxicons.com/3.0.6/fonts/basic/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="css/user_dashboard.css">
    <script src="https://unpkg.com/html5-qrcode@2.3.8"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
    <style>
        .available-assets-section {
            margin-bottom: 30px;
        }

        .available-assets-header {
            margin-bottom: 20px;
        }

        .available-assets-header h2 {
            font-size: 24px;
            color: #1f2937;
            margin-bottom: 8px;
        }

        .available-assets-header p {
            color: #6b7280;
            font-size: 14px;
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

        .condition-excellent {
            background: rgba(99, 102, 241, 0.2);
            color: #6366f1;
        }

        .condition-good {
            background: rgba(34, 197, 94, 0.2);
            color: #16a34a;
        }

        .condition-fair {
            background: rgba(251, 191, 36, 0.2);
            color: #b45309;
        }

        .asset-card-btn {
            width: 100%;
            padding: 10px;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 13px;
        }

        .asset-card-btn:hover {
            background: linear-gradient(135deg, #059669, #047857);
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
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-close:hover {
            color: #1f2937;
        }

        .modal-body {
            margin-bottom: 30px;
        }

        /* ✅ CAMERA SCANNER */
        #qr-scanner {
            width: 100%;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 15px;
            min-height: 350px;
            background: #000;
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

        .scanner-info i {
            font-size: 18px;
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
            margin-top: 2px;
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
            margin-top: 2px;
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
            background: white;
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
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
        }

        .modal-btn-confirm:hover:not(:disabled) {
            background: linear-gradient(135deg, #1d4ed8, #1e40af);
            transform: translateY(-2px);
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

            .modal-header h2 {
                font-size: 20px;
            }

            .condition-group {
                gap: 15px;
            }

            .modal-footer {
                flex-direction: column;
            }

            .modal-btn {
                width: 100%;
            }

            #qr-scanner {
                min-height: 300px;
            }
        }

        .hidden-canvas {
            display: none;
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
                    <h1><i class='bx bx-package'></i> Available Assets</h1>
                    <p>Click "Borrow Now" to scan QR code and get started</p>
                </div>

                <div class="available-assets-section">
                    <?php if ($available_assets_result->num_rows > 0): ?>
                        <div class="assets-grid">
                            <?php while ($asset = $available_assets_result->fetch_assoc()): ?>
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
                                                <i class='bx bx-chip'></i>
                                                <span><?php echo htmlspecialchars($asset['asset_model'] ?? 'N/A'); ?></span>
                                            </div>
                                            <div class="asset-card-detail">
                                                <i class='bx bx-barcode'></i>
                                                <span><?php echo htmlspecialchars(substr($asset['serial_number'] ?? 'N/A', 0, 15)); ?></span>
                                            </div>
                                        </div>

                                        <span class="asset-card-condition condition-<?php echo strtolower($asset['asset_condition'] ?? 'good'); ?>">
                                            <?php echo htmlspecialchars($asset['asset_condition'] ?? 'Good'); ?>
                                        </span>

                                        <button type="button" class="asset-card-btn" 
                                            data-asset-name="<?php echo htmlspecialchars($asset['asset_name']); ?>" 
                                            data-asset-code="<?php echo htmlspecialchars($asset['asset_code']); ?>" 
                                            data-qr-code="<?php echo htmlspecialchars($asset['qr_code'] ?? ''); ?>"
                                            data-category="<?php echo htmlspecialchars($asset['category_name'] ?? 'N/A'); ?>"
                                            data-model="<?php echo htmlspecialchars($asset['asset_model'] ?? 'N/A'); ?>">
                                            <i class='bx bx-download'></i> Borrow Now
                                        </button>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-assets">
                            <i class='bx bx-package'></i>
                            <h3>No Available Assets</h3>
                            <p>All assets are currently borrowed. Please check back later.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <!-- ✅ BORROW MODAL -->
    <div id="borrowModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class='bx bx-qr-scan'></i> Borrow Asset</h2>
                <button type="button" class="modal-close" onclick="closeBorrowModal()">&times;</button>
            </div>

            <div class="modal-body">
                <!-- Asset Name -->
                <div class="modal-field">
                    <label>Asset Name</label>
                    <input type="text" id="modalAssetName" readonly>
                </div>

                <!-- Asset Code -->
                <div class="modal-field">
                    <label>Asset Code</label>
                    <input type="text" id="modalAssetCode" readonly>
                </div>

                <!-- ✅ CAMERA SCANNER -->
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 700; color: #1f2937; margin-bottom: 8px; font-size: 14px;">
                        <i class='bx bx-camera'></i> QR Code Scanner
                    </label>
                    <div id="qr-scanner"></div>
                    
                    <div class="scanner-info">
                        <i class='bx bx-info-circle'></i>
                        <span>Point camera at QR code to scan</span>
                    </div>

                    <div class="scanner-controls">
                        <button type="button" class="stop-scanner-btn" id="stopScanBtn" onclick="stopBorrowScanner()">
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
                    <div class="asset-details-row">
                        <span class="asset-details-label">Model:</span>
                        <span class="asset-details-value" id="detailModel"></span>
                    </div>
                </div>

                <!-- Asset Condition on Borrow -->
                <div class="modal-field">
                    <label>Asset Condition on Borrow</label>
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

                <!-- Borrow Duration -->
                <div class="modal-field">
                    <label>Number of Days <span style="color: #ef4444;">*</span></label>
                    <select id="borrowDays">
                        <option value="1">1 day</option>
                        <option value="3">3 days</option>
                        <option value="5">5 days</option>
                        <option value="7" selected>7 days (Default)</option>
                        <option value="14">14 days</option>
                        <option value="30">30 days</option>
                    </select>
                </div>

                <!-- Remarks -->
                <div class="modal-field">
                    <label>Remarks (Optional)</label>
                    <textarea id="remarks" placeholder="Enter any additional notes..." style="min-height: 80px; resize: vertical;"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="modal-btn modal-btn-cancel" onclick="closeBorrowModal()">Cancel</button>
                <button type="button" class="modal-btn modal-btn-confirm" id="confirmBorrowBtn" onclick="submitBorrow()" disabled>
                    Borrow Asset
                </button>
            </div>
        </div>
    </div>

    <canvas id="qrCanvas" class="hidden-canvas"></canvas>

    <?php include 'includes/user_footer.php'; ?>
    <script src="js/user_dashboard.js"></script>
    
    <script>
        // ✅ GLOBAL VARIABLES
        let html5QrcodeScanner = null;
        let scanningActive = false;
        let scannedQRCode = '';
        let currentAsset = null;

        // ✅ INITIALIZE
        document.addEventListener('DOMContentLoaded', function() {
            const borrowButtons = document.querySelectorAll('.asset-card-btn');
            borrowButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    openBorrowModal(this);
                });
            });
        });

        // ✅ OPEN BORROW MODAL
        function openBorrowModal(button) {
            currentAsset = {
                name: button.getAttribute('data-asset-name'),
                code: button.getAttribute('data-asset-code'),
                expectedQR: button.getAttribute('data-qr-code'),
                category: button.getAttribute('data-category'),
                model: button.getAttribute('data-model')
            };

            document.getElementById('modalAssetName').value = currentAsset.name;
            document.getElementById('modalAssetCode').value = currentAsset.code;
            
            // Reset state
            scannedQRCode = '';
            document.getElementById('assetDetailsBox').style.display = 'none';
            document.getElementById('confirmBorrowBtn').disabled = true;
            document.getElementById('errorMessage').classList.remove('active');
            document.getElementById('successMessage').classList.remove('active');
            
            document.getElementById('borrowDays').value = '7';
            document.getElementById('remarks').value = '';
            document.getElementById('condGood').checked = true;

            document.getElementById('borrowModal').classList.add('show');
            
            // ✅ START SCANNER IMMEDIATELY
            setTimeout(() => {
                startBorrowScanner();
            }, 300);
        }

        // ✅ INITIALIZE SCANNER
        function initializeBorrowScanner() {
            if (html5QrcodeScanner) return;
            try {
                html5QrcodeScanner = new Html5Qrcode("qr-scanner");
            } catch (error) {
                showBorrowErrorMessage('Scanner Error', 'Failed to initialize camera: ' + error.message);
            }
        }

        // ✅ START SCANNER
        function startBorrowScanner() {
            if (scanningActive) return;
            
            initializeBorrowScanner();

            html5QrcodeScanner.start(
                { facingMode: "environment" },
                {
                    fps: 15,
                    qrbox: { width: 250, height: 250 },
                    aspectRatio: 1.0
                },
                onBorrowScanSuccess,
                onBorrowScanFailure
            ).then(() => {
                scanningActive = true;
                console.log('✓ Scanner started');
            }).catch(err => {
                showBorrowErrorMessage('Camera Error', 'Please allow camera access: ' + err.message);
            });
        }

        // ✅ ON SCAN SUCCESS
        function onBorrowScanSuccess(decodedText) {
            const scannedValue = decodedText.trim();
            console.log('Scanned:', scannedValue, 'Expected:', currentAsset.expectedQR);

            if (scannedValue !== currentAsset.expectedQR) {
                showBorrowErrorMessage(
                    'Wrong QR Code',
                    `This QR code does not match this asset.<br><br><strong>Expected:</strong> ${currentAsset.expectedQR}<br><strong>Scanned:</strong> ${scannedValue}`
                );
                return;
            }

            // ✅ CORRECT QR
            scannedQRCode = scannedValue;
            stopBorrowScanner();
            
            document.getElementById('errorMessage').classList.remove('active');
            document.getElementById('successMessage').classList.add('active');
            
            displayAssetDetails();
            document.getElementById('confirmBorrowBtn').disabled = false;
        }

        // ✅ ON SCAN FAILURE
        function onBorrowScanFailure(error) {
            // Silently ignore
        }

        // ✅ STOP SCANNER
        function stopBorrowScanner() {
            if (!html5QrcodeScanner || !scanningActive) return;

            html5QrcodeScanner.stop().then(() => {
                html5QrcodeScanner.clear();
                scanningActive = false;
                console.log('✓ Scanner stopped');
            }).catch(err => {
                console.error('Error stopping scanner:', err);
            });
        }

        // ✅ DISPLAY ASSET DETAILS
        function displayAssetDetails() {
            document.getElementById('detailAssetName').textContent = currentAsset.name;
            document.getElementById('detailAssetCode').textContent = currentAsset.code;
            document.getElementById('detailCategory').textContent = currentAsset.category;
            document.getElementById('detailModel').textContent = currentAsset.model;
            document.getElementById('assetDetailsBox').style.display = 'block';
        }

        // ✅ SHOW ERROR MESSAGE
        function showBorrowErrorMessage(title, message) {
            document.getElementById('errorTitle').textContent = title;
            document.getElementById('errorText').innerHTML = message;
            document.getElementById('errorMessage').classList.add('active');
            document.getElementById('successMessage').classList.remove('active');
        }

        // ✅ CLOSE MODAL
        function closeBorrowModal() {
            stopBorrowScanner();
            document.getElementById('borrowModal').classList.remove('show');
        }

        // ✅ SUBMIT BORROW
        function submitBorrow() {
            if (!scannedQRCode) {
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
                <input type="hidden" name="action" value="scan_borrow">
                <input type="hidden" name="qr_code" value="${scannedQRCode}">
                <input type="hidden" name="borrow_days" value="${document.getElementById('borrowDays').value}">
                <input type="hidden" name="asset_condition_on_borrow" value="${selectedCondition.value}">
                <input type="hidden" name="remarks" value="${document.getElementById('remarks').value}">
            `;
            document.body.appendChild(form);
            form.submit();
        }

        // ✅ CLOSE ON OUTSIDE CLICK
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('borrowModal');
            if (event.target === modal) {
                closeBorrowModal();
            }
        });
    </script>
</body>

</html>