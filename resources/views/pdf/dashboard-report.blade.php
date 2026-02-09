<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; color: #334155; margin: 0; padding: 20px; }
        .header { border-bottom: 2px solid #6366f1; padding-bottom: 10px; margin-bottom: 20px; }
        .title { font-size: 24px; font-weight: bold; color: #1e293b; }
        .meta { font-size: 10px; color: #64748b; text-transform: uppercase; margin-top: 5px; }
        
        .filter-info { background: #f8fafc; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 12px; }
        
        .stats-container { margin-bottom: 30px; }
        .stat-card { display: inline-block; width: 30%; border: 1px solid #e2e8f0; padding: 15px; border-radius: 10px; margin-right: 2%; }
        .stat-label { font-size: 9px; font-weight: bold; color: #64748b; text-transform: uppercase; }
        .stat-value { font-size: 20px; font-weight: bold; color: #6366f1; }

        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background-color: #f1f5f9; color: #475569; font-size: 10px; text-transform: uppercase; padding: 10px; text-align: left; }
        td { border-bottom: 1px solid #e2e8f0; padding: 10px; font-size: 11px; }
        .ref { font-family: monospace; color: #6366f1; font-weight: bold; }
        .badge { padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: bold; }
        .badge-analyzed { background: #dcfce7; color: #15803d; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .chart-grid { width: 100%; margin-top: 20px; }
    .chart-box { width: 48%; display: inline-block; vertical-align: top; margin-bottom: 20px; border: 1px solid #f1f5f9; padding: 10px; border-radius: 8px; }
    .chart-title { font-size: 9px; font-weight: bold; text-transform: uppercase; color: #64748b; margin-bottom: 8px; text-align: center; }
    .full-width { width: 100%; display: block; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Incident Information Report</div>
        <div class="meta">Generated on: {{ $date }} | Intelligence Unit</div>
    </div>

    <div class="filter-info">
        <strong>Active Filters:</strong> 
        Date Range: {{ $filters['start_date'] ?? 'All Time' }} to {{ $filters['end_date'] ?? 'Now' }} | 
        Classification: {{ $filters['classification'] ?? 'All Classifications' }}
    </div>

    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-label">Total Filtered Incidents</div>
            <div class="stat-value">{{ $total }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Reference</th>
                <th>Barangay</th>
                <th>Classification</th>
                <th>Subject</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($incidents as $item)
            <tr>
                <td class="ref">#{{ $item->reference }}</td>
                <td>{{ $item->barangay->barangay ?? 'N/A' }}</td>
                <td>{{ $item->classification->name ?? 'N/A' }}</td>
                <td>{{ Str::limit($item->subject, 40) }}</td>
                <td>
                    <span class="badge {{ $item->analysis ? 'badge-analyzed' : 'badge-pending' }}">
                        {{ $item->analysis ? 'ANALYZED' : 'PENDING' }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>