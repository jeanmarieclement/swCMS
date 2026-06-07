<?php
/**
 * Convert MySQL data dump to SQLite compatible format
 * This script reads MySQL dump and converts it to SQLite INSERT statements
 */

function convertMySQLToSQLite($inputFile, $outputFile) {
    $input = file_get_contents($inputFile);
    $output = "";
    
    // Remove MySQL specific comments and statements
    $patterns = [
        '/\/\*!\d+ [^*]*\*\/;?/',
        '/\/\*!\d+ [^*]*\*\//',
        '/LOCK TABLES[^;]*;/',
        '/UNLOCK TABLES;/',
        '/SET [^;]*;/',
        '/-- Server version[^;]*/',
        '/-- Host:[^;]*/',
        '/-- Dump completed[^;]*/',
        '/-- MySQL dump[^;]*/',
    ];
    
    foreach ($patterns as $pattern) {
        $input = preg_replace($pattern, '', $input);
    }
    
    // Split into lines for processing
    $lines = explode("\n", $input);
    $processedLines = [];
    
    foreach ($lines as $line) {
        $line = trim($line);
        
        // Skip empty lines and comments
        if (empty($line) || strpos($line, '--') === 0) {
            continue;
        }
        
        // Process INSERT statements
        if (strpos($line, 'INSERT INTO') === 0) {
            // Remove MySQL backticks
            $line = str_replace('`', '', $line);
            
            // Handle specific data type conversions
            $line = convertDataTypes($line);
            
            $processedLines[] = $line;
        }
    }
    
    // Join the processed lines
    $output = implode("\n", $processedLines);
    
    file_put_contents($outputFile, $output);
    echo "Conversion completed. Output saved to: $outputFile\n";
}

function convertDataTypes($line) {
    // Convert MySQL specific datetime formats to SQLite compatible
    // MySQL: '2025-04-16 14:37:50' -> SQLite: '2025-04-16 14:37:50'
    // No conversion needed for basic datetime
    
    // Convert MySQL NULL handling
    $line = preg_replace('/,NULL,/', ',NULL,', $line);
    
    // Convert boolean values (MySQL uses 0/1, SQLite also uses 0/1)
    // No conversion needed
    
    return $line;
}

// Run the conversion
$inputFile = '/var/www/html/data/mysql_data_export.sql';
$outputFile = '/var/www/html/scripts/sqlite_data.sql';

if (file_exists($inputFile)) {
    convertMySQLToSQLite($inputFile, $outputFile);
} else {
    echo "Input file not found: $inputFile\n";
    exit(1);
}
?>