<?php
require_once '../config/database.php';

try {
    // Ler o arquivo SQL
    $sql = file_get_contents(__DIR__ . '/sales_tables.sql');
    
    // Executar as queries
    if ($conn->multi_query($sql)) {
        do {
            // Consumir todos os resultados
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->more_results() && $conn->next_result());
        
        echo "Tabelas criadas com sucesso!\n";
    }
    
} catch (Exception $e) {
    echo "Erro ao criar tabelas: " . $e->getMessage() . "\n";
}

$conn->close();
