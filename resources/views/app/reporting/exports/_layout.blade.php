<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a202c; }
.header { border-bottom: 2px solid #0d9488; padding-bottom: 8px; margin-bottom: 14px; display: flex; justify-content: space-between; align-items: flex-end; }
.header h1 { font-size: 16px; font-weight: 700; color: #0d9488; }
.header .meta { font-size: 9px; color: #718096; text-align: right; }
.stat-grid { display: flex; gap: 10px; margin-bottom: 14px; }
.stat-card { flex: 1; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 10px; border-top: 3px solid #0d9488; }
.stat-card .label { font-size: 9px; color: #718096; text-transform: uppercase; letter-spacing: .05em; }
.stat-card .value { font-size: 14px; font-weight: 700; color: #0d9488; margin-top: 2px; }
table { width: 100%; border-collapse: collapse; font-size: 10px; }
thead tr { background: #0d9488; color: #fff; }
thead th { padding: 6px 8px; text-align: left; }
thead th.num { text-align: right; }
tbody tr:nth-child(even) { background: #f7fafc; }
tbody td { padding: 5px 8px; border-bottom: 1px solid #e2e8f0; }
tbody td.num { text-align: right; }
.badge { display: inline-block; padding: 1px 6px; border-radius: 999px; font-size: 9px; font-weight: 600; }
.badge-teal { background: #ccfbf1; color: #0f766e; }
.badge-rose { background: #ffe4e6; color: #be123c; }
.badge-amber { background: #fef3c7; color: #b45309; }
.badge-blue { background: #dbeafe; color: #1d4ed8; }
.section-title { font-size: 12px; font-weight: 600; margin: 12px 0 6px; color: #374151; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; }
.footer { margin-top: 14px; font-size: 9px; color: #a0aec0; text-align: center; border-top: 1px solid #e5e7eb; padding-top: 6px; }
</style>
</head>
<body>
<div class="header">
    <div>
        <h1>@yield('title')</h1>
        <p style="font-size:10px;color:#4a5568;margin-top:2px">@yield('subtitle', '')</p>
    </div>
    <div class="meta">
        <p>Generated: {{ now()->format('d M Y H:i') }}</p>
    </div>
</div>

@yield('content')

<div class="footer">This report was generated automatically by the Inventory Management System.</div>
</body>
</html>
