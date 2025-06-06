<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Request #{{ $formRequest->form_id }} - Print View</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@600&display=swap" rel="stylesheet">

    <!-- Styles -->
    <style>
        @media print {
            @page {
                size: landscape;
                margin: 0.5cm;
            }
            body {
                width: 100%;
                margin: 0;
                padding: 10px;
                font-size: 11px;
            }
            .no-print {
                display: none !important;
            }
        }
        body {
            font-family: 'Figtree', sans-serif;
            line-height: 1.4;
            max-width: 800px;
            margin: 0 auto;
            padding: 10px;
            font-size: 11px;
            color: black;
        }
        .slip-container {
            border: 1px solid #000;
            padding: 15px;
        }
        .header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #000;
        }
        .logo {
            max-height: 60px;
            margin-right: 20px;
        }
        .header-content {
            flex: 1;
            text-align: center;
        }
        .header-right {
            width: 60px;
        }
        .header h1 {
            font-size: 14px;
            margin: 0 0 5px 0;
            font-weight: bold;
            color: black;
        }
        .header p {
            font-size: 10px;
            margin: 0;
            color: black;
        }
        .details-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-bottom: 15px;
            font-size: 11px;
        }
        .field {
            display: flex;
            gap: 5px;
        }
        .field-label {
            font-weight: 600;
            min-width: 100px;
            color: black;
        }
        .field-value {
            flex: 1;
            color: black;
        }
        .approval-section {
            margin-top: 15px;
            border-top: 1px solid #000;
            padding-top: 10px;
        }
        .approval-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }
        .approval-box {
            text-align: center;
            padding: 5px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .signature-container {
            min-height: 30px;
            margin-bottom: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .signature-text {
            font-family: 'Dancing Script', cursive;
            font-size: 14px;
            color: black;
            text-transform: uppercase;
        }
        .signature-image {
            height: 30px;
            object-fit: contain;
        }
        .approval-name {
            font-weight: 600;
            font-size: 11px;
            margin-bottom: 2px;
            color: black;
        }
        .approval-info {
            font-size: 10px;
            color: black;
            line-height: 1.2;
        }
        .approval-status {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 10px;
            font-size: 10px;
            margin-top: 3px;
            border: 1px solid black;
            color: black;
        }
    </style>
</head>
<body>
    <!-- Print Button -->
    <div class="no-print">
        <button onclick="window.print()" style="position: fixed; top: 20px; right: 20px; padding: 8px 16px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer;">
            Print Request
        </button>
    </div>

    <div class="slip-container">
        <!-- Header -->
        <div class="header">
            <img src="{{ asset('images/lyceum-logo.png') }}" alt="Lyceum Logo" class="logo">
            <div class="header-content">
                <h1>{{ $formRequest->form_type === 'IOM' ? 'INTER-OFFICE MEMORANDUM' : 'LEAVE REQUEST FORM' }} #{{ $formRequest->form_id }}</h1>
                <p>Generated on {{ now()->format('F j, Y g:i A') }}</p>
            </div>
            <div class="header-right"></div>
        </div>

        <!-- Request Details -->
        <div class="details-grid">
            @if($formRequest->form_type === 'IOM')
            <div class="field">
                <div class="field-label">From:</div>
                <div class="field-value">{{ $formRequest->fromDepartment->dept_name }}</div>
            </div>
            <div class="field">
                <div class="field-label">To:</div>
                <div class="field-value">{{ $formRequest->toDepartment->dept_name }}</div>
            </div>
            <div class="field">
                <div class="field-label">Date:</div>
                <div class="field-value">{{ $formRequest->date_submitted->format('F j, Y') }}</div>
            </div>
            <div class="field">
                <div class="field-label">Date Needed:</div>
                <div class="field-value">{{ $formRequest->iomDetails->date_needed ? \Carbon\Carbon::parse($formRequest->iomDetails->date_needed)->format('F j, Y') : 'N/A' }}</div>
            </div>
            <div class="field">
                <div class="field-label">Priority:</div>
                <div class="field-value">{{ $formRequest->iomDetails->priority ?? 'N/A' }}</div>
            </div>
            <div class="field">
                <div class="field-label">Employee No:</div>
                <div class="field-value">{{ $formRequest->requester->username }}</div>
            </div>
            <div class="field" style="grid-column: span 2;">
                <div class="field-label">Re:</div>
                <div class="field-value">{{ $formRequest->title }}</div>
            </div>
            <div class="field" style="grid-column: span 2;">
                <div class="field-label">Purpose:</div>
                <div class="field-value">{{ $formRequest->iomDetails->purpose ?? 'N/A' }}</div>
            </div>
            @if($formRequest->iomDetails && $formRequest->iomDetails->body)
            <div class="field" style="grid-column: span 2;">
                <div class="field-label">Description:</div>
                <div class="field-value">{{ $formRequest->iomDetails->body }}</div>
            </div>
            @endif
            @else
            <!-- Leave Request Details -->
            <div class="field">
                <div class="field-label">Employee:</div>
                <div class="field-value">{{ $formRequest->requester->employeeInfo->FirstName }} {{ $formRequest->requester->employeeInfo->LastName }}</div>
            </div>
            <div class="field">
                <div class="field-label">Employee No:</div>
                <div class="field-value">{{ $formRequest->requester->username }}</div>
            </div>
            <div class="field">
                <div class="field-label">Department:</div>
                <div class="field-value">{{ $formRequest->fromDepartment->dept_name }}</div>
            </div>
            <div class="field">
                <div class="field-label">Date Filed:</div>
                <div class="field-value">{{ $formRequest->date_submitted->format('F j, Y') }}</div>
            </div>
            <div class="field">
                <div class="field-label">Leave Type:</div>
                <div class="field-value">{{ ucfirst($formRequest->leaveDetails->leave_type) }} Leave</div>
            </div>
            <div class="field">
                <div class="field-label">Duration:</div>
                <div class="field-value">{{ $formRequest->leaveDetails->days }} day(s)</div>
            </div>
            <div class="field">
                <div class="field-label">Start Date:</div>
                <div class="field-value">{{ $formRequest->leaveDetails->start_date->format('F j, Y') }}</div>
            </div>
            <div class="field">
                <div class="field-label">End Date:</div>
                <div class="field-value">{{ $formRequest->leaveDetails->end_date->format('F j, Y') }}</div>
            </div>
            <div class="field" style="grid-column: span 2;">
                <div class="field-label">Reason:</div>
                <div class="field-value">{{ $formRequest->leaveDetails->description }}</div>
            </div>
            @endif
        </div>

        <!-- Approval Section -->
        <div class="approval-section">
            <div class="approval-grid">
                @foreach($formRequest->approvals->sortBy('action_date') as $approval)
                    @if($approval->action !== 'Submitted')
                        <div class="approval-box">
                            <div class="signature-container">
                                @if($approval->signature_data)
                                    <img src="{{ $approval->signature_data }}" alt="Digital Signature" class="signature-image">
                                @else
                                    <div class="signature-text">
                                        {{ $approval->approver->employeeInfo->FirstName }} {{ $approval->approver->employeeInfo->LastName }}
                                    </div>
                                @endif
                            </div>
                            <div class="approval-name">
                                {{ $approval->approver->employeeInfo->FirstName }} {{ $approval->approver->employeeInfo->LastName }}
                            </div>
                            <div class="approval-info">
                                {{ $approval->approver->position }}<br>
                                {{ $approval->approver->department->dept_name }}
                            </div>
                            <div class="approval-status">
                                {{ $approval->action }}
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html> 