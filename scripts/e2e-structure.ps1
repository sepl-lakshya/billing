$ErrorActionPreference = 'Stop'
$base = 'http://localhost:4000/api'

# 1. Create cloud project
$body = @{ 'project-name' = 'Structure Test'; 'project-city' = 'Mumbai'; 'project-state' = '21'; 'tender-ref-no' = 'TND-002'; 'project-start-date' = '2026-03-01'; 'project-description' = 'structure verification' } | ConvertTo-Json
$r = Invoke-RestMethod -Uri "$base/cloud-projects" -Method Post -Body $body -ContentType 'application/json'
$projId = $r.data.id
Write-Host "PROJECT: id=$projId"

# 2. Preview with default (resource) grouping
$csv1 = 'C:\Users\Admin\OneDrive - Surbhi Electronet Private Limited (SEPL)\Desktop\Billing\reference\samples\part_0_0001.csv'
$csv2 = 'C:\Users\Admin\OneDrive - Surbhi Electronet Private Limited (SEPL)\Desktop\Billing\reference\samples\part_1_0001.csv'
Add-Type -AssemblyName System.Net.Http
$client = New-Object System.Net.Http.HttpClient
$content = New-Object System.Net.Http.MultipartFormDataContent
foreach ($f in @($csv1, $csv2)) {
  $fs = [System.IO.File]::OpenRead($f)
  $fc = New-Object System.Net.Http.StreamContent($fs)
  $fc.Headers.ContentType = 'text/csv'
  $content.Add($fc, 'files', [System.IO.Path]::GetFileName($f))
}
$resp = $client.PostAsync("$base/imports/$projId/preview", $content).Result
$prev = ($resp.Content.ReadAsStringAsync().Result | ConvertFrom-Json)
Write-Host "PREVIEW: status=$($prev.status) groups(bars)=$($prev.data.groups.Count)"
Write-Host "--- First 8 bar names ---"
$prev.data.groups | Select-Object -First 8 | ForEach-Object { Write-Host ("  {0}  ({1:N2})" -f $_.header, $_.totalCost) }

# 3. Commit with bill
$commitBody = @{ groups = $prev.data.groups; createBill = $true; month = 3; year = 2026 } | ConvertTo-Json -Depth 10
$c = Invoke-RestMethod -Uri "$base/imports/$projId/commit" -Method Post -Body $commitBody -ContentType 'application/json'
Write-Host "COMMIT: $($c.msg)"

# 4. Verify purchase headers = 2 (RI + PAYG)
$hdrs = (Invoke-RestMethod -Uri "$base/purchase-headers?projectId=$projId").data
Write-Host "--- Purchase headers ($($hdrs.Count)) ---"
$hdrs | ForEach-Object { Write-Host "  $($_.name)" }

# 5. Verify project headers (bars) + items
$bars = (Invoke-RestMethod -Uri "$base/cloud-projects/$projId/headers").data
$items = (Invoke-RestMethod -Uri "$base/cloud-projects/$projId/items").data
Write-Host "BARS: $($bars.Count)  ITEMS: $($items.Count)"

# 6. Bill status sanity (sales_total from bill_header = 0 initially)
$s = Invoke-RestMethod -Uri "$base/bills/project/$projId/status"
$row = $s.data.rows[0]
Write-Host "BILL: portal=$($row.portal_total) sales=$($row.sales_total) status=$($row.status)"

# 7. Cleanup
$d = Invoke-RestMethod -Uri "$base/cloud-projects/$projId" -Method Delete
Write-Host "CLEANUP: $($d.msg)"
