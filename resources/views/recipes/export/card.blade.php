<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $recipe->name }} - Recipe Card</title>
    <style>
        body {
            font-family: sans-serif;
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
            line-height: 1.6;
        }

        .header {
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .title {
            font-size: 24px;
            font-weight: bold;
            margin: 0;
        }

        .meta {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }

        .section {
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 18px;
            font-weight: bold;
            border-left: 4px solid #3b82f6;
            padding-left: 10px;
            margin-bottom: 10px;
            background: #f3f4f6;
            padding-top: 5px;
            padding-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th,
        td {
            text-align: left;
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f8f9fa;
        }

        .footer {
            margin-top: 40px;
            font-size: 12px;
            color: #999;
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1 class="title">{{ $recipe->name }}</h1>
        <div class="meta">
            Category: {{ $recipe->category->name ?? 'Uncategorized' }} |
            Yields: {{ $recipe->yields }} Portions |
            Cost/Portion: ₹{{ number_format($recipe->cost_per_portion, 2) }}
        </div>
    </div>

    <div class="section">
        <div class="section-title">Ingredients</div>
        <table>
            <thead>
                <tr>
                    <th>Ingredient</th>
                    <th>Quantity</th>
                    <th>Unit</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recipe->recipeIngredients as $ingredient)
                    <tr>
                        <td>{{ $ingredient->ingredient->name }}</td>
                        <td>{{ number_format($ingredient->quantity, 3) }}</td>
                        <td>{{ $ingredient->unit }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Method</div>
        <div style="white-space: pre-wrap;">{{ $recipe->method }}</div>
    </div>

    <div class="footer">
        Generated on {{ now()->toDateTimeString() }} | Kitchen OS
    </div>
</body>

</html>