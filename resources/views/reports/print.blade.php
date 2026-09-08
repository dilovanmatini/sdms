<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>{{ $report['title'] }}</title>
    <style>
        {!! \App\Support\PrintFont::faces(! empty($forPdf)) !!}

        body {
            font-family: {!! \App\Support\PrintFont::familyStack() !!};
            direction: rtl;
            color: #111827;
            font-size: 11px;
            line-height: 1.55;
            margin: {{ !empty($forPdf) ? '18px' : '28px' }};
            background: #fff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        h1 { font-size: 18px; margin: 0 0 4px; text-align: right; }
        .meta { color: #4b5563; margin-bottom: 16px; text-align: right; }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #d1d5db;
            padding: 5px 6px;
            text-align: right;
        }

        th { background: #f3f4f6; }

        .actions { margin-bottom: 16px; }
        .actions a, .actions button {
            display: inline-block;
            margin-left: 8px;
            padding: 7px 12px;
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
            .actions { display: none; }
            body { margin: 0; }
        }
    </style>
</head>
<body>
    @unless(!empty($forPdf))
        <div class="actions">
            <button type="button" onclick="window.print()">طباعة</button>
            <a href="{{ route('reports.pdf', ['report' => $report['type']] + request()->query()) }}">PDF</a>
            <a href="{{ route('reports.excel', ['report' => $report['type']] + request()->query()) }}">Excel</a>
            <a href="{{ route('reports.show', ['report' => $report['type']] + request()->query()) }}">رجوع</a>
        </div>
    @endunless

    <h1>{{ $report['title'] }}</h1>
    <div class="meta">
        {{ $app_name }} · {{ $generated_at }}
        @if ($report['from_date'] || $report['to_date'])
            · من {{ $report['from_date'] ?: 'البداية' }} إلى {{ $report['to_date'] ?: 'اليوم' }}
        @endif
        · عدد الصفوف: {{ $report['meta']['row_count'] }}
    </div>

    @php
        $columns = ! empty($forPdf)
            ? array_reverse($report['columns'])
            : $report['columns'];
    @endphp

    <table>
        <thead>
            <tr>
                @foreach ($columns as $column)
                    <th>{{ $column['label'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($report['rows'] as $row)
                <tr>
                    @foreach ($columns as $column)
                        <td>{{ $row[$column['key']] ?? '—' }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($columns) }}" style="text-align:center;color:#6b7280;">
                        لا توجد بيانات
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
