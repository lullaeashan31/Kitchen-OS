<!DOCTYPE html>
<html>

<head>
    <title>Production Sheet - {{ $productionDay->date->format('Y-m-d') }}</title>
    <style>
        body {
            font-family: sans-serif;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .section-title {
            font-size: 18px;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 10px;
            background: #eee;
            padding: 5px;
        }

        .checkbox {
            width: 20px;
            height: 20px;
            border: 1px solid #000;
            display: inline-block;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body onload="window.print()">

    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()">Print Sheet</button>
        <button onclick="window.close()">Close</button>
    </div>

    <div class="header">
        <h1>Kitchen Production Sheet</h1>
        <h3>{{ $productionDay->date->format('l, F d, Y') }}</h3>
        @if($productionDay->notes)
            <p><strong>Notes:</strong> {{ $productionDay->notes }}</p>
        @endif
    </div>

    <div class="section-title">Production Items</div>
    <table>
        <thead>
            <tr>
                <th>Recipe</th>
                <th>Portions</th>
                <th>Method Reference</th>
                <th>Check</th>
            </tr>
        </thead>
        <tbody>
            @foreach($productionDay->items as $item)
                <tr>
                    <td><strong>{{ $item->recipe->name }}</strong></td>
                    <td>{{ $item->portions }}</td>
                    <td>{{ Str::limit($item->recipe->method, 100) }}</td>
                    <td>
                        <div class="checkbox"></div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">Preparation Checklist (Total Ingredients)</div>
    <table>
        <thead>
            <tr>
                <th>Ingredient</th>
                <th>Total Quantity</th>
                <th>Unit</th>
                <th>Check</th>
            </tr>
        </thead>
        <tbody>
            @foreach($totalIngredients as $ing)
                <tr>
                    <td>{{ $ing['name'] }}</td>
                    <td>{{ Number::format($ing['total_quantity']) }}</td>
                    <td>{{ $ing['unit'] }}</td>
                    <td>
                        <div class="checkbox"></div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">Tasks</div>
    <ul>
        @foreach($productionDay->tasks as $task)
            <li style="margin-bottom: 10px; display: flex; align-items: center; gap: 10px;">
                <div class="checkbox"></div> {{ $task->title }} @if($task->assignedUser)
                <em>({{ $task->assignedUser->name }})</em> @endif
            </li>
        @endforeach
    </ul>

</body>

</html>