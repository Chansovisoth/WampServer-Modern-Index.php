<?php
// Custom WAMP Index by Klaude - Modern Design with Dark Mode
// Based on original WAMPServer index with improvements :]

$server_dir = "../";

require $server_dir . 'scripts/config.inc.php';
require $server_dir . 'scripts/wampserver.lib.php';

// Include virtualhost functions if not already included // ADDED BY KLAUDE (26/05/2025)
if (!function_exists('check_virtualhost')) {
    if (file_exists($server_dir . 'scripts/virtualhost.lib.php')) {
        require_once $server_dir . 'scripts/virtualhost.lib.php';
    } else {
        // Fallback: define a dummy function to avoid fatal error
        function check_virtualhost()
        {
            return array('ServerName' => array(), 'ServerNameValid' => array());
        }
    }
}

// Handle redirect after project creation or error
$projectCreated = false;
$projectError = false;
$newProjectName = '';
$newProjectPath = '';
$errorMessage = '';

if (isset($_GET['project_created']) && isset($_GET['project_path'])) {
    $projectCreated = true;
    $newProjectName = $_GET['project_created'];
    $newProjectPath = $_GET['project_path'];
}

if (isset($_GET['project_error'])) {
    $projectError = true;
    $errorMessage = urldecode($_GET['project_error']);
}

// Get server configuration - MOVED UP TO FIX UNDEFINED VARIABLE
$phpVersion = $wampConf['phpVersion'];
$apacheVersion = $wampConf['apacheVersion'];
$mysqlVersion = $wampConf['mysqlVersion'];
$port = $wampConf['apachePortUsed'];
$UrlPort = $port !== "80" ? ":" . $port : '';
$Mysqlport = $wampConf['mysqlPortUsed'];

