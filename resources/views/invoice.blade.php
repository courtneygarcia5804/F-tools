<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professional Online Invoice Generator | F-Tools</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #818cf8;
            --accent: #22d3ee;
            --bg-dark: #0f0f23;
            --bg-card: rgba(30, 30, 60, 0.6);
            --text-primary: #ffffff;
            --text-secondary: #a1a1aa;
            --border: rgba(255, 255, 255, 0.1);
            --shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            
            /* Invoice Preview Styles */
            --inv-bg: #ffffff;
            --inv-text: #1f2937;
            --inv-primary: #111827;
            --inv-accent: #6366f1;
            --inv-border: #e5e7eb;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-dark);
            color: var(--text-primary);
            min-height: 100vh;
            overflow-x: hidden;
        }

        .bg-pattern {
            position: fixed;
            inset: 0;
            background: 
                radial-gradient(circle at 20% 80%, rgba(99, 102, 241, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(34, 211, 238, 0.1) 0%, transparent 50%);
            z-index: -1;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
            display: flex;
            gap: 2rem;
            flex-direction: row;
        }

        /* Editor Section */
        .editor-section {
            flex: 1;
            max-width: 600px;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .card {
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary-light);
        }

        .form-group { margin-bottom: 1.25rem; }
        label { display: block; font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 0.5rem; }
        input, select, textarea {
            width: 100%;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 0.75rem;
            color: white;
            font-family: inherit;
            transition: all 0.3s;
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
        }

        /* Items Editor Styling */
        .items-editor-container { display: flex; flex-direction: column; gap: 1rem; margin-top: 1rem; }
        .item-editor-block { 
            background: rgba(255, 255, 255, 0.03); 
            border: 1px solid var(--border); 
            border-radius: 12px; 
            padding: 1rem; 
            position: relative; 
        }
        .item-editor-grid { 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: 1rem; 
            margin-top: 0.75rem; 
        }
        .btn-remove-item {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            color: #ef4444;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
            font-size: 1rem;
            opacity: 0.6;
            transition: opacity 0.3s;
        }
        .btn-remove-item:hover { opacity: 1; }
        .btn-add { background: none; border: 1px dashed var(--primary); color: var(--primary-light); width: 100%; padding: 0.75rem; border-radius: 8px; cursor: pointer; margin-top: 1rem; transition: all 0.3s; }
        .btn-add:hover { background: rgba(99, 102, 241, 0.1); border-style: solid; }

        /* Preview Section */
        .preview-section {
            flex: 1.2;
            position: sticky;
            top: 2rem;
            height: calc(100vh - 4rem);
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .preview-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--bg-card);
            padding: 1rem;
            border-radius: 12px;
            border: 1px solid var(--border);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            font-size: 0.875rem;
        }
        .btn-primary { background: linear-gradient(135deg, var(--primary), var(--primary-dark)); color: white; }
        .btn-secondary { background: rgba(255, 255, 255, 0.1); color: white; }
        .btn:hover { transform: translateY(-1px); box-shadow: 0 5px 15px rgba(0,0,0,0.3); }

        /* A4 Invoice Style */
        .invoice-preview-wrapper {
            background: #2a2a4a;
            padding: 2rem;
            border-radius: 12px;
            overflow-y: auto;
            display: flex;
            justify-content: center;
            flex: 1;
        }

        .invoice-a4 {
            width: 210mm;
            min-height: 297mm;
            background: var(--inv-bg);
            color: var(--inv-text);
            padding: 20mm;
            box-shadow: 0 10px 40px rgba(0,0,0,0.5);
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .invoice-header { display: flex; justify-content: space-between; margin-bottom: 3rem; }
        .company-info h2 { font-size: 1.5rem; font-weight: 700; color: var(--inv-primary); margin-bottom: 0.5rem; }
        .company-info p { font-size: 0.875rem; color: #6b7280; line-height: 1.4; }

        .invoice-title-block { text-align: right; }
        .invoice-title { font-size: 2.5rem; font-weight: 800; color: var(--inv-primary); text-transform: uppercase; letter-spacing: -0.02em; margin-bottom: 0.5rem; }
        .status-badge { display: inline-block; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; }
        .status-draft { background: #f3f4f6; color: #374151; }
        .status-unpaid { background: #fee2e2; color: #b91c1c; }
        .status-paid { background: #d1fae5; color: #065f46; }
        .status-overdue { background: #fef3c7; color: #92400e; }

        .invoice-details { display: flex; justify-content: space-between; margin-bottom: 3rem; gap: 2rem; }
        .details-col { flex: 1; }
        .details-col h3 { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #9ca3af; margin-bottom: 0.75rem; letter-spacing: 0.05em; }
        .details-col p { font-size: 0.9375rem; font-weight: 600; color: var(--inv-primary); }
        .details-col .client-name { font-size: 1.125rem; }

        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 2rem; }
        .items-table th { padding: 0.75rem 0; border-bottom: 2px solid var(--inv-primary); text-align: left; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; }
        .items-table td { padding: 1rem 0; border-bottom: 1px solid var(--inv-border); font-size: 0.875rem; }
        .item-row:last-child td { border-bottom: none; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .w-16 { width: 4rem; }
        .w-32 { width: 8rem; }

        .invoice-summary { display: flex; justify-content: flex-end; margin-bottom: 3rem; }
        .summary-table { width: 250px; }
        .summary-row { display: flex; justify-content: space-between; padding: 0.5rem 0; }
        .summary-row.total { border-top: 2px solid var(--inv-primary); margin-top: 0.5rem; padding-top: 1rem; }
        .summary-row.total .label { font-weight: 800; font-size: 1rem; color: var(--inv-primary); }
        .summary-row.total .value { font-weight: 800; font-size: 1.25rem; color: var(--inv-accent); }
        .summary-row .label { color: #6b7280; font-size: 0.875rem; }
        .summary-row .value { color: var(--inv-primary); font-weight: 600; }

        .invoice-bottom { display: flex; justify-content: space-between; margin-top: auto; border-top: 1px solid var(--inv-border); padding-top: 2rem; }
        .payment-info { flex: 1.5; }
        .payment-info h4 { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #9ca3af; margin-bottom: 0.5rem; }
        .payment-info p { font-size: 0.8125rem; color: #4b5563; line-height: 1.5; }
        .payment-details { margin-top: 0.5rem; display: grid; grid-template-columns: auto 1fr; gap: 0.25rem 1rem; }
        .payment-details span:first-child { font-weight: 600; color: #6b7280; }

        .signature-block { flex: 1; text-align: right; display: flex; flex-direction: column; align-items: flex-end; }
        .signature-img { max-width: 150px; max-height: 80px; margin-bottom: 0.5rem; }
        .signature-line { width: 200px; border-top: 1px solid var(--inv-primary); margin-top: 2rem; }
        .signatory-name { font-weight: 700; color: var(--inv-primary); font-size: 0.9rem; margin-top: 0.5rem; }
        .signatory-title { color: #6b7280; font-size: 0.75rem; }

        .invoice-footer { margin-top: 2rem; text-align: center; color: #9ca3af; font-size: 0.75rem; }

        @media print {
            body { background: white !important; color: black !important; }
            .bg-pattern, .editor-section, .preview-controls { display: none !important; }
            .container { padding: 0 !important; margin: 0 !important; width: 100% !important; max-width: none !important; display: block !important; }
            .preview-section { position: static !important; height: auto !important; padding: 0 !important; width: 100% !important; display: block !important; }
            .invoice-preview-wrapper { background: none !important; padding: 0 !important; overflow: visible !important; display: block !important; }
            .invoice-a4 { box-shadow: none !important; padding: 0 !important; margin: 0 auto !important; width: 210mm !important; min-height: 297mm !important; }
        }

        @media (max-width: 1200px) {
            .container { flex-direction: column; }
            .editor-section { max-width: none; }
            .preview-section { position: static; height: auto; }
            .invoice-preview-wrapper { overflow-x: auto; }
        }
    </style>
</head>
<body>
    <div class="bg-pattern"></div>
    
    <div class="container">
        <!-- Editor Section -->
        <div class="editor-section">
            <header style="margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: flex-end;">
                <div>
                    <h1 style="font-size: 1.5rem;">Online Invoice Generator</h1>
                    <p style="color: var(--text-secondary); font-size: 0.875rem;">Professional tool for freelancers & SMEs</p>
                </div>
                <button class="btn btn-secondary" onclick="loadSampleData()" style="padding: 0.4rem 0.8rem; font-size: 0.75rem; background: rgba(99, 102, 241, 0.1); border: 1px solid var(--primary);">🧪 Load Sample</button>
            </header>

            <!-- Organization Info -->
            <div class="card">
                <div class="card-title">🏢 Organization Details</div>
                <div class="form-group">
                    <label>Business Name</label>
                    <input type="text" id="input-org-name" placeholder="My Awesome Business" value="My Business Name">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Address & Contact (Multi-line)</label>
                    <textarea id="input-org-address" rows="3" placeholder="456 Artisan St, Suite 100&#10;City, State 7890&#10;contact@business.com">456 Artisan St, Suite 100
City, State 7890
contact@business.com</textarea>
                </div>
            </div>

            <!-- Page Settings -->
            <div class="card">
                <div class="card-title">⚙️ Display Settings</div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" id="check-show-payment" checked style="width: auto;">
                        <label style="margin: 0;">Payment Details</label>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" id="check-show-notes" checked style="width: auto;">
                        <label style="margin: 0;">Notes</label>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" id="check-show-signature" checked style="width: auto;">
                        <label style="margin: 0;">Signature Block</label>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" id="check-show-due-date" checked style="width: auto;">
                        <label style="margin: 0;">Due Date</label>
                    </div>
                </div>
            </div>

            <!-- Basic Info -->
            <div class="card">
                <div class="card-title">📄 Basic Information</div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>Invoice Number</label>
                        <input type="text" id="input-inv-no" placeholder="INV-2024-001" value="INV-2026-001">
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select id="input-status">
                            <option value="draft">Draft</option>
                            <option value="unpaid" selected>Unpaid</option>
                            <option value="paid">Paid</option>
                            <option value="overdue">Overdue</option>
                        </select>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>Issue Date</label>
                        <input type="date" id="input-date" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="form-group">
                        <label>Currency</label>
                        <select id="input-currency">
                            <option value="IDR" selected>IDR (Rupiah)</option>
                            <option value="USD">USD (Dollar)</option>
                            <option value="EUR">EUR (Euro)</option>
                            <option value="GBP">GBP (Pound)</option>
                            <option value="JPY">JPY (Yen)</option>
                        </select>
                    </div>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Due Date</label>
                    <input type="date" id="input-due-date" value="{{ date('Y-m-d', strtotime('+7 days')) }}">
                </div>
            </div>

            <!-- Client Info -->
            <div class="card">
                <div class="card-title">👥 Client Information</div>
                <div class="form-group">
                    <label>Client Name</label>
                    <input type="text" id="input-client-name" placeholder="ABC Corp or John Doe">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Client Address / Contact</label>
                    <textarea id="input-client-address" rows="3" placeholder="123 Business Rd, Building A&#10;contact@client.com"></textarea>
                </div>
            </div>

            <!-- Items -->
            <div class="card">
                <div class="card-title">🛍️ Invoice Items</div>
                <div id="items-editor-list" class="items-editor-container">
                    <!-- Items will be added here -->
                </div>
                <button class="btn-add" id="btn-add-item">+ Add New Item</button>
            </div>

            <!-- Payment Info -->
            <div class="card">
                <div class="card-title">🏦 Payment Details</div>
                <div class="form-group">
                    <label>Bank Name</label>
                    <input type="text" id="input-bank" placeholder="Global Bank">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>Account Number</label>
                        <input type="text" id="input-account" placeholder="1234567890">
                    </div>
                    <div class="form-group">
                        <label>Account Name</label>
                        <input type="text" id="input-account-name" placeholder="John Freelancer">
                    </div>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Notes / Instructions</label>
                    <textarea id="input-notes" rows="2" placeholder="Payment terms, special instructions, etc."></textarea>
                </div>
            </div>

            <!-- Signature -->
            <div class="card">
                <div class="card-title">✍️ Signature Section</div>
                <div class="form-group">
                    <label>Signatory Name</label>
                    <input type="text" id="input-signatory-name" placeholder="Authorized Person">
                </div>
                <div class="form-group">
                    <label>Position / Title</label>
                    <input type="text" id="input-signatory-title" placeholder="CEO / Director">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Signature Image (Optional)</label>
                    <input type="file" id="input-signature-img" accept="image/*">
                </div>
            </div>
            
            <footer style="margin-top: 1rem; color: var(--text-secondary); text-align: center; font-size: 0.75rem;">
                Professional Invoice Generator by F-Tools &copy; 2026
            </footer>
        </div>

        <!-- Preview Section -->
        <div class="preview-section">
            <div class="preview-controls">
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    <span style="font-weight: 600; font-size: 0.875rem;">Live Preview</span>
                    <span id="validation-msg" style="color: #fbbf24; font-size: 0.75rem; font-weight: 500;"></span>
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <button class="btn btn-secondary" onclick="window.print()">🖨️ Print / PDF</button>
                    <button class="btn btn-primary" id="btn-export-png">🖼️ Export PNG</button>
                </div>
            </div>

            <div class="invoice-preview-wrapper">
                <div class="invoice-a4" id="invoice-render">
                    <div class="invoice-header">
                        <div class="company-info" id="preview-org-block">
                            <h2 id="preview-org-name">My Business Name</h2>
                            <p id="preview-org-address" style="white-space: pre-line;">456 Artisan St, Suite 100<br>City, State 7890<br>contact@business.com</p>
                        </div>
                        <div class="invoice-title-block">
                            <div class="invoice-title">Invoice</div>
                            <span class="status-badge status-unpaid" id="preview-status">Unpaid</span>
                        </div>
                    </div>

                    <div class="invoice-details">
                        <div class="details-col">
                            <h3>Bill To</h3>
                            <p class="client-name" id="preview-client-name">Client Name</p>
                            <p id="preview-client-address" style="white-space: pre-line; font-weight: 400; font-size: 0.875rem; margin-top: 0.25rem;">Client Address</p>
                        </div>
                        <div class="details-col" style="text-align: right;">
                            <div>
                                <h3>Invoice Number</h3>
                                <p id="preview-inv-no">INV-2026-001</p>
                            </div>
                            <div style="margin-top: 1.5rem;">
                                <h3>Date Issued</h3>
                                <p id="preview-date">Feb 23, 2026</p>
                            </div>
                            <div style="margin-top: 1.5rem;" id="preview-due-date-block">
                                <h3>Due Date</h3>
                                <p id="preview-due-date">Mar 02, 2026</p>
                            </div>
                        </div>
                    </div>

                    <table class="items-table">
                        <thead>
                            <tr>
                                <th style="padding-right: 1rem;">Description</th>
                                <th class="text-right w-32" style="padding-right: 0.5rem;">Price</th>
                                <th class="text-center w-16">Qty</th>
                                <th class="text-right w-32">Total</th>
                            </tr>
                        </thead>
                        <tbody id="preview-items-body">
                            <!-- Items will be rendered here -->
                        </tbody>
                    </table>

                    <div class="invoice-summary">
                        <div class="summary-table">
                            <div class="summary-row">
                                <span class="label">Subtotal</span>
                                <span class="value" id="preview-subtotal">$0.00</span>
                            </div>
                            <div class="summary-row total">
                                <span class="label">Total Amount</span>
                                <span class="value" id="preview-total">$0.00</span>
                            </div>
                        </div>
                    </div>

                    <div class="invoice-bottom">
                        <div class="payment-info">
                            <div id="preview-payment-block">
                                <h4>Payment Information</h4>
                                <div class="payment-details">
                                    <span>Bank</span> <span id="preview-bank">-</span>
                                    <span>Account No.</span> <span id="preview-account">-</span>
                                    <span>Account Holder</span> <span id="preview-account-name">-</span>
                                </div>
                            </div>
                            <div style="margin-top: 1rem;" id="preview-notes-block">
                                <h4>Notes</h4>
                                <p id="preview-notes">-</p>
                            </div>
                        </div>
                        <div class="signature-block" id="preview-signature-block">
                            <div id="preview-signature-container">
                                <div class="signature-line"></div>
                            </div>
                            <div class="signatory-name" id="preview-signatory-name">Authorized Signatory</div>
                            <div class="signatory-title" id="preview-signatory-title">Authorized Person</div>
                        </div>
                    </div>

                    <div class="invoice-footer">
                        Thank you for your business!
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Init state
        const state = {
            invNo: 'INV-2026-001',
            status: 'unpaid',
            currency: 'IDR',
            date: '{{ date('Y-m-d') }}',
            dueDate: '{{ date('Y-m-d', strtotime('+7 days')) }}',
            clientName: '',
            clientAddress: '',
            bank: '',
            account: '',
            accountName: '',
            notes: '',
            signatoryName: '',
            signatoryTitle: '',
            orgName: 'My Business Name',
            orgAddress: '456 Artisan St, Suite 100\nCity, State 7890\ncontact@business.com',
            showPayment: true,
            showNotes: true,
            showSignature: true,
            showDueDate: true,
            signatureImg: null,
            items: [
                { description: 'Professional Service', qty: 1, price: 0 }
            ]
        };

        // DOM Elements mapping
        const inputs = {
            invNo: document.getElementById('input-inv-no'),
            status: document.getElementById('input-status'),
            currency: document.getElementById('input-currency'),
            date: document.getElementById('input-date'),
            dueDate: document.getElementById('input-due-date'),
            clientName: document.getElementById('input-client-name'),
            clientAddress: document.getElementById('input-client-address'),
            bank: document.getElementById('input-bank'),
            account: document.getElementById('input-account'),
            accountName: document.getElementById('input-account-name'),
            notes: document.getElementById('input-notes'),
            signatoryName: document.getElementById('input-signatory-name'),
            signatoryTitle: document.getElementById('input-signatory-title'),
            orgName: document.getElementById('input-org-name'),
            orgAddress: document.getElementById('input-org-address'),
            checkPayment: document.getElementById('check-show-payment'),
            checkNotes: document.getElementById('check-show-notes'),
            checkSignature: document.getElementById('check-show-signature'),
            checkDueDate: document.getElementById('check-show-due-date'),
            signatureImg: document.getElementById('input-signature-img')
        };

        const previews = {
            invNo: document.getElementById('preview-inv-no'),
            status: document.getElementById('preview-status'),
            date: document.getElementById('preview-date'),
            dueDate: document.getElementById('preview-due-date'),
            clientName: document.getElementById('preview-client-name'),
            clientAddress: document.getElementById('preview-client-address'),
            bank: document.getElementById('preview-bank'),
            account: document.getElementById('preview-account'),
            accountName: document.getElementById('preview-account-name'),
            notes: document.getElementById('preview-notes'),
            signatoryName: document.getElementById('preview-signatory-name'),
            signatoryTitle: document.getElementById('preview-signatory-title'),
            subtotal: document.getElementById('preview-subtotal'),
            total: document.getElementById('preview-total'),
            itemsBody: document.getElementById('preview-items-body'),
            signatureContainer: document.getElementById('preview-signature-container')
        };

        // Initialize Items
        function renderEditorItems() {
            const container = document.getElementById('items-editor-list');
            container.innerHTML = '';
            state.items.forEach((item, index) => {
                const div = document.createElement('div');
                div.className = 'item-editor-block';
                div.innerHTML = `
                    <button class="btn-remove-item" onclick="removeItem(${index})" title="Remove Item">✕</button>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Description</label>
                        <textarea oninput="updateItem(${index}, 'description', this.value)" placeholder="e.g. Graphic Design Services" rows="2" style="font-size: 0.875rem; resize: none;">${item.description}</textarea>
                    </div>
                    <div class="item-editor-grid">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Quantity</label>
                            <input type="number" value="${item.qty}" oninput="updateItem(${index}, 'qty', this.value)" style="text-align: center;">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Price</label>
                            <input type="number" value="${item.price}" oninput="updateItem(${index}, 'price', this.value)">
                        </div>
                    </div>
                `;
                container.appendChild(div);
            });
            updatePreview();
        }

        window.updateItem = (index, field, value) => {
            state.items[index][field] = field === 'description' ? value : parseFloat(value) || 0;
            updatePreview();
        };

        window.removeItem = (index) => {
            if (state.items.length > 1) {
                state.items.splice(index, 1);
                renderEditorItems();
            }
        };

        document.getElementById('btn-add-item').onclick = () => {
            state.items.push({ description: '', qty: 1, price: 0 });
            renderEditorItems();
        };

        // Event Listeners for inputs
        Object.keys(inputs).forEach(key => {
            if (key === 'signatureImg') {
                inputs[key].onchange = (e) => {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = (re) => {
                            state.signatureImg = re.target.result;
                            updatePreview();
                        };
                        reader.readAsDataURL(file);
                    }
                };
            } else if (key.startsWith('check')) {
                const stateKey = 'show' + key.replace('check', '');
                inputs[key].onchange = (e) => {
                    state[stateKey] = e.target.checked;
                    updatePreview();
                };
            } else {
                inputs[key].oninput = (e) => {
                    state[key] = e.target.value;
                    updatePreview();
                };
            }
        });

        // Update Preview
        function updatePreview() {
            previews.invNo.innerText = state.invNo || 'INV-000';
            
            // Org Info
            document.getElementById('preview-org-name').innerText = state.orgName || 'My Business';
            document.getElementById('preview-org-address').innerText = state.orgAddress || '';

            // Visibility Toggles
            document.getElementById('preview-payment-block').style.display = state.showPayment ? 'block' : 'none';
            document.getElementById('preview-notes-block').style.display = state.showNotes ? 'block' : 'none';
            document.getElementById('preview-signature-block').style.display = state.showSignature ? 'flex' : 'none';
            document.getElementById('preview-due-date-block').style.display = state.showDueDate ? 'block' : 'none';

            previews.clientName.innerText = state.clientName || 'Client Name';
            previews.clientName.style.borderBottom = state.clientName ? 'none' : '1px dashed #ccc';
            previews.clientAddress.innerText = state.clientAddress || 'Client Address';
            
            previews.date.innerText = formatDate(state.date);
            previews.dueDate.innerText = formatDate(state.dueDate);
            
            // Status
            previews.status.innerText = state.status;
            previews.status.className = `status-badge status-${state.status}`;
            
            // Payment
            previews.bank.innerText = state.bank || '-';
            previews.account.innerText = state.account || '-';
            previews.accountName.innerText = state.accountName || '-';
            previews.notes.innerText = state.notes || '-';
            
            // Signatory
            previews.signatoryName.innerText = state.signatoryName || 'Authorized Signatory';
            previews.signatoryTitle.innerText = state.signatoryTitle || 'Authorized Person';
            
            if (state.signatureImg) {
                previews.signatureContainer.innerHTML = `<img src="${state.signatureImg}" class="signature-img">`;
            } else {
                previews.signatureContainer.innerHTML = `<div class="signature-line"></div>`;
            }

            // Items table
            previews.itemsBody.innerHTML = '';
            let subtotal = 0;
            state.items.forEach(item => {
                const total = (item.qty || 0) * (item.price || 0);
                subtotal += total;
                const tr = document.createElement('tr');
                tr.className = 'item-row';
                tr.innerHTML = `
                    <td style="padding-right: 1rem; white-space: pre-line;">${item.description || 'New Service'}</td>
                    <td class="text-right w-32" style="padding-right: 0.5rem;">${formatCurrency(item.price)}</td>
                    <td class="text-center w-16">${item.qty}</td>
                    <td class="text-right w-32">${formatCurrency(total)}</td>
                `;
                previews.itemsBody.appendChild(tr);
            });

            previews.subtotal.innerText = formatCurrency(subtotal);
            previews.total.innerText = formatCurrency(subtotal);
            
            // Validation
            validate();
        }

        function formatCurrency(val) {
            const locales = {
                'IDR': 'id-ID',
                'USD': 'en-US',
                'EUR': 'de-DE',
                'GBP': 'en-GB',
                'JPY': 'ja-JP'
            };
            return new Intl.NumberFormat(locales[state.currency] || 'en-US', { 
                style: 'currency', 
                currency: state.currency 
            }).format(val);
        }

        function formatDate(dateStr) {
            if (!dateStr) return '-';
            const date = new Date(dateStr);
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const d = String(date.getDate()).padStart(2, '0');
            const m = months[date.getMonth()];
            const y = date.getFullYear();
            return `${d} ${m} ${y}`;
        }

        function validate() {
            const msgs = [];
            if (!state.invNo) msgs.push("Invoice # required");
            if (!state.clientName) msgs.push("Client Name required");
            if (state.items.length === 0 || (state.items.length === 1 && !state.items[0].description)) msgs.push("Item required");
            
            const msgEl = document.getElementById('validation-msg');
            if (msgs.length > 0) {
                msgEl.innerText = "⚠ " + msgs[0];
                return false;
            } else {
                msgEl.innerText = "";
                return true;
            }
        }

        // Export PNG
        document.getElementById('btn-export-png').onclick = async () => {
            if (!validate()) {
                alert('Please fill in all required fields (Invoice #, Client Name, and at least one Item).');
                return;
            }

            const btn = document.getElementById('btn-export-png');
            btn.innerText = '⌛ Exporting...';
            btn.disabled = true;

            try {
                const element = document.getElementById('invoice-render');
                const canvas = await html2canvas(element, {
                    scale: 3, // High resolution
                    useCORS: true,
                    backgroundColor: '#ffffff',
                    windowWidth: 1200 // Ensure consistent rendering
                });
                
                const link = document.createElement('a');
                link.download = `Invoice-${state.invNo || 'draft'}.png`;
                link.href = canvas.toDataURL('image/png');
                link.click();
            } catch (err) {
                console.error(err);
                alert('Export failed');
            } finally {
                btn.innerText = '🖼️ Export PNG';
                btn.disabled = false;
            }
        };

        // Add sample data support
        window.loadSampleData = () => {
            state.currency = 'IDR';
            state.orgName = 'F-Tools Creative';
            state.orgAddress = 'Gedung Cyber 2, Lt. 15\nKuningan, Jakarta Selatan\nhello@f-tools.com';
            state.clientName = 'PT Maju Mundur';
            state.clientAddress = 'Jl. Thamrin No. 1\nJakarta Pusat, DKI Jakarta\nfinance@majumundur.com';
            state.bank = 'Bank Central Asia (BCA)';
            state.account = '5210987321';
            state.accountName = 'F-Tools Creative Solutions';
            state.notes = 'Invoice ini valid dan tidak memerlukan tanda tangan basah jika sudah ada logo digital.';
            state.signatoryName = 'Fattah Amin';
            state.signatoryTitle = 'Direktur Utama';
            state.items = [
                { description: 'Konsultasi Strategi Digital', qty: 1, price: 5000000 },
                { description: 'Desain Identitas Visual', qty: 1, price: 12500000 },
                { description: 'Manajer Kampanye Iklan', qty: 2, price: 4500000 }
            ];

            // Update inputs
            inputs.orgName.value = state.orgName;
            inputs.orgAddress.value = state.orgAddress;
            inputs.clientName.value = state.clientName;
            inputs.currency.value = state.currency;
            inputs.clientAddress.value = state.clientAddress;
            inputs.bank.value = state.bank;
            inputs.account.value = state.account;
            inputs.accountName.value = state.accountName;
            inputs.notes.value = state.notes;
            inputs.signatoryName.value = state.signatoryName;
            inputs.signatoryTitle.value = state.signatoryTitle;

            renderEditorItems();
        };

        // Initial setup
        renderEditorItems();
    </script>
</body>
</html>
