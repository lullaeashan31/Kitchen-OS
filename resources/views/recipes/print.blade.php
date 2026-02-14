<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipe: {{ $recipe->name }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #111;
            line-height: 1.5;
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
        }

        h1 {
            margin-bottom: 5px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            font-size: 0.9em;
        }

        .meta-item {
            font-weight: bold;
        }

        .section-title {
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 30px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 0.95em;
        }

        th,
        td {
            text-align: left;
            padding: 8px 4px;
            border-bottom: 1px solid #eee;
        }

        th {
            font-weight: bold;
            border-bottom: 2px solid #ddd;
        }

        .text-right {
            text-align: right;
        }

        .method {
            white-space: pre-wrap;
            margin-top: 10px;
        }

        .stage-header {
            background: #f9f9f9;
            font-weight: bold;
            padding: 10px 5px;
            margin-top: 15px;
            border-top: 1px solid #ddd;
        }

        @media print {
            body {
                padding: 0;
                max-width: 100%;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body onload="window.print()">

    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.history.back()" style="padding: 10px 20px; cursor: pointer;">&larr; Back</button>
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer; float: right;">Print
            Recipe</button>
    </div>

    <h1>{{ $recipe->name }}</h1>

    <div class="meta">
        <div>
            <div class="meta-item">Category: {{ $recipe->category->name ?? 'N/A' }}</div>
            <div class="meta-item">Yield: {{ $recipe->yields }} Portions</div>
        </div>
        <div class="text-right">
            <div class="meta-item">Prep Time: {{ $recipe->prep_time_minutes ?? '-' }} mins</div>
            @if($recipe->isSubRecipe())
                <div class="meta-item">Output: {{ number_format($recipe->output_quantity, 3) }} {{ $recipe->output_unit }}
                </div>
                <div class="meta-item">Storage: {{ $recipe->producesIngredient->storage_location ?? 'Not Assigned' }}</div>
            @endif
        </div>
    </div>

    @if($recipe->stages->count() > 0)
        <!-- Staged Ingredients -->
        <h3 class="section-title">Ingredients by Set</h3>

        @foreach($recipe->stages as $stage)
            <div class="stage-header">Set {{ $loop->iteration }}: {{ $stage->name }}</div>

            <table>
                <thead>
                    <tr>
                        <th width="40%">Ingredient</th>
                        <th width="20%">Storage Loc.</th>
                        <th width="20%" class="text-right">Quantity</th>
                        <th width="20%">Unit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stage->ingredients as $ri)
                        <tr>
                            <td>
                                {{ $ri->ingredient->name }}
                                @if($ri->ingredient->allergen_tags && count($ri->ingredient->allergen_tags) > 0)
                                    <span style="font-size: 0.8em; color: #d32f2f; font-weight: bold; margin-left: 5px;">
                                        [{{ implode(', ', $ri->ingredient->allergen_tags) }}]
                                    </span>
                                @endif
                            </td>
                            <td>{{ $ri->ingredient->storage_location ?? '-' }}</td>
                            <td class="text-right">{{ number_format($ri->quantity, 3) }}</td>
                            <td>{{ $ri->unit }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if($stage->method)
                <div
                    style="margin-top: 10px; padding: 10px; background: #f0f4f8; border-left: 4px solid #3b82f6; font-size: 0.9em;">
                    <strong>Instructions for {{ $stage->name }}:</strong><br>
                    {{ $stage->method }}
                </div>
            @endif
        @endforeach
    @else
        <!-- Flat List / Grouped -->
        <h3 class="section-title">Ingredients</h3>
        @php
            $grouped = $recipe->recipeIngredients->groupBy('ingredient_group');
        @endphp

        <table>
            <thead>
                <tr>
                    <th width="40%">Ingredient</th>
                    <th width="20%">Storage Loc.</th>
                    <th width="20%" class="text-right">Quantity</th>
                    <th width="20%">Unit</th>
                </tr>
            </thead>
            <tbody>
                @foreach($grouped as $group => $items)
                    @if($group)
                        <tr>
                            <td colspan="4" style="background: #fdfdfd; font-weight: bold; padding-top: 10px;">
                                {{ $group }}
                            </td>
                        </tr>
                    @endif
                    @foreach($items as $ri)
                        <tr>
                            <td style="{{ $group ? 'padding-left: 20px;' : '' }}">{{ $ri->ingredient->name }}</td>
                            <td>{{ $ri->ingredient->storage_location ?? '-' }}</td>
                            <td class="text-right">{{ number_format($ri->quantity, 3) }}</td>
                            <td>{{ $ri->unit }}</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    @endif

    @if($recipe->method)
        <h3 class="section-title">Recipe Overview / Description</h3>
        <div class="method" style="background: #fafafa; padding: 15px; border: 1px solid #eee; border-radius: 5px;">
            {{ $recipe->method }}</div>
    @endif

</body>

</html>