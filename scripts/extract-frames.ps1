param(
  [string]$Video = "C:\Users\Admin\OneDrive - Surbhi Electronet Private Limited (SEPL)\Desktop\Billing\reference\samples\Old Billing Portal.mp4",
  [string]$OutDir = "$env:TEMP\video-frames",
  [int]$Count = 24
)
Add-Type -AssemblyName PresentationCore

New-Item -ItemType Directory -Force -Path $OutDir | Out-Null

$player = New-Object System.Windows.Media.MediaPlayer
$player.ScrubbingEnabled = $true
$player.Volume = 0
$player.Open([Uri]$Video)

# Wait for media to open
$tries = 0
while (-not $player.NaturalDuration.HasTimeSpan -and $tries -lt 100) {
  Start-Sleep -Milliseconds 200
  # Pump dispatcher so MediaOpened fires
  [System.Windows.Threading.Dispatcher]::CurrentDispatcher.Invoke([Action]{}, [System.Windows.Threading.DispatcherPriority]::Background)
  $tries++
}
if (-not $player.NaturalDuration.HasTimeSpan) { Write-Error "Could not open video"; exit 1 }

$dur = $player.NaturalDuration.TimeSpan.TotalSeconds
$w = $player.NaturalVideoWidth
$h = $player.NaturalVideoHeight
Write-Host "Duration: $([math]::Round($dur,1))s  ${w}x${h}"

for ($i = 0; $i -lt $Count; $i++) {
  $t = [math]::Round(($dur * ($i + 0.5)) / $Count, 1)
  $player.Position = [TimeSpan]::FromSeconds($t)
  Start-Sleep -Milliseconds 900
  [System.Windows.Threading.Dispatcher]::CurrentDispatcher.Invoke([Action]{}, [System.Windows.Threading.DispatcherPriority]::Background)
  Start-Sleep -Milliseconds 300

  $dv = New-Object System.Windows.Media.DrawingVisual
  $dc = $dv.RenderOpen()
  $dc.DrawVideo($player, (New-Object System.Windows.Rect(0, 0, $w, $h)))
  $dc.Close()
  $rtb = New-Object System.Windows.Media.Imaging.RenderTargetBitmap($w, $h, 96, 96, [System.Windows.Media.PixelFormats]::Pbgra32)
  $rtb.Render($dv)
  $enc = New-Object System.Windows.Media.Imaging.PngBitmapEncoder
  $enc.Frames.Add([System.Windows.Media.Imaging.BitmapFrame]::Create($rtb))
  $name = "frame_{0:D2}_t{1}s.png" -f $i, [int]$t
  $fs = [IO.File]::Create((Join-Path $OutDir $name))
  $enc.Save($fs)
  $fs.Close()
  Write-Host "saved $name"
}
$player.Close()
Write-Host "DONE -> $OutDir"
