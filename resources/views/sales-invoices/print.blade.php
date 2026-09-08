<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>فاتورة — {{ $invoice['number'] }}</title>
    <style>
        {!! \App\Support\PrintFont::faces() !!}

        * {
            box-sizing: border-box;
        }

        body {
            font-family: {!! \App\Support\PrintFont::familyStack() !!};
            direction: rtl;
            color: #111827;
            font-size: 12px;
            line-height: 1.55;
            margin: 0;
            padding: 28px 36px;
            background: #fff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .actions {
            margin-bottom: 20px;
        }

        .actions a,
        .actions button {
            display: inline-block;
            margin-inline-start: 8px;
            padding: 8px 14px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: #fff;
            color: #111827;
            text-decoration: none;
            cursor: pointer;
            font-family: inherit;
            font-size: 12px;
        }

        .top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 24px;
            margin-bottom: 28px;
        }

        .company {
            flex: 1;
            min-width: 0;
        }

        .company-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 10px;
        }

        .company-brand img {
            max-height: 72px;
            max-width: 180px;
            object-fit: contain;
        }

        .company-name {
            font-size: 20px;
            font-weight: 700;
            margin: 0;
        }

        .company-details {
            color: #374151;
            white-space: pre-wrap;
            line-height: 1.6;
        }

        .invoice-meta {
            text-align: end;
            flex-shrink: 0;
            min-width: 180px;
        }

        .invoice-title {
            margin: 0 0 6px;
            font-size: 32px;
            font-weight: 700;
            letter-spacing: 0.02em;
            line-height: 1.1;
        }

        .invoice-number {
            margin: 0 0 16px;
            font-size: 13px;
            color: #111827;
        }

        .balance-label {
            margin: 0;
            font-size: 12px;
            color: #4b5563;
        }

        .balance-amount {
            margin: 2px 0 0;
            font-size: 22px;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            line-height: 1.2;
        }

        .parties {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 32px;
            margin-bottom: 28px;
        }

        .bill-to {
            flex: 1;
            min-width: 0;
        }

        .section-label {
            margin: 0 0 6px;
            font-weight: 700;
        }

        .bill-to-name {
            margin: 0 0 2px;
            font-weight: 600;
        }

        .bill-to-line {
            margin: 0;
            color: #374151;
            white-space: pre-wrap;
        }

        .dates {
            flex-shrink: 0;
            border-collapse: collapse;
        }

        .dates th,
        .dates td {
            padding: 2px 0;
            vertical-align: top;
            font-weight: 400;
        }

        .dates th {
            padding-inline-end: 12px;
            text-align: end;
            color: #4b5563;
            white-space: nowrap;
        }

        .dates td {
            text-align: start;
            font-variant-numeric: tabular-nums;
        }

        table.lines {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        table.lines th {
            background: #7eb6ef;
            color: #fff;
            font-weight: 600;
            padding: 8px 10px;
            text-align: start;
            border: none;
        }

        table.lines th.col-num,
        table.lines th.col-qty,
        table.lines th.col-price,
        table.lines th.col-amount {
            text-align: end;
        }

        table.lines th.col-index {
            width: 36px;
            text-align: center;
        }

        table.lines td {
            padding: 9px 10px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
            text-align: start;
        }

        table.lines tbody tr:nth-child(even) td {
            background: #f9fafb;
        }

        table.lines td.col-index {
            text-align: center;
            color: #6b7280;
        }

        table.lines td.col-num {
            text-align: end;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }

        .line-name {
            font-weight: 500;
        }

        .line-code {
            color: #6b7280;
            font-size: 11px;
            margin-top: 2px;
        }

        .summary-wrap {
            display: flex;
            justify-content: flex-end;
            margin-top: 4px;
            margin-bottom: 28px;
        }

        .summary {
            width: 260px;
            border-collapse: collapse;
        }

        .summary td {
            padding: 6px 0;
        }

        .summary .label {
            text-align: start;
            color: #374151;
            padding-inline-end: 16px;
        }

        .summary .value {
            text-align: end;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }

        .summary .total-row td {
            background: #e5e7eb;
            font-weight: 700;
            padding: 8px 10px;
        }

        .notes {
            margin-bottom: 20px;
            white-space: pre-wrap;
        }

        .notes strong {
            display: block;
            margin-bottom: 4px;
        }

        .footer {
            margin-top: 24px;
            padding-top: 14px;
            border-top: 1px solid #d1d5db;
            color: #374151;
            white-space: pre-wrap;
            line-height: 1.7;
        }

        @media print {
            .actions {
                display: none;
            }

            body {
                padding: 16px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="actions">
        <button type="button" onclick="window.print()">طباعة</button>
        <a href="{{ $back_url }}">رجوع</a>
    </div>

    <div class="top">
        <div class="company">
            <div class="company-brand">
                <img src="{{ $logo_url }}" alt="{{ $app_name }}">
                <p class="company-name">{{ $app_name }}</p>
            </div>
            @if ($invoice_header)
                <div class="company-details">{{ $invoice_header }}</div>
            @endif
        </div>

        <div class="invoice-meta">
            <h1 class="invoice-title">فاتورة</h1>
            <p class="invoice-number">رقم الفاتورة # {{ $invoice['number'] }}</p>
            <p class="balance-label">المبلغ المستحق</p>
            <p class="balance-amount">{{ $invoice['remaining_amount'] }}</p>
        </div>
    </div>

    <div class="parties">
        <div class="bill-to">
            <p class="section-label">إلى</p>
            <p class="bill-to-name">{{ $invoice['distributor']['name'] }}</p>
            @if ($invoice['distributor']['contact_person'])
                <p class="bill-to-line">{{ $invoice['distributor']['contact_person'] }}</p>
            @endif
            @if ($invoice['distributor']['phone'])
                <p class="bill-to-line">{{ $invoice['distributor']['phone'] }}</p>
            @endif
            @if ($invoice['distributor']['address'])
                <p class="bill-to-line">{{ $invoice['distributor']['address'] }}</p>
            @endif
        </div>

        <table class="dates">
            <tr>
                <th>تاريخ الفاتورة :</th>
                <td>{{ $invoice['invoice_date'] ?: '—' }}</td>
            </tr>
            <tr>
                <th>الحالة :</th>
                <td>{{ $invoice['status_label'] }}</td>
            </tr>
        </table>
    </div>

    <table class="lines">
        <thead>
            <tr>
                <th class="col-index">#</th>
                <th>البيان</th>
                <th class="col-qty">الكمية</th>
                <th class="col-price">السعر</th>
                <th class="col-amount">المبلغ</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice['lines'] as $index => $line)
                <tr>
                    <td class="col-index">{{ $index + 1 }}</td>
                    <td>
                        <div class="line-name">{{ $line['product_name'] }}</div>
                        <div class="line-code">{{ $line['product_code'] }}</div>
                    </td>
                    <td class="col-num">{{ $line['quantity'] }}</td>
                    <td class="col-num">{{ $line['unit_price'] }}</td>
                    <td class="col-num">{{ $line['line_total'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary-wrap">
        <table class="summary">
            <tr>
                <td class="label">المجموع الفرعي</td>
                <td class="value">{{ $invoice['subtotal'] }}</td>
            </tr>
            @if ($invoice['discount'] !== '0')
                <tr>
                    <td class="label">الخصم</td>
                    <td class="value">{{ $invoice['discount'] }}</td>
                </tr>
            @endif
            <tr class="total-row">
                <td class="label">الإجمالي</td>
                <td class="value">{{ $invoice['grand_total'] }}</td>
            </tr>
        </table>
    </div>

    @if ($invoice['notes'])
        <div class="notes">
            <strong>ملاحظات</strong>
            <div>{{ $invoice['notes'] }}</div>
        </div>
    @endif

    @if ($invoice_footer)
        <div class="footer">{{ $invoice_footer }}</div>
    @endif

    <script>
        window.addEventListener('load', () => {
            const triggerPrint = () => window.print();

            if (document.fonts?.ready) {
                document.fonts.ready.then(triggerPrint).catch(triggerPrint);
            } else {
                triggerPrint();
            }
        });
    </script>
</body>
</html>
