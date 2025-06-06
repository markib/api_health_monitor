# Path to your PHP executable
$phpPath = "C:\xampp\php\php.exe"

# Path to your Laravel project root (where artisan is)
$laravelPath = "C:\xampp\htdocs\api-health-monitor"

# Change directory to Laravel project
Set-Location -Path $laravelPath

# Run the Laravel schedule:run command
& $phpPath artisan schedule:run
