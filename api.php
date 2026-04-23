<?php
declare(strict_types=1);

/**
 * VOLUND Backend API - Gestionnaire de fichiers Preseed Debian
 * PHP 8.4 - Optimisé pour Nginx
 */

// Configuration
const PRESEED_DIR = __DIR__ . '/preseeds/';
const SCRIPTS_DIR = __DIR__ . '/scripts/';
const CONFIG_FILE = __DIR__ . '/volund_config.json';

// Créer les répertoires s'ils n'existent pas
if (!file_exists(PRESEED_DIR)) {
    mkdir(PRESEED_DIR, 0755, true);
}
if (!file_exists(SCRIPTS_DIR)) {
    mkdir(SCRIPTS_DIR, 0755, true);
}

// Vérifier si c'est une requête pour servir directement un fichier (preseed ou script)
$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);

// Servir les preseeds directement (sans headers JSON)
if (preg_match('#^/preseed/([a-zA-Z0-9_\-\.]+\.cfg)$#', $path, $matches)) {
    servePreseedFile($matches[1]);
    exit();
}

// Servir les scripts directement (sans headers JSON)
if (preg_match('#^/script/([a-zA-Z0-9_\-\.]+\.sh)$#', $path, $matches)) {
    serveScriptFile($matches[1]);
    exit();
}

// Pour toutes les autres routes API, on ajoute les headers JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Gérer les requêtes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Fonctions pour servir les fichiers directement
function servePreseedFile(string $filename): void {
    $filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $filename);
    $filepath = PRESEED_DIR . $filename;
    
    if (!file_exists($filepath)) {
        http_response_code(404);
        header('Content-Type: text/plain; charset=utf-8');
        echo "# Preseed non trouvé: $filename\n";
        exit();
    }
    
    // Incrémenter les stats
    $config = loadConfigStatic();
    $config['stats']['total_deployments']++;
    $config['stats']['last_deployment'] = date('Y-m-d H:i:s');
    
    if (!isset($config['preseeds'][$filename]['downloads'])) {
        $config['preseeds'][$filename]['downloads'] = 0;
    }
    $config['preseeds'][$filename]['downloads']++;
    $config['preseeds'][$filename]['last_download'] = date('Y-m-d H:i:s');
    
    saveConfigStatic($config);
    
    // Servir le fichier
    header('Content-Type: text/plain; charset=UTF-8');
    header('Content-Disposition: inline; filename="' . $filename . '"');
    header('Cache-Control: no-cache, must-revalidate');
    readfile($filepath);
}

function serveScriptFile(string $filename): void {
    $filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $filename);
    $filepath = SCRIPTS_DIR . $filename;
    
    if (!file_exists($filepath)) {
        http_response_code(404);
        header('Content-Type: text/plain; charset=utf-8');
        echo "#!/bin/bash\n# Script non trouvé: $filename\necho 'Erreur: script non trouvé'\nexit 1\n";
        exit();
    }
    
    // Servir le fichier en texte brut
    header('Content-Type: text/x-shellscript; charset=UTF-8');
    header('Content-Disposition: inline; filename="' . $filename . '"');
    header('Cache-Control: no-cache, must-revalidate');
    header('X-Content-Type-Options: nosniff');
    readfile($filepath);
}

function loadConfigStatic(): array {
    if (file_exists(CONFIG_FILE)) {
        $content = file_get_contents(CONFIG_FILE);
        return json_decode($content, true) ?? getDefaultConfigStatic();
    }
    return getDefaultConfigStatic();
}

function getDefaultConfigStatic(): array {
    return [
        'preseeds' => [],
        'scripts' => [],
        'stats' => [
            'total_deployments' => 0,
            'last_update' => null
        ]
    ];
}

