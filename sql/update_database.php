<?php
require_once __DIR__ . '/../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Ler o arquivo SQL
    $sql = file_get_contents(__DIR__ . '/update_clients_table.sql');
    
    // Executar as queries
    $db->exec($sql);
    
    // Migrar dados antigos
    $query = "UPDATE clients SET 
              street = address,
              number = '',
              neighborhood = '',
              city = '',
              state = ''
              WHERE street IS NULL";
    $db->exec($query);
    
    echo "Banco de dados atualizado com sucesso!\n";
    
} catch (Exception $e) {
    echo "Erro ao atualizar banco de dados: " . $e->getMessage() . "\n";
}
