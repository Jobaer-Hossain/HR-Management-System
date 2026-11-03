$ErrorActionPreference = 'Stop'

python -m venv .face-venv
& .\.face-venv\Scripts\python.exe -m pip install --upgrade pip
& .\.face-venv\Scripts\python.exe -m pip install -r requirements-face.txt

$modelDir = Join-Path (Get-Location) 'storage\app\face-models'
New-Item -ItemType Directory -Force -Path $modelDir | Out-Null
$zip = Join-Path $modelDir 'buffalo_l.zip'
Invoke-WebRequest -Uri 'https://github.com/deepinsight/insightface/releases/download/v0.7/buffalo_l.zip' -OutFile $zip
Expand-Archive -LiteralPath $zip -DestinationPath $modelDir -Force
Remove-Item -LiteralPath $zip -Force
Write-Host 'Face runtime and SCRFD/ArcFace models installed.'
