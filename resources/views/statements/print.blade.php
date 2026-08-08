<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>كشف حساب — {{ $statement['distributor']['name'] }}</title>
    <style>
        @font-face {
            font-family: 'NotoSansArabic';
            font-style: normal;
            font-weight: 400;
            src: url('{{ str_replace('\\', '/', resource_path('fonts/NotoSansArabic-Regular.ttf')) }}') format('truetype');
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'NotoSansArabic', DejaVu Sans, sans-serif;
            direction: rtl;
            color: #111827;
            font-size: 12px;
            margin: {{ !empty($forPdf) ? '24px' : '32px' }};
            background: #fff;
        }

        h1 {
            font-size: 20px;
            margin: 0 0 4px;
        }

        .meta {
            color: #4b5563;
            margin-bottom: 20px;
        }

        .grid {
            width: 100%;
            margin-bottom: 20px;
        }

        .grid td {
            vertical-align: top;
            padding: 2px 0;
        }

        .label {
            color: #6b7280;
            width: 110px;
        }

        table.entries {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        table.entries th,
        table.entries td {
            border: 1px solid #d1d5db;
            padding: 6px 8px;
            text-align: right;
        }

        table.entries th {
            background: #f3f4f6;
            font-weight: 600;
        }

        .num {
            text-align: left;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }

        .totals {
            margin-top: 16px;
            width: 100%;
        }

        .totals td {
            padding: 4px 0;
        }

        .totals .value {
            text-align: left;
            font-weight: 600;
        }

        .actions {
            margin-bottom: 20px;
        }

        .actions a,
        .actions button {
            display: inline-block;
            margin-left: 8px;
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

        @media print {
            .actions {
                display: none;
            }

            body {
                margin: 0;
            }
        }
    </style>
</head>
<body>
    @unless(!empty($forPdf))
        <div class="actions">
            <button type="button" onclick="window.print()">طباعة</button>
            <a href="{{ route('statements.pdf', request()->query()) }}">تحميل PDF</a>
            <a href="{{ route('statements.index', request()->query()) }}">رجوع</a>
        </div>
    @endunless

    <h1>كشف حساب العميل</h1>
    <div class="meta">{{ $app_name }} · تاريخ الإصدار: {{ $generated_at }}</div>

    <table class="grid">
        <tr>
            <td class="label">الموزع</td>
            <td>{{ $statement['distributor']['name'] }}</td>
        </tr>
        <tr>
            <td class="label">جهة الاتصال</td>
            <td>{{ $statement['distributor']['contact_person'] ?: '—' }}</td>
        </tr>
        <tr>
            <td class="label">الهاتف</td>
            <td>{{ $statement['distributor']['phone'] ?: '—' }}</td>
        </tr>
        <tr>
            <td class="label">العنوان</td>
            <td>{{ $statement['distributor']['address'] ?: '—' }}</td>
        </tr>
        <tr>
            <td class="label">الفترة</td>
            <td>
                @if ($statement['from_date'] || $statement['to_date'])
                    من {{ $statement['from_date'] ?: 'البداية' }}
                    إلى {{ $statement['to_date'] ?: 'اليوم' }}
                @else
                    كل الفترات
                @endif
            </td>
        </tr>
    </table>

    <table class="entries">
        <thead>
            <tr>
                <th>التاريخ</th>
                <th>النوع</th>
                <th>المرجع</th>
                <th>مدين</th>
                <th>دائن</th>
                <th>الرصيد الجاري</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="3">رصيد افتتاحي</td>
                <td class="num">—</td>
                <td class="num">—</td>
                <td class="num">{{ $statement['opening_balance'] }}</td>
            </tr>
            @forelse ($statement['entries'] as $entry)
                <tr>
                    <td>{{ $entry['entry_date'] }}</td>
                    <td>{{ $entry['type_label'] }}</td>
                    <td>{{ $entry['reference_number'] ?: '—' }}</td>
                    <td class="num">{{ $entry['debit'] !== '0.00' ? $entry['debit'] : '—' }}</td>
                    <td class="num">{{ $entry['credit'] !== '0.00' ? $entry['credit'] : '—' }}</td>
                    <td class="num">{{ $entry['running_balance'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center;color:#6b7280;">لا توجد حركات في هذه الفترة</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td>إجمالي المدين</td>
            <td class="value num">{{ $statement['total_debit'] }}</td>
        </tr>
        <tr>
            <td>إجمالي الدائن</td>
            <td class="value num">{{ $statement['total_credit'] }}</td>
        </tr>
        <tr>
            <td>الرصيد المتبقي</td>
            <td class="value num">{{ $statement['closing_balance'] }}</td>
        </tr>
    </table>
</body>
</html>
