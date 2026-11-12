# to build the andriod virtual app
 php artisan native:run android emulator-5554 --build=debug
# to watch the app
 php artisan native:watch android emulator-5554  
# to stop the app
 adb shell am force-stop com.admin.bloomriveraurora    
# to start the app
 adb shell am start -n com.admin.bloomriveraurora/com.nativephp.mobile.ui.MainActivity
# Regenerate Composer autoload files
 composer dump-autoload --optimize