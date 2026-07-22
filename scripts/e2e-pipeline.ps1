$ErrorActionPreference = 'Stop'
$base = 'http://localhost:4000/api'

# Setup: project + import + bill
$body = @{ 'project-name' = 'Pipeline Test'; 'project-city' = 'Mumbai'; 'project-state' = '21'; 'tender-ref-no' = 'TND-003'; 'project-start-date' = '2026-03-01'; 'project-description' = 'x' } | ConvertTo-Json
$projId = (Invoke-RestMethod -Uri "$base/cloud-projects" -Method Post -Body $body -ContentType 'application/json').data.id
Add-Type -AssemblyName System.Net.Http
$client = New-Object System.Net.Http.HttpClient
$content = New-Object System.Net.Http.MultipartFormDataContent
foreach ($f in @('C:\Users\Admin\OneDrive - Surbhi Electronet Private Limited (SEPL)\Desktop\Billing\reference\samples\part_0_0001.csv','C:\Users\Admin\OneDrive - Surbhi Electronet Private Limited (SEPL)\Desktop\Billing\reference\samples\part_1_0001.csv')) {
  $fs=[System.IO.File]::OpenRead($f); $fc=New-Object System.Net.Http.StreamContent($fs); $fc.Headers.ContentType='text/csv'; $content.Add($fc,'files',[System.IO.Path]::GetFileName($f))
}
$prev = ($client.PostAsync("$base/imports/$projId/preview", $content).Result.Content.ReadAsStringAsync().Result | ConvertFrom-Json)
$c = Invoke-RestMethod -Uri "$base/imports/$projId/commit" -Method Post -Body (@{ groups=$prev.data.groups; createBill=$true; month=3; year=2026 } | ConvertTo-Json -Depth 10) -ContentType 'application/json'
$billId = $c.data.billId
Write-Host "SETUP OK: proj=$projId bill=$billId"

# Add a discount (RI 6%, PAYG 19% like old portal)
$disc = Invoke-RestMethod -Uri "$base/cloud-projects/$projId/discounts" -Method Post -Body (@{ 'discount-ri'='6'; 'discount-payg'='19'; 'credit-days'='30' } | ConvertTo-Json) -ContentType 'application/json'
Write-Host "DISCOUNT: $($disc.msg)"

# Pricing data: headers for sales
$pr = (Invoke-RestMethod -Uri "$base/bills/$billId/pricing").data
Write-Host "PRICING: rows=$($pr.rows.Count) headers=$($pr.headers.Count) purchaseHeaders=$($pr.purchaseHeaders.Count)"

# Sales price: set each header amount = its items' portal sum + 10%
$hIds = @(); $hVals = @()
foreach ($h in $pr.headers) {
  $sum = ($pr.rows | Where-Object { $_.header_id -eq $h.id } | Measure-Object -Property portal_price -Sum).Sum
  $hIds += $h.id; $hVals += [math]::Round([double]$sum * 1.1, 2)
}
$sp = Invoke-RestMethod -Uri "$base/bills/sales-price" -Method Post -Body (@{ projectId=$projId; billId=$billId; projectItemIds=@(); salesPrices=@(); projectHeaderIds=$hIds; headerPrices=$hVals } | ConvertTo-Json) -ContentType 'application/json'
Write-Host "SALES: $($sp.msg)"

# Purchase price: per purchase header, actual = calculated
$pr2 = (Invoke-RestMethod -Uri "$base/bills/$billId/pricing").data
$phIds = @(); $phVals = @(); $itemIds = @(); $itemVals = @()
foreach ($p in $pr2.purchaseHeaders) { $phIds += $p.purchase_header_id; $phVals += $p.calculated_amount }
foreach ($r in $pr2.rows) {
  $pct = if ($r.model -eq 'RI') { 6 } else { 19 }
  $pp0 = if ($null -eq $r.portal_price) { 0 } else { [double]$r.portal_price }
  $itemIds += $r.id; $itemVals += [math]::Round($pp0 * (1 - $pct/100), 2)
}
$pp = Invoke-RestMethod -Uri "$base/bills/purchase-price" -Method Post -Body (@{ projectId=$projId; billId=$billId; projectItemIds=$itemIds; purchasePrices=$itemVals; purchaseHeaderIds=$phIds; headerPrices=$phVals } | ConvertTo-Json) -ContentType 'application/json'
Write-Host "PURCHASE: $($pp.msg)"

# Final status
$row = (Invoke-RestMethod -Uri "$base/bills/project/$projId/status").data.rows[0]
Write-Host ("FINAL: portal={0} sales={1} purchase={2} status={3} ri={4}% payg={5}% deviation={6}" -f $row.portal_total,$row.sales_total,$row.purchase_total,$row.status,$row.ri_discount,$row.payg_discount,$row.deviation)

# Cleanup
$d = Invoke-RestMethod -Uri "$base/cloud-projects/$projId" -Method Delete
Write-Host "CLEANUP: $($d.msg)"