// Handle project creation
if (isset($_POST['create_project'])) {
    $projectName = trim($_POST['project_name']) ?: 'Untitled';
    $projectDir = trim($_POST['project_directory']) ?: '';

    // Sanitize project name
    $projectName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $projectName);

    // Sanitize and validate directory
    if ($projectDir) {
        $projectDir = trim($projectDir, '/\\');
        $projectDir = preg_replace('/[^a-zA-Z0-9_\/-]/', '_', $projectDir);
        $fullPath = $projectDir . '/' . $projectName;
        $targetDir = $projectDir;
    } else {
        $fullPath = $projectName;
        $targetDir = '.';
    }

    // Check if target directory exists, create if it doesn't
    if ($projectDir && !is_dir($projectDir)) {
        if (!mkdir($projectDir, 0755, true)) {
            $redirectUrl = $_SERVER['PHP_SELF'] . '?project_error=' . urlencode("Failed to create directory: " . $projectDir);
            header('Location: ' . $redirectUrl);
            exit();
        }
    }

    // Check if project already exists
    if (is_dir($fullPath)) {
        $errorMsg = $langue === 'khmer'
            ? "គម្រោងដែលមានឈ្មោះ '" . $projectName . "' មានរួចហើយ។ សូមប្រើឈ្មោះផ្សេង។"
            : "Project '" . $projectName . "' already exists. Please use a different name.";
        $redirectUrl = $_SERVER['PHP_SELF'] . '?project_error=' . urlencode($errorMsg);
        header('Location: ' . $redirectUrl);
        exit();
    }

    // Create project directory
    if (mkdir($fullPath, 0755)) {
        // Create basic index.php file (keep existing content)
        $indexContent = '<?php
/**
 * ' . $projectName . ' - New WAMP Project
 * Created: ' . date('Y-m-d H:i:s') . '
 * Location: ' . $fullPath . '
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . $projectName . '</title>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; 
            max-width: 800px; 
            margin: 0 auto; 
            padding: 2rem; 
            line-height: 1.6;
            color: #333;
        }
        .header { 
            text-align: center; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            color: white; 
            padding: 2rem; 
            border-radius: 10px; 
            margin-bottom: 2rem;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin: 2rem 0;
        }
        .info-card {
            padding: 1.5rem;
            border: 1px solid #e1e5e9;
            border-radius: 8px;
            background: #f8f9fa;
        }
        .info-card h3 {
            margin-top: 0;
            color: #495057;
        }
        .breadcrumb {
            background: #e9ecef;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            font-family: monospace;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🚀 Welcome to ' . $projectName . '</h1>
        <p>Your new WAMP project is ready!</p>
    </div>
    
    <div class="breadcrumb">
        📁 Location: /' . $fullPath . '
    </div>
    
    <div class="info-grid">
        <div class="info-card">
            <h3>📁 Project Info</h3>
            <p><strong>Name:</strong> ' . $projectName . '</p>
            <p><strong>Created:</strong> ' . date('Y-m-d H:i:s') . '</p>
            <p><strong>Directory:</strong> ' . ($projectDir ?: 'www (root)') . '</p>
        </div>
        
        <div class="info-card">
            <h3>🔧 Server Info</h3>
            <p><strong>PHP Version:</strong> <?php echo PHP_VERSION; ?></p>
            <p><strong>Server:</strong> <?php echo $_SERVER["SERVER_SOFTWARE"] ?? "Unknown"; ?></p>
            <p><strong>Document Root:</strong> <?php echo $_SERVER["DOCUMENT_ROOT"] ?? "Unknown"; ?></p>
        </div>
    </div>
    
    <div class="info-card">
        <h3>🎯 Quick Start</h3>
        <p>Start building your project by editing this file or creating new files in your project directory.</p>
        <p><strong>Project URL:</strong> <a href="http://localhost' . $UrlPort . '/' . $fullPath . '/" target="_blank">http://localhost' . $UrlPort . '/' . $fullPath . '/</a></p>
    </div>

    <div class="info-card">
        <h3>📊 PHP Configuration</h3>
        <p><a href="?phpinfo=1" target="_blank" style="color: #667eea; text-decoration: none;">View PHP Info</a></p>
        
        <?php if (isset($_GET["phpinfo"])): ?>
            <hr style="margin: 1rem 0;">
            <?php phpinfo(); ?>
        <?php endif; ?>
    </div>
</body>
</html>';

        file_put_contents($fullPath . '/index.php', $indexContent);
        // Redirect to prevent form resubmission on refresh
        $redirectUrl = $_SERVER['PHP_SELF'] . '?project_created=' . urlencode($projectName) . '&project_path=' . urlencode($fullPath);
        header('Location: ' . $redirectUrl);
        exit();
    } else {
        $redirectUrl = $_SERVER['PHP_SELF'] . '?project_error=' . urlencode("Failed to create project directory");
        header('Location: ' . $redirectUrl);
        exit();
    }
}

// Get existing directories for dropdown
function getDirectories($dir = '.', $prefix = '')
{
    $directories = array();
    $projectsListIgnore = array('.', '..', 'wampthemes', 'wamplangues');

    if (is_dir($dir)) {
        $handle = opendir($dir);
        while (false !== ($file = readdir($handle))) {
            $fullPath = $dir . '/' . $file;
            if (is_dir($fullPath) && !in_array($file, $projectsListIgnore) && !str_starts_with($file, '.')) {
                $displayPath = $prefix ? $prefix . '/' . $file : $file;
                $directories[] = $displayPath;

                // Only go 1 level deep (limit to 2 folders total: www/ and www/YEAR3/)
                if (substr_count($displayPath, '/') < 1) {
                    $subdirs = getDirectories($fullPath, $displayPath);
                    $directories = array_merge($directories, $subdirs);
                }
            }
        }
        closedir($handle);
    }

    return $directories;
}

$availableDirectories = getDirectories();

// Language handling - Support all WAMP original languages + Khmer
$langue = $wampConf['language'];
$langueget = (!empty($_GET['lang']) ? strip_tags(trim($_GET['lang'])) : '');

// Get all available language files
$i_langues = glob('wamplangues/index_*.php');
$languages = array();
foreach ($i_langues as $value) {
    $languages[] = str_replace(array('wamplangues/index_', '.php'), '', $value);
}

// Validate and set language
if (in_array($langueget, $languages)) {
    $langue = $langueget;
}

// Load language files
include 'wamplangues/index_english.php';
if (file_exists('wamplangues/index_' . $langue . '.php')) {
    $langue_temp = $langues;
    include 'wamplangues/index_' . $langue . '.php';
    $langues = array_merge($langue_temp, $langues);
}

// Handle phpinfo
if (isset($_GET['phpinfo'])) {
    $type_info = intval(trim($_GET['phpinfo']));
    if ($type_info < -1 || $type_info > 64) $type_info = -1;
    phpinfo($type_info);
    exit();
}

// Get projects
$projectsListIgnore = array('.', '..', 'wampthemes', 'wamplangues');
$projects = array();
$handle = opendir(".");
while (false !== ($file = readdir($handle))) {
    if (is_dir($file) && !in_array($file, $projectsListIgnore)) {
        $projects[] = $file;
    }
}
closedir($handle);

// Get aliases
$aliasDir = $server_dir . 'alias/';
$aliases = array();
if (is_dir($aliasDir)) {
    $handle = opendir($aliasDir);
    while (false !== ($file = readdir($handle))) {
        if (is_file($aliasDir . $file) && strstr($file, '.conf')) {
            $aliasName = str_replace('.conf', '', $file);
            if (stripos($aliasName, 'phpmyadmin') !== false) {
                $aliases[] = array('name' => 'phpMyAdmin', 'url' => '/phpmyadmin', 'type' => 'database');
            } elseif (stripos($aliasName, 'adminer') !== false) {
                $aliases[] = array('name' => 'Adminer', 'url' => '/adminer', 'type' => 'database');
            } elseif (stripos($aliasName, 'phpsysinfo') !== false) {
                $aliases[] = array('name' => 'phpSysInfo', 'url' => '/phpsysinfo', 'type' => 'system');
            }
        }
    }
    closedir($handle);
}

// Get virtual hosts
$virtualHosts = array();
$vhostError = false;
$virtualHost = check_virtualhost();
if (isset($virtualHost['ServerName']) && is_array($virtualHost['ServerName'])) {
    foreach ($virtualHost['ServerName'] as $key => $value) {
        if ($virtualHost['ServerNameValid'][$value] === true) {
            $status = 'active';
            $ssl = in_array($value, $virtualHost['ServerNameHttps'] ?? array());
            $protocol = $ssl ? 'https://' : 'http://';
            $virtualHosts[] = array(
                'name' => $value,
                'url' => $protocol . $value,
                'status' => $status,
                'ssl' => $ssl
            );
        }
    }
}

// Get PHP extensions
$phpExtensions = get_loaded_extensions();
sort($phpExtensions);

// Error checking
$errors = array();
$phpini = mb_strtolower(trim(str_replace("\\", "/", php_ini_loaded_file())));
$c_phpConfFileOri = mb_strtolower($c_phpVersionDir . '/php' . $wampConf['phpVersion'] . '/' . $phpConfFileForApache);
if ($phpini != mb_strtolower($c_phpConfFile) && $phpini != $c_phpConfFileOri) {
    $errors[] = "PHP configuration file mismatch. Loaded: " . $phpini;
}

// Enhanced Language Configuration with flagcdn.com
$languageConfig = [
    'english' => [
        'name' => 'English (Default)',
        'flag' => 'us',
        'native' => 'English'
    ],
    'khmer' => [
        'name' => 'Khmer',
        'flag' => 'kh',
        'native' => 'ខ្មែរ'
    ],
    'bulgarian' => [
        'name' => 'Bulgarian',
        'flag' => 'bg',
        'native' => 'Български'
    ],
    'chinese' => [
        'name' => 'Chinese',
        'flag' => 'cn',
        'native' => '中文'
    ],
    'czech' => [
        'name' => 'Czech',
        'flag' => 'cz',
        'native' => 'Čeština'
    ],
    'dutch' => [
        'name' => 'Dutch',
        'flag' => 'nl',
        'native' => 'Nederlands'
    ],
    'french' => [
        'name' => 'French',
        'flag' => 'fr',
        'native' => 'Français'
    ],
    'german' => [
        'name' => 'German',
        'flag' => 'de',
        'native' => 'Deutsch'
    ],
    'hellenic' => [
        'name' => 'Hellenic',
        'flag' => 'gr',
        'native' => 'Ελληνικά'
    ],
    'italian' => [
        'name' => 'Italian',
        'flag' => 'it',
        'native' => 'Italiano'
    ],
    'japanese' => [
        'name' => 'Japanese',
        'flag' => 'jp',
        'native' => '日本語'
    ],
    'latvian' => [
        'name' => 'Latvian',
        'flag' => 'lv',
        'native' => 'Latviešu'
    ],
    'macedonian' => [
        'name' => 'macedonian',
        'flag' => 'mk',
        'native' => 'Македонски'
    ],
    'portuguese' => [
        'name' => 'Portuguese',
        'flag' => 'pt',
        'native' => 'Português'
    ],
    'romanian' => [
        'name' => 'romanian',
        'flag' => 'ro',
        'native' => 'Română'
    ],
    'spanish' => [
        'name' => 'Spanish',
        'flag' => 'es',
        'native' => 'Español'
    ],
    'turkish' => [
        'name' => 'turkish',
        'flag' => 'tr',
        'native' => 'Türkçe'
    ]
];


// Function to get flag SVG from flagcdn.com
function getFlagSvg($countryCode)
{
    return "https://flagcdn.com/{$countryCode}.svg";
}

// Generate language names with flags
$languageNames = array();
foreach ($languages as $lang) {
    $config = $languageConfig[$lang] ?? [
        'name' => ucfirst($lang),
        'flag' => 'white',
        'native' => ucfirst($lang)
    ];
    $languageNames[$lang] = $config;
}

?>
<!DOCTYPE html>
<html lang="<?php echo $langue === 'khmer' ? 'km' : 'en'; ?>" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WAMPServer - <?php echo $langues['titreHtml'] ?? 'Local Development'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="shortcut icon" href="favicon.ico" type="image/ico" />
    <?php if ($langue === 'khmer'): ?>
        <!-- Khmer font support -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Khmer:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <style>
            .khmer-font {
                font-family: 'Noto Sans Khmer', sans-serif;
            }

            body.khmer-lang {
                font-family: 'Noto Sans Khmer', sans-serif;
            }
        </style>
    <?php endif; ?>
    <script>
        // Dark mode configuration
        tailwind.config = {
            darkMode: 'class',
        }

        // Initialize theme
        if (localStorage.getItem('wamp-theme') === 'dark' ||
            (!localStorage.getItem('wamp-theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        }
    </script>
    <style>
        .fade-in {
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-bounce {
            animation: fadeInBounce 0.3s ease-out;
        }

        @keyframes fadeInBounce {
            0% {
            opacity: 0;
            transform: translateY(30px);
            }
            60% {
            opacity: 1;
            transform: translateY(-10px);
            }
            85% {
            transform: translateY(4px);
            }
            100% {
            opacity: 1;
            transform: translateY(0);
            }
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Enhanced flag styling */
        .flag-icon {
            width: 20px;
            height: 15px;
            border-radius: 2px;
            object-fit: cover;
            flex-shrink: 0;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(0, 0, 0, 0.1);
            display: inline-block;
            vertical-align: middle;
        }

        .dark .flag-icon {
            border-color: rgba(255, 255, 255, 0.1);
        }

        /* Language selector improvements */
        .language-option {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            transition: all 0.15s ease;
        }

        .language-option:hover {
            background-color: rgba(99, 102, 241, 0.1);
        }

        .dark .language-option:hover {
            background-color: rgba(99, 102, 241, 0.2);
        }

        /* Responsive flag sizing */
        @media (max-width: 640px) {
            .flag-icon {
                width: 18px;
                height: 13px;
            }
        }

        /* Loading state for flags */
        .flag-loading {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }

        @keyframes loading {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
        }

        .dark .flag-loading {
            background: linear-gradient(90deg, #374151 25%, #4b5563 50%, #374151 75%);
            background-size: 200% 100%;
        }
    </style>
</head>

<body class="h-full flex flex-col bg-gray-50 dark:bg-gray-900 transition-colors duration-200<?php echo $langue === 'khmer' ? ' khmer-lang' : ''; ?>">
    <!-- Success notification for project creation -->
    <?php if (isset($projectCreated) && $projectCreated): ?>
        <div id="success-notification" class="fixed top-4 right-4 z-50 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded fade-in max-w-md">
            <div class="flex items-start">
                <svg class="h-5 w-5 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <div>
                    <p class="font-medium"><?php echo $langue === 'khmer' ? 'គម្រោងត្រូវបានបង្កើតដោយជោគជ័យ!' : 'Project created successfully!'; ?></p>
                    <p class="text-sm mt-1">
                        <strong><?php echo htmlspecialchars($newProjectName); ?></strong><br>
                        <span class="text-green-600">📁 <?php echo htmlspecialchars($newProjectPath); ?></span>
                    </p>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-green-700 hover:text-green-900 flex-shrink-0">×</button>
            </div>
        </div>
        <script>
            // Auto-hide notification after 5 seconds and clean URL
            setTimeout(() => {
                const notification = document.getElementById('success-notification');
                if (notification) {
                    notification.style.opacity = '0';
                    setTimeout(() => notification.remove(), 300);
                }
            }, 5000);

            // Clean URL parameters to prevent notification on refresh
            if (window.location.search.includes('project_created')) {
                const url = new URL(window.location);
                url.searchParams.delete('project_created');
                url.searchParams.delete('project_path');
                window.history.replaceState({}, document.title, url.pathname + url.search);
            }
        </script>
    <?php endif; ?>

    <!-- Error notification for project creation -->
    <?php if (isset($projectError) && $projectError): ?>
        <div id="error-notification" class="fixed top-4 right-4 z-50 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded fade-in max-w-md">
            <div class="flex items-start">
                <svg class="h-5 w-5 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <p class="font-medium"><?php echo $langue === 'khmer' ? 'មានបញ្ហាក្នុងការបង្កើតគម្រោង!' : 'Project Creation Failed!'; ?></p>
                    <p class="text-sm mt-1">
                        <?php echo htmlspecialchars($errorMessage); ?>
                    </p>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-red-700 hover:text-red-900 flex-shrink-0">×</button>
            </div>
        </div>
        <script>
            // Auto-hide error notification after 7 seconds and clean URL
            setTimeout(() => {
                const notification = document.getElementById('error-notification');
                if (notification) {
                    notification.style.opacity = '0';
                    setTimeout(() => notification.remove(), 300);
                }
            }, 7000);

            // Clean URL parameters to prevent notification on refresh
            if (window.location.search.includes('project_error')) {
                const url = new URL(window.location);
                url.searchParams.delete('project_error');
                window.history.replaceState({}, document.title, url.pathname + url.search);
            }
        </script>
    <?php endif; ?>

    <!-- Header -->
    <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700 flex-shrink-0">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <button onclick="window.location.reload(true);" class="flex items-center space-x-4" title="Refresh (discard unsaved changes)">
                    <div class="flex items-center space-x-2">
                        <svg class="h-12 w-12 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                        </svg>
                        <div>
                            <h1 class="text-left text-2xl font-bold text-gray-900 dark:text-white">
                                <span class="mt-[-10] text-indigo-600 dark:text-indigo-400">WAMP</span>Server
                                <span class="relative -top-[3px] inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                                    v<?php echo $c_wampVersion; ?>
                                </span>
                            </h1>


                            <p class="text-left text-sm text-gray-500 dark:text-gray-400">
                                <?php echo $langue === 'khmer' ? 'ទីអភិវឌ្ឍន៍មូលដ្ឋាន' : 'Local Development Environment'; ?>
                            </p>
                        </div>
                    </div>
                </button>

                <div class="flex items-center space-x-4 ms-5">
                    <!-- Enhanced Language Selector with Flags -->
                    <div class="relative">
                        <button
                            type="button"
                            id="language-selector"
                            class="flex items-center justify-between w-48 px-3 py-2 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                            aria-expanded="false"
                            aria-haspopup="true">
                            <div class="flex items-center gap-2">
                                <?php
                                $currentLang = $languageNames[$langue] ?? $languageNames['english'];
                                ?>
                                <img
                                    src="<?php echo getFlagSvg($currentLang['flag']); ?>"
                                    alt="<?php echo $currentLang['name']; ?> flag"
                                    class="flag-icon"
                                    loading="lazy"
                                    onerror="this.style.display='none'">
                                <span class="text-gray-900 dark:text-white font-medium">
                                    <?php echo $currentLang['native']; ?>
                                </span>
                            </div>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <!-- Language Dropdown -->
                        <div id="language-dropdown" class="hidden absolute right-0 z-50 mt-1 w-64 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg max-h-80 overflow-y-auto custom-scrollbar" role="menu" aria-orientation="vertical">
                            <?php foreach ($languages as $lang): ?>
                                <?php $langConfig = $languageNames[$lang];
                                $isActive = $lang === $langue; ?>
                                <a href="?lang=<?php echo urlencode($lang); ?>" class="language-option text-gray-900 dark:text-white hover:bg-indigo-50 dark:hover:bg-indigo-900/20 <?php echo $isActive ? 'bg-indigo-50 dark:bg-indigo-900/30' : ''; ?>" role="menuitem">
                                    <img
                                        src="<?php echo getFlagSvg($langConfig['flag']); ?>"
                                        alt="<?php echo $langConfig['name']; ?> flag"
                                        class="flag-icon flag-loading"
                                        loading="lazy"
                                        onload="this.classList.remove('flag-loading')"
                                        onerror="this.style.display='none'; this.classList.remove('flag-loading')">
                                    <div class="flex flex-col">
                                        <span class="font-medium"><?php echo $langConfig['native']; ?></span>
                                        <?php if ($langConfig['native'] !== $langConfig['name']): ?>
                                            <span class="text-xs text-gray-500 dark:text-gray-400"><?php echo $langConfig['name']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($isActive): ?>
                                        <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400 ml-auto" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Dark Mode Toggle -->
                    <button onclick="toggleDarkMode()" class="p-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                        <svg id="sun-icon" class="h-4 w-4 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        <svg id="moon-icon" class="h-4 w-4 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content - This will grow to fill available space -->
    <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">
        <!-- Server Configuration -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 mb-8">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                    <svg class="h-5 w-5 mr-2 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <?php echo $langues['titreConf'] ?? ($langue === 'khmer' ? 'ការកំណត់រចនាសម្ព័ន្ធម៉ាស៊ីនមេ' : 'Server Configuration'); ?>
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                        <div class="flex items-center space-x-2 mb-2">
                            <svg class="h-4 w-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                            </svg>
                            <span class="font-medium text-gray-900 dark:text-white">Apache</span>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-300"><?php echo $apacheVersion; ?></p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            <?php echo $langue === 'khmer' ? 'ច្រក៖ ' : 'Port: '; ?><?php echo $port; ?>
                        </p>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                        <div class="flex items-center space-x-2 mb-2">
                            <svg class="h-4 w-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                            </svg>
                            <span class="font-medium text-gray-900 dark:text-white">PHP</span>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-300"><?php echo $phpVersion; ?></p>
                        <button onclick="showModal('phpExtensions')" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">
                            <?php echo $langue === 'khmer' ? 'មើលផ្នែកបន្ថែម' : 'View Extensions'; ?>
                        </button>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                        <div class="flex items-center space-x-2 mb-2">
                            <svg class="h-4 w-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                            </svg>
                            <span class="font-medium text-gray-900 dark:text-white">MySQL</span>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-300"><?php echo $mysqlVersion; ?></p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            <?php echo $langue === 'khmer' ? 'ច្រក៖ ' : 'Port: '; ?><?php echo $Mysqlport; ?>
                        </p>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                        <div class="flex items-center space-x-2 mb-2">
                            <svg class="h-4 w-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            <span class="font-medium text-gray-900 dark:text-white">
                                <?php echo $langue === 'khmer' ? 'ស្ថានភាព' : 'Status'; ?>
                            </span>
                        </div>
                        <div class="flex items-center space-x-1">
                            <div class="animate-bounce w-2 h-2 bg-green-500 rounded-full"></div>
                            <span class="text-sm text-green-600 dark:text-green-400">
                                <?php echo $langue === 'khmer' ? 'កំពុងដំណើរការ' : 'Running'; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Tabs -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <!-- Tab Navigation -->
            <div class="border-b border-gray-200 dark:border-gray-700">
                <nav class="flex space-x-8 px-6" aria-label="Tabs">
                    <button onclick="showTab('projects')" class="tab-button active py-4 px-1 border-b-2 font-medium text-sm" data-tab="projects">
                        <?php echo $langue === 'khmer' ? 'គម្រោង' : 'Projects'; ?> (<?php echo count($projects); ?>)
                    </button>
                    <button onclick="showTab('tools')" class="tab-button py-4 px-1 border-b-2 font-medium text-sm" data-tab="tools">
                        <?php echo $langue === 'khmer' ? 'ឧបករណ៍' : 'Tools'; ?> (<?php echo count($aliases); ?>)
                    </button>
                    <button onclick="showTab('vhosts')" class="tab-button py-4 px-1 border-b-2 font-medium text-sm" data-tab="vhosts">
                        <?php echo $langue === 'khmer' ? 'ម៉ាស៊ីនមេនិម្មិត' : 'Virtual Hosts'; ?> (<?php echo count($virtualHosts); ?>)
                    </button>
                    <button onclick="showTab('system')" class="tab-button py-4 px-1 border-b-2 font-medium text-sm" data-tab="system">
                        <?php echo $langue === 'khmer' ? 'ឧបករណ៍ប្រព័ន្ធ' : 'System Tools'; ?>
                    </button>
                </nav>
            </div>

            <!-- Tab Content -->
            <div class="p-6">
                <!-- Projects Tab -->
                <div id="projects-tab" class="tab-content">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                            <?php echo $langue === 'khmer' ? 'គម្រោងរបស់អ្នក' : 'Your Projects'; ?>
                        </h3>
                        <div>
                            <a href="http://localhost/phpmyadmin/" target="_blank" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 transition-colors">
                                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                                </svg>
                                phpMyAdmin
                            </a>
                            <button onclick="showModal('createProject')" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 transition-colors">
                                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                <?php echo $langue === 'khmer' ? 'បន្ថែមគម្រោង' : 'Add Project'; ?>
                            </button>
                        </div>

                    </div>

                    <?php if (empty($projects)): ?>
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">
                                <?php echo $langue === 'khmer' ? 'គ្មានគម្រោង' : 'No projects'; ?>
                            </h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                <?php echo $langue === 'khmer' ? 'ចាប់ផ្តើមដោយបង្កើតថតគម្រោងថ្មី។' : 'Get started by creating a new project folder.'; ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <?php foreach ($projects as $project): ?>
                                <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4 hover:shadow-md transition-shadow dark:hover:bg-gray-700">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="font-medium text-gray-900 dark:text-white"><?php echo htmlspecialchars($project); ?></h4>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            <?php echo $langue === 'khmer' ? 'ដំណើរការ' : 'Active'; ?>
                                        </span>
                                    </div>
                                    <a href="http://localhost<?php echo $UrlPort; ?>/<?php echo urlencode($project); ?>/"
                                        target="_blank"
                                        class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                                        <?php echo $langue === 'khmer' ? 'បើកគម្រោង →' : 'Open Project →'; ?>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Tools Tab -->
                <div id="tools-tab" class="tab-content hidden">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-6">
                        <?php echo $langue === 'khmer' ? 'ឧបករណ៍អភិវឌ្ឍន៍' : 'Development Tools'; ?>
                    </h3>

                    <div class="space-y-4">
                        <?php foreach ($aliases as $alias): ?>
                            <div class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-600 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div class="p-2 bg-indigo-100 dark:bg-indigo-900 rounded-lg">
                                        <?php if ($alias['type'] === 'database'): ?>
                                            <svg class="h-4 w-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                                            </svg>
                                        <?php else: ?>
                                            <svg class="h-4 w-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900 dark:text-white"><?php echo $alias['name']; ?></h4>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            <?php echo $langue === 'khmer' ? ($alias['type'] === 'database' ? 'ឧបករណ៍មូលដ្ឋានទិន្នន័យ' : 'ឧបករណ៍ប្រព័ន្ធ') : ucfirst($alias['type']) . ' Tool'; ?>
                                        </p>
                                    </div>
                                </div>
                                <a href="<?php echo $alias['url']; ?>"
                                    target="_blank"
                                    class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                    <?php echo $langue === 'khmer' ? 'បើក' : 'Open'; ?>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Virtual Hosts Tab -->
                <div id="vhosts-tab" class="tab-content hidden">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                            <?php echo $langue === 'khmer' ? 'ម៉ាស៊ីនមេនិម្មិត' : 'Virtual Hosts'; ?>
                        </h3>
                        <a href="add_vhost.php?lang=<?php echo $langue; ?>" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <?php echo $langue === 'khmer' ? 'បន្ថែមម៉ាស៊ីនមេនិម្មិត' : 'Add Virtual Host'; ?>
                        </a>
                    </div>

                    <?php if (empty($virtualHosts)): ?>
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">
                                <?php echo $langue === 'khmer' ? 'គ្មានម៉ាស៊ីនមេនិម្មិត' : 'No virtual hosts'; ?>
                            </h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                <?php echo $langue === 'khmer' ? 'បង្កើតម៉ាស៊ីនមេនិម្មិតសម្រាប់ដែនមូលដ្ឋានរបស់អ្នក។' : 'Create virtual hosts for your local domains.'; ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($virtualHosts as $vhost): ?>
                                <div class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-600 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <div class="p-2 bg-indigo-100 dark:bg-indigo-900 rounded-lg">
                                            <svg class="h-4 w-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h4 class="font-medium text-gray-900 dark:text-white"><?php echo htmlspecialchars($vhost['name']); ?></h4>
                                            <div class="flex items-center space-x-2">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                    <?php echo $langue === 'khmer' ? 'ដំណើរការ' : ucfirst($vhost['status']); ?>
                                                </span>
                                                <?php if ($vhost['ssl']): ?>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                        SSL
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <a href="<?php echo $vhost['url']; ?>"
                                        target="_blank"
                                        class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        <?php echo $langue === 'khmer' ? 'ទស្សនា' : 'Visit'; ?>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- System Tools Tab -->
                <div id="system-tab" class="tab-content hidden">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-6">
                        <?php echo $langue === 'khmer' ? 'ឧបករណ៍ប្រព័ន្ធ' : 'System Tools'; ?>
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <a href="?phpinfo=-1" target="_blank" class="block p-4 border border-gray-200 dark:border-gray-600 rounded-lg hover:shadow-md transition-shadow dark:hover:bg-gray-700">
                            <div class="font-medium text-gray-900 dark:text-white">
                                <?php echo $langue === 'khmer' ? 'ព័ត៌មាន PHP' : 'PHP Info'; ?>
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                <?php echo $langue === 'khmer' ? 'មើលការកំណត់រចនាសម្ព័ន្ធ PHP និងផ្នែកបន្ថែមដែលបានផ្ទុក' : 'View PHP configuration and loaded extensions'; ?>
                            </div>
                        </a>

                        <?php if (function_exists('xdebug_info')): ?>
                            <a href="?xdebuginfo" target="_blank" class="block p-4 border border-gray-200 dark:border-gray-600 rounded-lg hover:shadow-md transition-shadow dark:hover:bg-gray-700">
                                <div class="font-medium text-gray-900 dark:text-white">
                                    <?php echo $langue === 'khmer' ? 'ព័ត៌មាន Xdebug' : 'Xdebug Info'; ?>
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    <?php echo $langue === 'khmer' ? 'មើលការកំណត់រចនាសម្ព័ន្ធ Xdebug' : 'View Xdebug configuration'; ?>
                                </div>
                            </a>
                        <?php endif; ?>

                        <button onclick="showModal('serverInfo')" class="block w-full text-left p-4 border border-gray-200 dark:border-gray-600 rounded-lg hover:shadow-md transition-shadow dark:hover:bg-gray-700">
                            <div class="font-medium text-gray-900 dark:text-white">
                                <?php echo $langue === 'khmer' ? 'ព័ត៌មានម៉ាស៊ីនមេ' : 'Server Information'; ?>
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                <?php echo $langue === 'khmer' ? 'មើលការកំណត់រចនាសម្ព័ន្ធម៉ាស៊ីនមេលម្អិត' : 'View detailed server configuration'; ?>
                            </div>
                        </button>

                        <button onclick="showModal('logs')" class="block w-full text-left p-4 border border-gray-200 dark:border-gray-600 rounded-lg hover:shadow-md transition-shadow dark:hover:bg-gray-700">
                            <div class="font-medium text-gray-900 dark:text-white">
                                <?php echo $langue === 'khmer' ? 'កំណត់ហេតុម៉ាស៊ីនមេ' : 'Server Logs'; ?>
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                <?php echo $langue === 'khmer' ? 'មើលកំណត់ហេតុកំហុស Apache និង PHP' : 'View Apache and PHP error logs'; ?>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Error Messages -->
        <?php if (!empty($errors)): ?>
            <div class="mt-8 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-400 p-4 rounded-lg fade-in">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                            <?php echo $langue === 'khmer' ? 'បានរកឃើញបញ្ហាការកំណត់រចនាសម្ព័ន្ធ' : 'Configuration Issues Detected'; ?>
                        </h3>
                        <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                            <?php foreach ($errors as $error): ?>
                                <p><?php echo htmlspecialchars($error); ?></p>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <!-- Footer - Now properly sticky at bottom -->
    <footer class="flex-shrink-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-600 dark:text-gray-400 text-sm">
                    <?php echo $langue === 'khmer' ? 'WAMPServer - ទំព័រដើមផ្ទាល់ខ្លួនដោយ 🦊<a tooltip="Visit my GitHub" href="https://github.com/Chansovisoth">Chansovisoth</a>' : 'WAMPServer - Custom Index by 🦊<a href="https://github.com/Chansovisoth">Chansovisoth</a>'; ?>
                </p>
                <div class="flex space-x-4 mt-4 md:mt-0">
                    <a href="http://wampserver.aviatechno.net/" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline text-sm">
                        <?php echo $langue === 'khmer' ? 'ឯកសារណែនាំ' : 'Documentation'; ?>
                    </a>
                    <a href="http://forum.wampserver.com/" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline text-sm">
                        <?php echo $langue === 'khmer' ? 'វេទិកាជំនួយ' : 'Support Forum'; ?>
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Modals -->
    <!-- PHP Extensions Modal -->
    <div id="phpExtensions" class="modal fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="fade-in-bounce relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    <?php echo $langue === 'khmer' ? 'ផ្នែកបន្ថែម PHP ដែលបានផ្ទុក' : 'PHP Loaded Extensions'; ?>
                </h3>
                <button onclick="hideModal('phpExtensions')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="grid grid-cols-3 md:grid-cols-5 gap-2 max-h-96 overflow-y-auto custom-scrollbar">
                <?php foreach ($phpExtensions as $ext): ?>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                        <?php echo htmlspecialchars($ext); ?>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Server Info Modal -->
    <div id="serverInfo" class="modal fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    <?php echo $langue === 'khmer' ? 'ព័ត៌មានម៉ាស៊ីនមេ' : 'Server Information'; ?>
                </h3>
                <button onclick="hideModal('serverInfo')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="space-y-4 max-h-96 overflow-y-auto custom-scrollbar">
                <div>
                    <h4 class="font-medium text-gray-900 dark:text-white">
                        <?php echo $langue === 'khmer' ? 'កម្មវិធីម៉ាស៊ីនមេ' : 'Server Software'; ?>
                    </h4>
                    <p class="text-sm text-gray-600 dark:text-gray-300"><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></p>
                </div>
                <div>
                    <h4 class="font-medium text-gray-900 dark:text-white">
                        <?php echo $langue === 'khmer' ? 'ឫសឯកសារ' : 'Document Root'; ?>
                    </h4>
                    <p class="text-sm text-gray-600 dark:text-gray-300"><?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown'; ?></p>
                </div>
                <div>
                    <h4 class="font-medium text-gray-900 dark:text-white">
                        <?php echo $langue === 'khmer' ? 'ឯកសារកំណត់រចនាសម្ព័ន្ធ PHP' : 'PHP Configuration File'; ?>
                    </h4>
                    <p class="text-sm text-gray-600 dark:text-gray-300"><?php echo php_ini_loaded_file(); ?></p>
                </div>
                <div>
                    <h4 class="font-medium text-gray-900 dark:text-white">
                        <?php echo $langue === 'khmer' ? 'ពេលវេលាម៉ាស៊ីនមេ' : 'Server Time'; ?>
                    </h4>
                    <p class="text-sm text-gray-600 dark:text-gray-300"><?php echo date('Y-m-d H:i:s'); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Logs Modal -->
    <div id="logs" class="modal fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    <?php echo $langue === 'khmer' ? 'កំណត់ហេតុម៉ាស៊ីនមេ' : 'Server Logs'; ?>
                </h3>
                <button onclick="hideModal('logs')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-300">
                <p><?php echo $langue === 'khmer' ? 'ឯកសារកំណត់ហេតុជាធម្មតាមានទីតាំងនៅ៖' : 'Log files are typically located in:'; ?></p>
                <ul class="mt-2 space-y-1 list-disc list-inside">
                    <li><?php echo $langue === 'khmer' ? 'កំណត់ហេតុកំហុស Apache៖' : 'Apache Error Log:'; ?> <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded"><?php echo $server_dir; ?>logs/apache_error.log</code></li>
                    <li><?php echo $langue === 'khmer' ? 'កំណត់ហេតុកំហុស PHP៖' : 'PHP Error Log:'; ?> <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded"><?php echo $server_dir; ?>logs/php_error.log</code></li>
                    <li><?php echo $langue === 'khmer' ? 'កំណត់ហេតុចូលប្រើ៖' : 'Access Log:'; ?> <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded"><?php echo $server_dir; ?>logs/access.log</code></li>
                </ul>
                <p class="mt-4"><?php echo $langue === 'khmer' ? 'ប្រើរូបតំណាង WAMP ដើម្បីចូលប្រើឯកសារកំណត់ហេតុដោយផ្ទាល់។' : 'Use the WAMP tray icon to access log files directly.'; ?></p>
            </div>
        </div>
    </div>

    <!-- Create Project Modal -->
    <div id="createProject" class="modal fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="fade-in-bounce relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    <?php echo $langue === 'khmer' ? 'បង្កើតគម្រោងថ្មី' : 'Create New Project'; ?>
                </h3>
                <button onclick="hideModal('createProject')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form method="post" class="space-y-4">
                <!-- Project Name Field -->
                <div>
                    <label for="project_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <?php echo $langue === 'khmer' ? 'ឈ្មោះគម្រោង' : 'Project Name'; ?> *
                    </label>
                    <input
                        type="text"
                        id="project_name"
                        name="project_name"
                        required
                        placeholder="<?php echo $langue === 'khmer' ? 'បញ្ចូលឈ្មោះគម្រោង' : 'Enter project name'; ?>"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        <?php echo $langue === 'khmer' ? 'ប្រើតែអក្សរ, លេខ, សហសញ្ញា និងដាស់' : 'Use only letters, numbers, underscores, and hyphens'; ?>
                    </p>
                </div>

                <!-- Directory Field -->
                <div>
                    <label for="project_directory" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <?php echo $langue === 'khmer' ? 'ទីតាំងផ្ទុកគម្រោង (ស្រេចចិត្ត)' : 'Target Directory (Optional)'; ?>
                    </label>
                    <div class="relative">
                        <input
                            type="text"
                            id="project_directory"
                            name="project_directory"
                            placeholder="<?php echo $langue === 'khmer' ? 'ទុកទទេសម្រាប់ www' : 'Leave empty for www root or enter directory'; ?>"
                            class="w-full px-3 py-2 pr-10 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            autocomplete="off"
                            onclick="toggleDirectoryDropdown()"
                            onkeyup="filterDirectories()">
                        <button type="button" onclick="toggleDirectoryDropdown()" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <!-- Directory Dropdown -->
                        <div id="directory-dropdown" class="hidden absolute z-10 w-full mt-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg max-h-48 overflow-y-auto">
                            <div class="py-1">
                                <div onclick="selectDirectory('')" class="px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer text-gray-900 dark:text-white">
                                    <span class="font-medium">www/</span> <span class="text-gray-500 dark:text-gray-400">(root)</span>
                                </div>
                                <?php foreach ($availableDirectories as $dir): ?>
                                    <div onclick="selectDirectory('<?php echo htmlspecialchars($dir); ?>')" class="px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer text-gray-900 dark:text-white directory-option" data-directory="<?php echo strtolower($dir); ?>">
                                        <span class="font-medium">www/<?php echo htmlspecialchars($dir); ?>/</span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        <?php echo $langue === 'khmer' ? 'ឧទាហរណ៍៖ YEAR3, projects/web, ឬទុកទទេសម្រាប់ www' : 'Examples: YEAR3, projects/web, or leave empty for www root'; ?>
                    </p>
                </div>

                <!-- Preview -->
                <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-md">
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        <strong><?php echo $langue === 'khmer' ? 'ទីតាំងគម្រោង៖' : 'Project will be created at:'; ?></strong>
                    </p>
                    <p id="project-preview" class="text-sm font-mono text-indigo-600 dark:text-indigo-400 mt-1">
                        www/<span id="preview-directory"></span><span id="preview-name">[project-name]</span>/
                    </p>
                </div>

                <!-- Error Display -->
                <!-- <?php if (isset($projectError)): ?>
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md p-3">
                    <p class="text-sm text-red-600 dark:text-red-400"><?php echo htmlspecialchars($projectError); ?></p>
                </div>
            <?php endif; ?> -->

                <!-- Buttons -->
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="hideModal('createProject')" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <?php echo $langue === 'khmer' ? 'បោះបង់' : 'Cancel'; ?>
                    </button>
                    <button type="submit" name="create_project" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600">
                        <?php echo $langue === 'khmer' ? 'បង្កើតគម្រោង' : 'Create Project'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Dark mode functionality
        function toggleDarkMode() {
            const html = document.documentElement;
            const isDark = html.classList.contains('dark');

            if (isDark) {
                html.classList.remove('dark');
                localStorage.setItem('wamp-theme', 'light');
            } else {
                html.classList.add('dark');
                localStorage.setItem('wamp-theme', 'dark');
            }
        }

        // Tab functionality
        function showTab(tabName) {
            // Hide all tab contents
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => content.classList.add('hidden'));

            // Remove active class from all tab buttons
            const tabButtons = document.querySelectorAll('.tab-button');
            tabButtons.forEach(button => {
                button.classList.remove('active', 'border-indigo-500', 'text-indigo-600', 'dark:text-indigo-400');
                button.classList.add('border-transparent', 'text-gray-500', 'dark:text-gray-400', 'hover:text-gray-700', 'dark:hover:text-gray-300', 'hover:border-gray-300', 'dark:hover:border-gray-600');
            });

            // Show selected tab content
            document.getElementById(tabName + '-tab').classList.remove('hidden');

            // Add active class to selected tab button
            const activeButton = document.querySelector(`[data-tab="${tabName}"]`);
            activeButton.classList.add('active', 'border-indigo-500', 'text-indigo-600', 'dark:text-indigo-400');
            activeButton.classList.remove('border-transparent', 'text-gray-500', 'dark:text-gray-400', 'hover:text-gray-700', 'dark:hover:text-gray-300', 'hover:border-gray-300', 'dark:hover:border-gray-600');
        }

        // Modal functionality
        function showModal(modalId) {
            document.getElementById(modalId).classList.remove('hidden');
        }

        function hideModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target === modal) {
                    modal.classList.add('hidden');
                }
            });
        }

        // Initialize tab styles
        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('.tab-button');
            tabButtons.forEach(button => {
                if (!button.classList.contains('active')) {
                    button.classList.add('border-transparent', 'text-gray-500', 'dark:text-gray-400', 'hover:text-gray-700', 'dark:hover:text-gray-300', 'hover:border-gray-300', 'dark:hover:border-gray-600');
                } else {
                    button.classList.add('border-indigo-500', 'text-indigo-600', 'dark:text-indigo-400');
                }
            });
        });

        // Directory dropdown functionality
        function toggleDirectoryDropdown() {
            const dropdown = document.getElementById('directory-dropdown');
            dropdown.classList.toggle('hidden');
        }

        function selectDirectory(directory) {
            const input = document.getElementById('project_directory');
            input.value = directory;
            document.getElementById('directory-dropdown').classList.add('hidden');
            updateProjectPreview();
        }

        function filterDirectories() {
            const input = document.getElementById('project_directory');
            const filter = input.value.toLowerCase();
            const options = document.querySelectorAll('.directory-option');

            options.forEach(option => {
                const directory = option.getAttribute('data-directory');
                if (directory.includes(filter)) {
                    option.style.display = 'block';
                } else {
                    option.style.display = 'none';
                }
            });

            updateProjectPreview();
        }

        function updateProjectPreview() {
            const projectName = document.getElementById('project_name').value || '[project-name]';
            const directory = document.getElementById('project_directory').value;

            document.getElementById('preview-name').textContent = projectName;
            document.getElementById('preview-directory').textContent = directory ? directory + '/' : '';
        }

        // Update preview when typing
        document.addEventListener('DOMContentLoaded', function() {
            const projectNameInput = document.getElementById('project_name');
            const projectDirInput = document.getElementById('project_directory');

            if (projectNameInput) {
                projectNameInput.addEventListener('input', updateProjectPreview);
            }
            if (projectDirInput) {
                projectDirInput.addEventListener('input', updateProjectPreview);
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('directory-dropdown');
            const input = document.getElementById('project_directory');

            if (dropdown && input && !dropdown.contains(event.target) && !input.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
        });

        // Enhanced Language Selector functionality
        document.addEventListener('DOMContentLoaded', function() {
            const languageSelector = document.getElementById('language-selector');
            const languageDropdown = document.getElementById('language-dropdown');

            if (languageSelector && languageDropdown) {
                // Toggle dropdown
                languageSelector.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const isHidden = languageDropdown.classList.contains('hidden');
                    languageDropdown.classList.toggle('hidden', !isHidden);
                    languageSelector.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!languageSelector.contains(e.target) && !languageDropdown.contains(e.target)) {
                        languageDropdown.classList.add('hidden');
                        languageSelector.setAttribute('aria-expanded', 'false');
                    }
                });

                // Handle keyboard navigation
                languageSelector.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        languageSelector.click();
                    } else if (e.key === 'Escape') {
                        languageDropdown.classList.add('hidden');
                        languageSelector.setAttribute('aria-expanded', 'false');
                        languageSelector.focus();
                    }
                });

                // Preload flag images for better performance
                const flagUrls = [
                    <?php foreach ($languages as $lang): ?>
                        <?php $langConfig = $languageNames[$lang]; ?> '<?php echo getFlagSvg($langConfig['flag']); ?>',
                    <?php endforeach; ?>
                ];

                flagUrls.forEach(url => {
                    const img = new Image();
                    img.src = url;
                });
            }
        });
    </script>
</body>

</html>