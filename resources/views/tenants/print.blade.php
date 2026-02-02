<!DOCTYPE html>
<html>
<head>
    <title>Tenant Invoice</title>
    <style>
        body { font-family: Arial; }
        table { width:100%; border-collapse: collapse; }
        th, td { border:1px solid #000; padding:8px; }
        th { background:#f3f3f3; }
		.tenant-grid {
			display: grid;
			grid-template-columns: 1fr 1fr;
			gap: 12px 40px;
			margin-bottom: 20px;
			font-size: 14px;
		}

		.tenant-row {
			display: flex;
		}

		.tenant-row strong {
			width: 140px;
		}
    </style>
</head>

<body onload="window.print()">

    <center>
        <img src="{{ asset('iiuc.webp') }}" height="60">
        <h2>IIUC Tower</h2>
        <p>1700/A, Plot#9, Agrabad C/A Sk. Mujib Raod <br/>Chittagong, Bangladesh. Phone : 880-31-252 12 00</p>
        <hr>
    </center>

    <h3>Tenant Details</h3>

<div class="tenant-grid">
    <div class="tenant-row">
        <strong>ID:</strong> {{ $tenant->client_id }}
    </div>

    <div class="tenant-row">
        <strong>Name:</strong> {{ $tenant->client_name }}
    </div>

    <div class="tenant-row">
        <strong>Floor:</strong> {{ $tenant->floor }}
    </div>

    <div class="tenant-row">
        <strong>Status:</strong> {{ ucfirst($tenant->status) }}
    </div>

    <div class="tenant-row">
        <strong>Start:</strong> {{ $tenant->rent_start_date->format('Y-m-d') }}
    </div>

    <div class="tenant-row">
        <strong>Expire:</strong> {{ $tenant->expired_date->format('Y-m-d') }}
    </div>

    <div class="tenant-row">
        <strong>Rent Increase:</strong> {{ $tenant->rent_increase }} %
    </div>

    <div class="tenant-row">
        <strong>Base Rent:</strong> {{ number_format($tenant->total_rent, 2) }} BDT
    </div>

    <div class="tenant-row">
        <strong>Current Rent:</strong> {{ number_format($tenant->current_rent, 2) }} BDT
    </div>

    <div class="tenant-row">
        <strong>Advance:</strong> {{ number_format($tenant->rent_advance, 2) }} BDT
    </div>

    <div class="tenant-row">
        <strong>Address:</strong> {{ $tenant->contact_address }}
    </div>
</div>

    <h3>Rent Items</h3>

    <table>
        <tr>
            <th>SFT</th>
            <th>Rate</th>
            <th>Total</th>
        </tr>

        @foreach($tenant->rent_items ?? [] as $item)
            @php
                $sft = (float) ($item['sft'] ?? 0);
                $rate = (float) ($item['rate'] ?? 0);
                $total = $sft * $rate;
            @endphp

            <tr>
                <td>{{ $sft }}</td>
                <td>{{ number_format($rate, 2) }}</td>
                <td>{{ number_format($total, 2) }}</td>
            </tr>
        @endforeach
    </table>

</body>
</html>
