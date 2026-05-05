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
            <div class="meta-item">Yield: {{ $recipe->yield_portions ?? $recipe->yields }} Portions</div>
            @if($recipe->yield_weight_grams)
                <div class="meta-item">Total Weight: {{ number_format($recipe->yield_weight_grams, 0) }}g</div>
            @endif
        </div>
        <div class="text-right">
            @if($recipe->isSubRecipe())
                <div class="meta-item">Storage: {{ $recipe->producesIngredient->storage_location ?? 'Not Assigned' }}</div>
            @endif
            <div class="meta-item">Printed: {{ now()->format('d M Y H:i') }}</div>
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
                                {{ $ri->ingredient->name ?? 'Unknown' }}
                                @if($ri->ingredient && $ri->ingredient->allergen_tags && count($ri->ingredient->allergen_tags) > 0)
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
                            <td style="{{ $group ? 'padding-left: 20px;' : '' }}">{{ $ri->ingredient->name ?? 'Unknown' }}</td>
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

    @php
        $allSubRecipes = collect();
        foreach($recipe->stages as $stage) {
            foreach($stage->ingredients as $ri) {
                if ($ri->ingredient && $ri->ingredient->producedByRecipes && $ri->ingredient->producedByRecipes->count() > 0) {
                    $allSubRecipes->push($ri->ingredient->producedByRecipes->first());
                }
            }
        }
        $allSubRecipes = $allSubRecipes->unique('id');
    @endphp

    @if($allSubRecipes->count() > 0)
        <div style="page-break-before: always;"></div>
        <h3 class="section-title">Sub-Recipe Detailed Instructions</h3>
        <p style="font-size: 0.8em; color: #666; margin-bottom: 20px;">The following details are for sub-components used in this recipe.</p>

        @foreach($allSubRecipes as $sub)
            <div style="margin-bottom: 40px; border: 1px solid #eee; padding: 20px; border-radius: 10px; background: #fff;">
                <h4 style="margin-top: 0; color: #1a56db; border-bottom: 2px solid #e5e7eb; padding-bottom: 10px; font-size: 1.2em;">
                    Sub-Recipe: {{ $sub->name }}
                </h4>
                
                <div style="display: flex; gap: 20px; margin-bottom: 15px; font-size: 0.85em; color: #4b5563;">
                    <span><strong>Target Yield:</strong> {{ $sub->yield_portions ?? $sub->yields }} Portions</span>
                    @if($sub->yield_weight_grams)
                        <span><strong>Total Weight:</strong> {{ number_format($sub->yield_weight_grams, 0) }}g</span>
                    @endif
                </div>

                <table style="font-size: 0.85em; margin-bottom: 20px;">
                    <thead>
                        <tr style="background: #f8fafc;">
                            <th width="60%" style="padding: 10px; border: 1px solid #e5e7eb;">Ingredient</th>
                            <th width="20%" class="text-right" style="padding: 10px; border: 1px solid #e5e7eb;">Qty</th>
                            <th width="20%" style="padding: 10px; border: 1px solid #e5e7eb;">Unit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sub->stages as $sStage)
                            @foreach($sStage->ingredients as $sRi)
                                <tr>
                                    <td style="padding: 8px; border: 1px solid #e5e7eb;">{{ $sRi->ingredient->name ?? 'Unknown' }}</td>
                                    <td class="text-right" style="padding: 8px; border: 1px solid #e5e7eb;">{{ number_format($sRi->quantity, 3) }}</td>
                                    <td style="padding: 8px; border: 1px solid #e5e7eb;">{{ $sRi->unit }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>

                @if($sub->method)
                    <div style="font-size: 0.9em; line-height: 1.6; background: #fdfdfd; border: 1px solid #f1f5f9; padding: 15px; border-radius: 6px;">
                        <strong style="color: #374151; text-transform: uppercase; font-size: 0.8em; letter-spacing: 0.05em;">Preparation Method:</strong><br>
                        <div style="margin-top: 8px; white-space: pre-wrap;">{{ $sub->method }}</div>
                    </div>
                @endif
            </div>
        @endforeach
    @endif

</body>

</html>