function saveConfigStatic(array $config): void {
    file_put_contents(CONFIG_FILE, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Classe principale de l'API
class VolundAPI {
    private array $config;
    
    public function __construct() {
        $this->config = $this->loadConfig();
    }
    
    private function loadConfig(): array {
        if (file_exists(CONFIG_FILE)) {
            $content = file_get_contents(CONFIG_FILE);
            return json_decode($content, true) ?? $this->getDefaultConfig();
        }
        return $this->getDefaultConfig();
    }
    
    private function getDefaultConfig(): array {
        return [
            'preseeds' => [],
            'stats' => [
                'total_deployments' => 0,
                'last_update' => null
            ]
        ];
    }
    
    private function saveConfig(): void {
        file_put_contents(CONFIG_FILE, json_encode($this->config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    private function sanitizeFilename(string $filename): string {
        $filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $filename);
        if (!str_ends_with($filename, '.cfg')) {
            $filename .= '.cfg';
        }
        return $filename;
    }
    
    private function sendJSON(array $data, int $code = 200): void {
        http_response_code($code);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit();
    }
    
    private function sendError(string $message, int $code = 400): void {
        $this->sendJSON(['success' => false, 'error' => $message], $code);
    }
    
    public function handleRequest(): void {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];
        $path = parse_url($uri, PHP_URL_PATH);
        
        // Router
        if (preg_match('#^/api/preseeds$#', $path)) {
            $this->handlePreseeds($method);
        } elseif (preg_match('#^/api/preseed/([a-zA-Z0-9_\-\.]+)$#', $path, $matches)) {
            $this->handlePreseedDetail($method, $matches[1]);
        } elseif (preg_match('#^/api/scripts$#', $path)) {
            $this->handleScripts($method);
        } elseif (preg_match('#^/api/scripts/download$#', $path)) {
            $this->downloadScriptFromUrl($method);
        } elseif (preg_match('#^/api/script/([a-zA-Z0-9_\-\.]+)$#', $path, $matches)) {
            $this->handleScriptDetail($method, $matches[1]);
        } elseif (preg_match('#^/api/themes$#', $path)) {
            $this->handleThemes($method);
        } elseif (preg_match('#^/api/stats$#', $path)) {
            $this->handleStats($method);
        } else {
            $this->sendError('Route non trouvée', 404);
        }
    }
    
    private function handlePreseeds(string $method): void {
        if ($method === 'GET') {
            $this->listPreseeds();
        } elseif ($method === 'POST') {
            $this->createPreseed();
        } else {
            $this->sendError('Méthode non autorisée', 405);
        }
    }
    
    private function listPreseeds(): void {
        $preseeds = [];
        $files = glob(PRESEED_DIR . '*.cfg') ?: [];
        
        foreach ($files as $filepath) {
            $filename = basename($filepath);
            $fileStats = stat($filepath);
            
            $preseedInfo = [
                'filename' => $filename,
                'name' => pathinfo($filename, PATHINFO_FILENAME),
                'size' => $fileStats['size'],
                'modified' => date('Y-m-d H:i:s', $fileStats['mtime']),
                'url' => $this->getBaseUrl() . '/preseed/' . $filename,
                'download_url' => $this->getBaseUrl() . '/api/preseed/' . $filename . '?download=1',
                'checksum' => md5_file($filepath)
            ];
            
            if (isset($this->config['preseeds'][$filename])) {
                $preseedInfo = array_merge($preseedInfo, $this->config['preseeds'][$filename]);
            }
            
            $preseeds[] = $preseedInfo;
        }
        
        $this->sendJSON([
            'success' => true,
            'count' => count($preseeds),
            'preseeds' => $preseeds
        ]);
    }
    
    private function createPreseed(): void {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (!isset($data['filename']) || !isset($data['content'])) {
            $this->sendError('Filename et content requis');
        }
        
        $filename = $this->sanitizeFilename($data['filename']);
        $filepath = PRESEED_DIR . $filename;
        
        if (file_exists($filepath)) {
            $this->sendError('Le fichier existe déjà', 409);
        }
        
        file_put_contents($filepath, $data['content']);
        
        $this->config['preseeds'][$filename] = [
            'description' => $data['description'] ?? '',
            'debian_version' => $data['debian_version'] ?? 'Debian 12',
            'type' => $data['type'] ?? 'custom',
            'created' => date('Y-m-d H:i:s')
        ];
        $this->config['stats']['last_update'] = date('Y-m-d H:i:s');
        $this->saveConfig();
        
        $this->sendJSON([
            'success' => true,
            'message' => 'Preseed créé avec succès',
            'filename' => $filename,
            'url' => $this->getBaseUrl() . '/preseed/' . $filename
        ], 201);
    }
    
    private function handlePreseedDetail(string $method, string $filename): void {
        $filename = $this->sanitizeFilename($filename);
        $filepath = PRESEED_DIR . $filename;
        
        if (!file_exists($filepath)) {
            $this->sendError('Preseed non trouvé', 404);
        }
        
        match($method) {
            'GET' => $this->getPreseed($filename, $filepath),
            'PUT' => $this->updatePreseed($filename, $filepath),
            'DELETE' => $this->deletePreseed($filename, $filepath),
            default => $this->sendError('Méthode non autorisée', 405)
        };
    }
    
    private function getPreseed(string $filename, string $filepath): void {
        if (isset($_GET['download'])) {
            header('Content-Type: text/plain; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            readfile($filepath);
            exit();
        }
        
        $this->sendJSON([
            'success' => true,
            'filename' => $filename,
            'content' => file_get_contents($filepath),
            'metadata' => $this->config['preseeds'][$filename] ?? [],
            'url' => $this->getBaseUrl() . '/preseed/' . $filename
        ]);
    }
    
    private function updatePreseed(string $filename, string $filepath): void {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (!isset($data['content'])) {
            $this->sendError('Content requis');
        }
        
        file_put_contents($filepath, $data['content']);
        
        if (!isset($this->config['preseeds'][$filename])) {
            $this->config['preseeds'][$filename] = [];
        }
        
        if (isset($data['description'])) {
            $this->config['preseeds'][$filename]['description'] = $data['description'];
        }
        if (isset($data['debian_version'])) {
            $this->config['preseeds'][$filename]['debian_version'] = $data['debian_version'];
        }
        if (isset($data['type'])) {
            $this->config['preseeds'][$filename]['type'] = $data['type'];
        }
        
        $this->config['preseeds'][$filename]['modified'] = date('Y-m-d H:i:s');
        $this->config['stats']['last_update'] = date('Y-m-d H:i:s');
        $this->saveConfig();
        
        $this->sendJSON([
            'success' => true,
            'message' => 'Preseed mis à jour',
            'filename' => $filename,
            'checksum' => md5_file($filepath)
        ]);
    }
    
    private function deletePreseed(string $filename, string $filepath): void {
        unlink($filepath);
        
        if (isset($this->config['preseeds'][$filename])) {
            unset($this->config['preseeds'][$filename]);
        }
        $this->config['stats']['last_update'] = date('Y-m-d H:i:s');
        $this->saveConfig();
        
        $this->sendJSON([
            'success' => true,
            'message' => 'Preseed supprimé'
        ]);
    }
    
    private function handleStats(string $method): void {
        if ($method !== 'GET') {
            $this->sendError('Méthode non autorisée', 405);
        }
        
        $totalPreseeds = count(glob(PRESEED_DIR . '*.cfg') ?: []);
        
        $this->sendJSON([
            'success' => true,
            'stats' => [
                'total_preseeds' => $totalPreseeds,
                'total_deployments' => $this->config['stats']['total_deployments'] ?? 0,
                'last_update' => $this->config['stats']['last_update'] ?? null,
                'last_deployment' => $this->config['stats']['last_deployment'] ?? null
            ]
        ]);
    }
    
    private function handleScripts(string $method): void {
        if ($method === 'GET') {
            $this->listScripts();
        } elseif ($method === 'POST') {
            $this->createScript();
        } else {
            $this->sendError('Méthode non autorisée', 405);
        }
    }
    
    private function listScripts(): void {
        $scripts = [];
        $files = glob(SCRIPTS_DIR . '*.sh') ?: [];
        
        foreach ($files as $filepath) {
            $filename = basename($filepath);
            $fileStats = stat($filepath);
            
            $scriptInfo = [
                'filename' => $filename,
                'size' => $fileStats['size'],
                'modified' => date('Y-m-d H:i:s', $fileStats['mtime']),
                'url' => $this->getBaseUrl() . '/script/' . $filename,
                'download_url' => $this->getBaseUrl() . '/api/script/' . $filename . '?download=1'
            ];
            
            if (isset($this->config['scripts'][$filename])) {
                $scriptInfo = array_merge($scriptInfo, $this->config['scripts'][$filename]);
            }
            
            $scripts[] = $scriptInfo;
        }
        
        $this->sendJSON([
            'success' => true,
            'count' => count($scripts),
            'scripts' => $scripts
        ]);
    }
    
    private function createScript(): void {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (!isset($data['filename']) || !isset($data['content'])) {
            $this->sendError('Filename et content requis');
        }
        
        $filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $data['filename']);
        if (!str_ends_with($filename, '.sh')) {
            $filename .= '.sh';
        }
        
        $filepath = SCRIPTS_DIR . $filename;
        
        file_put_contents($filepath, $data['content']);
        chmod($filepath, 0755);
        
        if (!isset($this->config['scripts'])) {
            $this->config['scripts'] = [];
        }
        
        $this->config['scripts'][$filename] = [
            'description' => $data['description'] ?? '',
            'created' => date('Y-m-d H:i:s')
        ];
        $this->saveConfig();
        
        $this->sendJSON([
            'success' => true,
            'message' => 'Script créé avec succès',
            'filename' => $filename,
            'url' => $this->getBaseUrl() . '/script/' . $filename
        ], 201);
    }
    
    private function downloadScriptFromUrl(string $method): void {
        if ($method !== 'POST') {
            $this->sendError('Méthode non autorisée', 405);
        }
        
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (!isset($data['url']) || !isset($data['filename'])) {
            $this->sendError('URL et filename requis');
        }
        
        $filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $data['filename']);
        if (!str_ends_with($filename, '.sh')) {
            $filename .= '.sh';
        }
        
        $filepath = SCRIPTS_DIR . $filename;
        
        // Télécharger le script
        $content = @file_get_contents($data['url']);
        if ($content === false) {
            $this->sendError('Impossible de télécharger le script depuis cette URL', 400);
        }
        
        file_put_contents($filepath, $content);
        chmod($filepath, 0755);
        
        if (!isset($this->config['scripts'])) {
            $this->config['scripts'] = [];
        }
        
        $this->config['scripts'][$filename] = [
            'description' => $data['description'] ?? '',
            'source_url' => $data['url'],
            'created' => date('Y-m-d H:i:s')
        ];
        $this->saveConfig();
        
        $this->sendJSON([
            'success' => true,
            'message' => 'Script téléchargé avec succès',
            'filename' => $filename,
            'url' => $this->getBaseUrl() . '/script/' . $filename
        ], 201);
    }
    
    private function handleScriptDetail(string $method, string $filename): void {
        $filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $filename);
        if (!str_ends_with($filename, '.sh')) {
            $filename .= '.sh';
        }
        
        $filepath = SCRIPTS_DIR . $filename;
        
        if (!file_exists($filepath)) {
            $this->sendError('Script non trouvé', 404);
        }
        
        match($method) {
            'GET' => $this->getScript($filename, $filepath),
            'DELETE' => $this->deleteScript($filename, $filepath),
            default => $this->sendError('Méthode non autorisée', 405)
        };
    }
    
    private function getScript(string $filename, string $filepath): void {
        if (isset($_GET['download'])) {
            header('Content-Type: text/plain; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            readfile($filepath);
            exit();
        }
        
        $this->sendJSON([
            'success' => true,
            'filename' => $filename,
            'content' => file_get_contents($filepath),
            'metadata' => $this->config['scripts'][$filename] ?? [],
            'url' => $this->getBaseUrl() . '/script/' . $filename
        ]);
    }
    
    private function deleteScript(string $filename, string $filepath): void {
        unlink($filepath);
        
        if (isset($this->config['scripts'][$filename])) {
            unset($this->config['scripts'][$filename]);
        }
        $this->saveConfig();
        
        $this->sendJSON([
            'success' => true,
            'message' => 'Script supprimé'
        ]);
    }
    
    private function handleThemes(string $method): void {
        if ($method !== 'GET') {
            $this->sendError('Méthode non autorisée', 405);
            return;
        }
        
        $this->listThemes();
    }
    
    private function listThemes(): void {
        $themesDir = __DIR__ . '/themes/';
        $themes = [];
        
        if (is_dir($themesDir)) {
            $files = glob($themesDir . '*.css') ?: [];
            
            foreach ($files as $filepath) {
                $filename = basename($filepath);
                
                // Ignorer base.css car c'est le fichier de structure
                if ($filename === 'base.css') {
                    continue;
                }
                
                // Enlever l'extension .css pour avoir le nom du thème
                $themeName = pathinfo($filename, PATHINFO_FILENAME);
                $themes[] = $themeName;
            }
        }
        
        // Fallback : thèmes par défaut si le dossier est vide
        if (empty($themes)) {
            $themes = ['cyberpunk', 'light', 'dark', 'nordic'];
        }
        
        $this->sendJSON([
            'success' => true,
            'themes' => $themes,
            'count' => count($themes)
        ]);
    }
    
    private function getBaseUrl(): string {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $script = dirname($_SERVER['SCRIPT_NAME']);
        $script = ($script === '/' || $script === '\\') ? '' : $script;
        return $protocol . '://' . $host . $script;
    }
}

// Exécuter l'API
try {
    $api = new VolundAPI();
    $api->handleRequest();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erreur serveur',
        'message' => $e->getMessage()
    ]);
}
