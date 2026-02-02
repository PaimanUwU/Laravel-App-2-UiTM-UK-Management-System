<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Maintenance - UK UiTM Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8">
        <div class="text-center">
            <div class="mb-6">
                <img src="{{ asset('UKLogo.jpeg') }}" alt="UK UiTM Logo" class="mx-auto h-20 w-auto mb-4">
            </div>
            
            <div class="mb-6">
                <i class="fas fa-tools text-6xl text-blue-600 mb-4"></i>
            </div>
            
            <h1 class="text-3xl font-bold text-gray-800 mb-4">System Under Maintenance</h1>
            
            <p class="text-gray-600 mb-6">
                The UK UiTM Management System is currently undergoing scheduled maintenance. 
                We apologize for any inconvenience and appreciate your patience.
            </p>
            
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6 text-left">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            <strong>Note:</strong> System administrators can still access the system during maintenance.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="space-y-3">
                <div class="flex items-center justify-center text-sm text-gray-500">
                    <i class="fas fa-clock mr-2"></i>
                    <span>Maintenance started: {{ now()->format('d M Y H:i') }}</span>
                </div>
                
                <div class="flex items-center justify-center text-sm text-gray-500">
                    <i class="fas fa-sync-alt mr-2"></i>
                    <span>Expected completion: Soon</span>
                </div>
            </div>
            
            <div class="mt-8">
                <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Admin Login
                </a>
            </div>
        </div>
    </div>
    
    <script>
        // Auto-refresh every 5 minutes
        setTimeout(function() {
            location.reload();
        }, 300000);
    </script>
</body>
</html>
