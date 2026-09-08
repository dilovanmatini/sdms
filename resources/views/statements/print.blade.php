<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>كشف حساب — {{ $statement['distributor']['name'] }}</title>
    <style>
        {!! \App\Support\PrintFont::faces(! empty($forPdf)) !!}

        * {
            box-sizing: border-box;
        }

        body {
            font-family: {!! \App\Support\PrintFont::familyStack() !!};
            direction: rtl;
            color: #111827;
            font-size: 12px;
            line-height: 1.6;
            margin: {{ !empty($forPdf) ? '24px' : '32px' }};
            background: #fff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        h1 {
            font-size: 20px;
            margin: 0 0 4px;
            text-align: right;
        }

        .meta {
            color: #4b5563;
            margin-bottom: 20px;
            text-align: right;
        }

        .grid {
            width: 100%;
            margin-bottom: 20px;
        }

        .grid td {
            vertical-align: top;
            padding: 2px 0;
            text-align: right;
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
            text-align: right;
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
        @foreach ([
            ['الموزع', $statement['distributor']['name']],
            ['جهة الاتصال', $statement['distributor']['contact_person'] ?: '—'],
            ['الهاتف', $statement['distributor']['phone'] ?: '—'],
            ['العنوان', $statement['distributor']['address'] ?: '—'],
            ['الفترة', ($statement['from_date'] || $statement['to_date'])
                ? 'من '.($statement['from_date'] ?: 'البداية').' إلى '.($statement['to_date'] ?: 'اليوم')
                : 'كل الفترات'],
        ] as [$label, $value])
            <tr>
                @if (! empty($forPdf))
                    {{-- DomPDF does not reverse table columns for RTL --}}
                    <td>{{ $value }}</td>
                    <td class="label">{{ $label }}</td>
                @else
                    <td class="label">{{ $label }}</td>
                    <td>{{ $value }}</td>
                @endif
            </tr>
        @endforeach
    </table>

    @php
        $entryColumns = [
            ['key' => 'date', 'label' => 'التاريخ'],
            ['key' => 'type', 'label' => 'النوع'],
            ['key' => 'reference', 'label' => 'المرجع'],
            ['key' => 'debit', 'label' => 'مدين', 'num' => true],
            ['key' => 'credit', 'label' => 'دائن', 'num' => true],
            ['key' => 'balance', 'label' => 'الرصيد الجاري', 'num' => true],
        ];

        if (! empty($forPdf)) {
            $entryColumns = array_reverse($entryColumns);
        }
    @endphp

    <table class="entries">
        <thead>
            <tr>
                @foreach ($entryColumns as $column)
                    <th>{{ $column['label'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            <tr>
                @if (! empty($forPdf))
                    <td class="num">{{ $statement['opening_balance'] }}</td>
                    <td class="num">—</td>
                    <td class="num">—</td>
                    <td colspan="3">رصيد افتتاحي</td>
                @else
                    <td colspan="3">رصيد افتتاحي</td>
                    <td class="num">—</td>
                    <td class="num">—</td>
                    <td class="num">{{ $statement['opening_balance'] }}</td>
                @endif
            </tr>
            @forelse ($statement['entries'] as $entry)
                @php
                    $cells = [
                        'date' => $entry['entry_date'],
                        'type' => $entry['type_label'],
                        'reference' => $entry['reference_number'] ?: '—',
                        'debit' => $entry['debit'] !== \App\Support\MoneyDisplay::withSymbol('0.00') ? $entry['debit'] : '—',
                        'credit' => $entry['credit'] !== \App\Support\MoneyDisplay::withSymbol('0.00') ? $entry['credit'] : '—',
                        'balance' => $entry['running_balance'],
                    ];
                @endphp
                <tr>
                    @foreach ($entryColumns as $column)
                        <td @class(['num' => ! empty($column['num'])])>{{ $cells[$column['key']] }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center;color:#6b7280;">لا توجد حركات في هذه الفترة</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="totals">
        @foreach ([
            ['إجمالي المدين', $statement['total_debit']],
            ['إجمالي الدائن', $statement['total_credit']],
            ['الرصيد المتبقي', $statement['closing_balance']],
        ] as [$label, $value])
            <tr>
                @if (! empty($forPdf))
                    <td class="value num">{{ $value }}</td>
                    <td>{{ $label }}</td>
                @else
                    <td>{{ $label }}</td>
                    <td class="value num">{{ $value }}</td>
                @endif
            </tr>
        @endforeach
    </table>
</body>
</html>
