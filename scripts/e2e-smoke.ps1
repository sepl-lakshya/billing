$ErrorActionPreference = 'Stop'
$base = 'http://localhost:4000/api'

# 1. Create cloud project
$body = @{ 'project-name' = 'E2E Test Project'; 'project-city' = 'Mumbai'; 'project-state' = '21'; 'tender-ref-no' = 'TND-001'; 'project-start-date' = '2026-03-01'; 'project-description' = 'smoke test' } | ConvertTo-Json
$r = Invoke-RestMethod -Uri "$base/cloud-projects" -Method Post -Body $body -ContentType 'application/json'
Write-Host "CREATE: status=$($r.status) msg=$($r.msg)"
$projects = (Invoke-RestMethod -Uri "$base/cloud-projects").data
$projId = ($projects | Where-Object { $_.name -eq 'E2E Test Project' })[0].id
Write-Host "PROJECT ID: $projId"

# 2. Import preview (multipart with 2 reference CSVs)
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
$prev = $resp.Content.ReadAsStringAsync().Result | ConvertFrom-Json
Write-Host "PREVIEW: status=$($prev.status) groups=$($prev.data.groups.Count) msg=$($prev.msg)"

# 3. Commit import with bill creation
$commitBody = @{ groups = $prev.data.groups; createBill = $true; month = 3; year = 2026 } | ConvertTo-Json -Depth 10
$c = Invoke-RestMethod -Uri "$base/imports/$projId/commit" -Method Post -Body $commitBody -ContentType 'application/json'
Write-Host "COMMIT: status=$($c.status) msg=$($c.msg)"

# 4. Bill status
$s = Invoke-RestMethod -Uri "$base/bills/project/$projId/status"
$row = $s.data.rows[0]
Write-Host "BILL: month=$($row.month_name) year=$($row.year) portal=$($row.portal_total) status=$($row.status) hasCI=$($null -ne $row.has_ci)"
Write-Host "AVAILABLE MONTHS: $($s.data.availableMonths.Count)"

# 5. Items + headers created
$items = (Invoke-RestMethod -Uri "$base/cloud-projects/$projId/items").data
$headers = (Invoke-RestMethod -Uri "$base/cloud-projects/$projId/headers").data
Write-Host "ITEMS: $($items.Count)  PURCHASE HEADERS: $($headers.Count)"

# 6. Cleanup
$d = Invoke-RestMethod -Uri "$base/cloud-projects/$projId" -Method Delete
Write-Host "CLEANUP: status=$($d.status) msg=$($d.msg)"
