@extends('layouts.app')

@section('title', 'Manage Purchase Units')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0 text-gray-800">Purchase Units</h1>
            <p class="text-muted">Define custom units for purchases and their conversion factors to usage units (g, ml, etc).</p>
        </div>
        <div class="col-md-6 text-right">
            <a href="{{ route('admin.purchase-units.create', ['kitchen_slug' => $kitchen->slug]) }}" class="btn btn-primary">
                <i class="fas fa-plus mr-2"></i>Add New Unit
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-left-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Unit Conversion List</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Unit Name</th>
                            <th>Base Unit</th>
                            <th>Conversion Factor</th>
                            <th>Example</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($units as $unit)
                            <tr>
                                <td class="font-weight-bold">{{ $unit->name }}</td>
                                <td><span class="badge badge-info">{{ $unit->base_unit }}</span></td>
                                <td>{{ number_format($unit->conversion_factor, 3) }}</td>
                                <td>
                                    1 {{ $unit->name }} = {{ number_format($unit->conversion_factor, 2) }} {{ $unit->base_unit }}
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('admin.purchase-units.edit', ['kitchen_slug' => $kitchen->slug, 'purchase_unit' => $unit->id]) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.purchase-units.destroy', ['kitchen_slug' => $kitchen->slug, 'purchase_unit' => $unit->id]) }}" 
                                              method="POST" 
                                              onsubmit="return confirm('Are you sure you want to delete this unit?');"
                                              style="display: inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    No custom units defined yet. Click "Add New Unit" to get started.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Help Card -->
    <div class="card border-left-info shadow h-100 py-2">
        <div class="card-body">
            <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">How Conversion Factors Work</div>
                    <div class="p mb-0 font-weight-normal text-gray-800">
                        The <strong>Conversion Factor</strong> defines how many base units (usage units) are in one purchase unit.<br>
                        <em>Example:</em> If you buy a <strong>Bag of Flour</strong> which is <strong>25kg</strong>, and your usage unit is <strong>g</strong> (grams):<br>
                        Factor = 25 * 1000 = <strong>25000</strong>.
                    </div>
                </div>
                <div class="col-auto">
                    <i class="fas fa-info-circle fa-2x text-gray-300"></i>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
