<?php
$version = "57";
$zipFileName = "sistema_vendas_v{$version}.zip";

// Lista de arquivos e diretórios para incluir
$includeDirs = [
    'api',
    'assets',
    'config',
    'includes',
    'modules',
    'vendor'
];

// Lista de arquivos individuais para incluir
$includeFiles = [
    'index.php',
    '.htaccess'
];

// Lista de exclusões
$excludePatterns = [
    '.git',
    '.gitignore',
    '.vscode',
    'node_modules',
    'composer.lock',
    'package-lock.json',
    'generate_release.php',
    'README.md',
    'sistema_vendas_v*.zip',
    'temp',
    'logs',
    '*.log',
    '*.tmp',
    '*.cache',
    'thumbs.db',
    '.DS_Store'
];

// Changelog
$changelog = "
Versão $version - " . date('d/m/Y') . "
- Implementado CRUD completo de vendas
- Adicionado dashboard com indicadores de vendas
- Adicionada funcionalidade de cancelamento de vendas
- Adicionada funcionalidade de marcar venda como paga
- Melhorada a interface de vendas com Select2
- Adicionados filtros de busca por data e status
- Melhorada a acessibilidade dos formulários
- Corrigidos bugs no gerenciamento de estoque
";

// Função para verificar se um arquivo deve ser excluído
function shouldExclude($file, $patterns) {
    $file = str_replace('\\', '/', $file); // Normalizar separadores de caminho
    foreach ($patterns as $pattern) {
        if (strpos($file, $pattern) !== false) {
            return true;
        }
    }
    return false;
}

// Função para adicionar arquivos ao ZIP
function addFilesToZip($baseDir, $zip, $excludePatterns) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($baseDir),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($baseDir) + 1);

            // Pular arquivos que devem ser excluídos
            if (shouldExclude($relativePath, $excludePatterns)) {
                continue;
            }

            $zip->addFile($filePath, $relativePath);
        }
    }
}

try {
    // Criar novo arquivo ZIP
    $zip = new ZipArchive();
    
    if ($zip->open($zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        throw new Exception("Não foi possível criar o arquivo ZIP");
    }

    // Adicionar diretórios incluídos
    foreach ($includeDirs as $dir) {
        if (is_dir($dir)) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir),
                RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen(__DIR__) + 1);

                    // Pular arquivos que devem ser excluídos
                    if (shouldExclude($relativePath, $excludePatterns)) {
                        continue;
                    }

                    $zip->addFile($filePath, $relativePath);
                }
            }
        }
    }

    // Adicionar arquivos individuais
    foreach ($includeFiles as $file) {
        if (file_exists($file)) {
            $zip->addFile($file, $file);
        }
    }

    // Adicionar arquivo de changelog
    $zip->addFromString('changelog.txt', $changelog);

    // Fechar o arquivo ZIP
    $zip->close();
    
    echo "Arquivo ZIP criado com sucesso: $zipFileName\n";
    
} catch (Exception $e) {
    echo "Erro ao criar arquivo ZIP: " . $e->getMessage() . "\n";
    exit(1);
}
