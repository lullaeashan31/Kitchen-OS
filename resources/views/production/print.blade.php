<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Production Sheet - {{ $productionDay->date->format('Y-m-d') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:wght@400;800&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy: #06101E;
            --brass: #B5975A;
            --ivory: #F2EDE6;
        }
        
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            color: var(--navy);
            background: white;
            margin: 0;
            padding: 40px;
            line-height: 1.5;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid var(--navy);
            padding-bottom: 20px;
            margin-bottom: 40px;
        }

        h1 {
            font-family: 'EB Garamond', serif;
            font-size: 32px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 0 0 10px 0;
        }

        h2 {
            font-family: 'EB Garamond', serif;
            font-size: 18px;
            font-weight: 400;
            text-transform: uppercase;
            letter-spacing: 4px;
            color: var(--brass);
            margin: 0;
        }

        .section-title {
            font-family: 'EB Garamond', serif;
            font-size: 20px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 1px solid #eee;
            padding-bottom: 8px;
            margin: 30px 0 15px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #eee;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        th {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #666;
            text-align: left;
            padding: 12px 8px;
            border-bottom: 2px solid var(--navy);
        }

        td {
            font-size: 13px;
            padding: 12px 8px;
            border-bottom: 1px solid #eee;
        }

        .font-bold { font-weight: 700; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .checkbox {
            width: 18px;
            height: 18px;
            border: 1px solid var(--navy);
            display: inline-block;
            vertical-align: middle;
        }

        .notes-box {
            font-family: 'EB Garamond', serif;
            font-style: italic;
            font-size: 16px;
            background: #f9f9f9;
            padding: 20px;
            border-left: 4px solid var(--brass);
            margin-bottom: 30px;
        }

        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }

        .controls {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
        }

        .btn {
            background: var(--navy);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            cursor: pointer;
        }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print controls">
        <button class="btn" onclick="window.print()">Print</button>
        <button class="btn" style="background:#666" onclick="window.close()">Close</button>
    </div>

    <div class="header">
        <h1>Kitchen Production</h1>
        <h2>{{ $productionDay->date->format('l, F d, Y') }}</h2>
    </div>

    @if($productionDay->notes)
        <div class="notes-box">
            "{{ $productionDay->notes }}"
        </div>
    @endif

    <div class="section-title">Production Items</div>
    <table>
        <thead>
            <tr>
                <th style="width: 30%;">Recipe</th>
                <th class="text-center" style="width: 15%;">Portions</th>
                <th>Method Reference</th>
                <th class="text-right" style="width: 50px;">Check</th>
            </tr>
        </thead>
        <tbody>
            @foreach($productionDay->items as $item)
                <tr>
                    <td class="font-bold">{{ $item->recipe->name }}</td>
                    <td class="text-center">{{ $item->portions }}</td>
                    <td style="color: #666; font-size: 11px;">{{ Str::limit($item->recipe->method, 150) }}</td>
                    <td class="text-right"><div class="checkbox"></div></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">Consolidated Ingredients</div>
    <table>
        <thead>
            <tr>
                <th style="width: 50%;">Ingredient</th>
                <th class="text-center" style="width: 25%;">Total Quantity</th>
                <th class="text-center" style="width: 15%;">Unit</th>
                <th class="text-right" style="width: 50px;">Check</th>
            </tr>
        </thead>
        <tbody>
            @foreach($totalIngredients as $ing)
                <tr>
                    <td class="font-bold">{{ $ing['name'] }}</td>
                    <td class="text-center font-bold">{{ number_format($ing['total_quantity'], 2) }}</td>
                    <td class="text-center uppercase" style="font-size: 11px;">{{ $ing['unit'] }}</td>
                    <td class="text-right"><div class="checkbox"></div></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">Tasks Checklist</div>
    <div style="columns: 2; gap: 40px;">
        @foreach($productionDay->tasks as $task)
            <div style="margin-bottom: 15px; break-inside: avoid; display: flex; align-items: flex-start; gap: 12px; font-size: 13px;">
                <div class="checkbox" style="margin-top: 2px;"></div>
                <div>
                    <div class="font-bold">{{ $task->title }}</div>
                    @if($task->assignedUser)
                        <div style="font-size: 10px; text-transform: uppercase; color: #888;">{{ $task->assignedUser->name }}</div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

</body>
</html>