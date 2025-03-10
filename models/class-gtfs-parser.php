<?php
/**
 * GTFS Parser class
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    GTFS_Importer
 * @subpackage GTFS_Importer/models
 */

/**
 * GTFS Parser class
 *
 * Handles the processing of GTFS zip files and extraction of data
 *
 * @package    GTFS_Importer
 * @subpackage GTFS_Importer/models
 * @author     Your Name <email@example.com>
 */
class GTFS_Parser {

    /**
     * Process the GTFS zip file and import data into database
     *
     * @param string $zip_path Path to the GTFS zip file
     * @param array $models Array of model objects for different GTFS data types
     * @param array $import_settings Settings that control what gets imported
     * @return array Statistics about what was imported
     * @throws Exception If any errors occur during import
     */
    public function process_gtfs_zip($zip_path, $models, $import_settings = array()) {
        global $wpdb;
        
        // Default import settings
        $default_settings = array(
            'routes' => true,
            'stops'  => true,
            'trips'  => false,
        );
        
        $import_settings = wp_parse_args($import_settings, $default_settings);
        
        // Results to return
        $results = array(
            'routes' => 0,
            'stops'  => 0,
            'trips'  => 0,
        );
        
        // Extract models
        $route_model = isset($models['route_model']) ? $models['route_model'] : null;
        $stop_model = isset($models['stop_model']) ? $models['stop_model'] : null;
        
        // Step 1: Extract the ZIP file to a temporary directory
        $tmp_dir = $this->unzip_to_temp_dir($zip_path);
        
        if (!$tmp_dir) {
            throw new Exception(__('Failed to extract the ZIP file.', 'gtfs-importer'));
        }
        public function process_gtfs_zip($zip_path, $models, $import_settings = array()) {
    global $wpdb;
    
    // Default import settings
    $default_settings = array(
        'routes' => true,
        'stops'  => true,
        'trips'  => false,
        'fares'  => false
    );
    
    $import_settings = wp_parse_args($import_settings, $default_settings);
    
    // Results to return
    $results = array(
        'routes' => 0,
        'stops'  => 0,
        'trips'  => 0,
        'fares'  => 0
    );
    
    // Extract models
    $route_model = isset($models['route_model']) ? $models['route_model'] : null;
    $stop_model = isset($models['stop_model']) ? $models['stop_model'] : null;
    $fare_model = isset($models['fare_model']) ? $models['fare_model'] : null;
    
    // ... [existing extraction code] ...
    
    try {
        // ... [existing import code] ...
        
        // Import fares if enabled
        if ($import_settings['fares'] && $fare_model) {
            $fare_attributes_file = $tmp_dir . '/fare_attributes.txt';
            $fare_rules_file = $tmp_dir . '/fare_rules.txt';
            
            // Import fare attributes
            $attributes_count = $this->import_fare_attributes($fare_attributes_file, $fare_model);
            
            // Import fare rules
            $rules_count = $this->import_fare_rules($fare_rules_file, $fare_model);
            
            $results['fares'] = $attributes_count;
        }
        
        // ... [existing commit code] ...
    }
    catch (Exception $e) {
        // ... [existing rollback code] ...
    }
    
    // ... [existing cleanup code] ...
    
    return $results;
}

        
        // Store information about the archive contents
        $dir_contents = scandir($tmp_dir);
        $files_in_archive = array_filter($dir_contents, function($file) use ($tmp_dir) {
            return !in_array($file, array('.', '..')) && is_file($tmp_dir . '/' . $file);
        });
        
        error_log('Files in GTFS archive: ' . print_r($files_in_archive, true));
        
        // Check for GTFS files in subdirectory
        $subdirs = array_filter($dir_contents, function($file) use ($tmp_dir) {
            return !in_array($file, array('.', '..')) && is_dir($tmp_dir . '/' . $file);
        });
        
        // If files are in a subdirectory, update the tmp_dir path
        if (!empty($subdirs) && !file_exists($tmp_dir . '/routes.txt') && !file_exists($tmp_dir . '/stops.txt')) {
            $subdir = reset($subdirs); // Get first subdirectory
            $potential_subdir = $tmp_dir . '/' . $subdir;
            $subdir_contents = scandir($potential_subdir);
            
            // Check if the subdirectory contains GTFS files
            if (in_array('routes.txt', $subdir_contents) || in_array('stops.txt', $subdir_contents)) {
                error_log('GTFS files found in subdirectory: ' . $subdir);
                $tmp_dir = $potential_subdir;
            }
        }
        
        // Check for required files in a more flexible way
        // Only check for files that are enabled in import settings
        $missing_critical_files = array();
        
        if ($import_settings['routes'] && !file_exists($tmp_dir . '/routes.txt')) {
            $missing_critical_files[] = 'routes.txt';
        }
        
        if ($import_settings['stops'] && !file_exists($tmp_dir . '/stops.txt')) {
            $missing_critical_files[] = 'stops.txt';
        }
        
        if (!empty($missing_critical_files)) {
            $this->delete_directory($tmp_dir);
            throw new Exception(
                sprintf(
                    __('Missing required files for selected import options: %s', 'gtfs-importer'),
                    implode(', ', $missing_critical_files)
                )
            );
        }
        
        // Start a transaction for data integrity
        $wpdb->query('START TRANSACTION');
        
        try {
            // Step 2: Import routes if enabled
            if ($import_settings['routes'] && $route_model) {
                $routes_file = $tmp_dir . '/routes.txt';
                $results['routes'] = $this->import_routes($routes_file, $route_model);
            }
            
            // Step 3: Import stops if enabled
            if ($import_settings['stops'] && $stop_model) {
                $stops_file = $tmp_dir . '/stops.txt';
                $results['stops'] = $this->import_stops($stops_file, $stop_model);
            }
            
            // Step 4: Add import to history
            $this->record_import_history(basename($zip_path), $results);
            
            // Commit the transaction
            $wpdb->query('COMMIT');
        } catch (Exception $e) {
            // Rollback on error
            $wpdb->query('ROLLBACK');
            
            // Clean up the temp directory
            $this->delete_directory($tmp_dir);
            
            // Re-throw the exception
            throw $e;
        }
        
        // Step 5: Clean up the temporary directory
        $this->delete_directory($tmp_dir);
        
        return $results;
    }
    
    /**
     * Import routes from routes.txt
     *
     * @param string $routes_file Path to routes.txt
     * @param GTFS_Route_Model $route_model Route model object
     * @return int Number of routes imported
     * @throws Exception If file can't be read or parsed
     */
    private function import_routes($routes_file, $route_model) {
        if (!file_exists($routes_file)) {
            throw new Exception(__('Routes file not found in the GTFS feed.', 'gtfs-importer'));
        }
        
        // Force proper file encoding detection
        $file_content = file_get_contents($routes_file);
        $encoding = mb_detect_encoding($file_content, array('UTF-8', 'ISO-8859-1', 'WINDOWS-1252'), true);
        
        // Log the detected encoding for debugging
        error_log('Routes.txt detected encoding: ' . $encoding);
        
        if ($encoding != 'UTF-8') {
            $file_content = mb_convert_encoding($file_content, 'UTF-8', $encoding);
            file_put_contents($routes_file . '.utf8', $file_content);
            $routes_file = $routes_file . '.utf8';
        }
        
        $handle = fopen($routes_file, 'r');
        if (!$handle) {
            throw new Exception(__('Unable to open routes.txt for reading.', 'gtfs-importer'));
        }
        
        // Get CSV headers and normalize them
        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            throw new Exception(__('Invalid routes.txt format or empty file.', 'gtfs-importer'));
        }
        
        // Normalize headers (trim whitespace, lowercase)
        $headers = array_map(function($header) {
            return strtolower(trim($header));
        }, $headers);
        
        // Log headers for debugging
        error_log('Routes.txt headers: ' . implode(', ', $headers));
        
        // Find required columns with flexible matching
        $idx_route_id = $this->find_column($headers, array('route_id'));
        $idx_shortname = $this->find_column($headers, array('route_short_name', 'shortname', 'short_name'));
        $idx_longname = $this->find_column($headers, array('route_long_name', 'longname', 'long_name'));
        $idx_type = $this->find_column($headers, array('route_type'));
        $idx_color = $this->find_column($headers, array('route_color'));
        $idx_text_color = $this->find_column($headers, array('route_text_color'));
        
        // Only route_id is absolutely required
        if ($idx_route_id === false) {
            fclose($handle);
            throw new Exception(__('Required column route_id not found in routes.txt', 'gtfs-importer'));
        }
        
        // Log found column indices for debugging
        error_log("Routes column indices - route_id: $idx_route_id, short_name: $idx_shortname, long_name: $idx_longname, type: $idx_type");
        
        // Clear existing routes if setting is enabled
        if (get_option('gtfs_clear_on_import', '1') === '1') {
            $route_model->truncate_table();
        }
        
        $count = 0;
        $row_count = 0;
        $error_count = 0;
        
        // Process the file line by line
        while (($row = fgetcsv($handle)) !== false) {
            $row_count++;
            
            // Skip rows with insufficient columns
            if (count($row) < count($headers)) {
                error_log("Warning: Row $row_count in routes.txt has fewer columns than expected");
                $error_count++;
                continue;
            }
            
            // Extract data with safety checks
            $route_id = $idx_route_id !== false && isset($row[$idx_route_id]) ? trim($row[$idx_route_id]) : '';
            $short_name = $idx_shortname !== false && isset($row[$idx_shortname]) ? trim($row[$idx_shortname]) : '';
            $long_name = $idx_longname !== false && isset($row[$idx_longname]) ? trim($row[$idx_longname]) : '';
            $route_type = $idx_type !== false && isset($row[$idx_type]) ? trim($row[$idx_type]) : null;
            $route_color = $idx_color !== false && isset($row[$idx_color]) ? trim($row[$idx_color]) : '';
            $route_text_color = $idx_text_color !== false && isset($row[$idx_text_color]) ? trim($row[$idx_text_color]) : '';
            
            if (empty($route_id)) {
                error_log("Warning: Skipping row $row_count due to missing route_id");
                $error_count++;
                continue; // Skip rows without a route_id
            }
            
            // Create route name from short name and long name
            $route_name = trim($short_name . ' ' . $long_name);
            if (empty($route_name)) {
                $route_name = "Route " . $route_id; // Fallback name
            }
            
            // Insert into database
            $route_data = array(
                'route_id' => $route_id,
                'route_name' => $route_name,
            );
            
            // Add optional fields if they exist
            if ($route_type !== null) {
                $route_data['route_type'] = $route_type;
            }
            
            if ($route_color) {
                $route_data['route_color'] = $route_color;
            }
            
            if ($route_text_color) {
                $route_data['route_text_color'] = $route_text_color;
            }
            
            $result = $route_model->insert_route($route_data);
            
            if ($result) {
                $count++;
            } else {
                error_log("Failed to insert route: " . print_r($route_data, true));
                $error_count++;
            }
        }
        
        fclose($handle);
        
        if ($count === 0) {
            error_log("Warning: No routes were imported from routes.txt. Total rows processed: $row_count, Errors: $error_count");
        } else {
            error_log("Successfully imported $count routes from routes.txt. Total rows: $row_count, Errors: $error_count");
        }
        
        return $count;
    }
    
    /**
     * Import stops from stops.txt
     *
     * @param string $stops_file Path to stops.txt
     * @param GTFS_Stop_Model $stop_model Stop model object
     * @return int Number of stops imported
     * @throws Exception If file can't be read or parsed
     */
    private function import_stops($stops_file, $stop_model) {
        if (!file_exists($stops_file)) {
            throw new Exception(__('Stops file not found in the GTFS feed.', 'gtfs-importer'));
        }
        /**
 * Import fare attributes from fare_attributes.txt
 *
 * @param string $fare_attributes_file Path to fare_attributes.txt
 * @param GTFS_Fare_Model $fare_model Fare model object
 * @return int Number of fare attributes imported
 * @throws Exception If file can't be read or parsed
 */
private function import_fare_attributes($fare_attributes_file, $fare_model) {
    if (!file_exists($fare_attributes_file)) {
        return 0; // Skip if file doesn't exist
    }
    
    $handle = fopen($fare_attributes_file, 'r');
    if (!$handle) {
        throw new Exception(__('Unable to open fare_attributes.txt for reading.', 'gtfs-importer'));
    }
    
    // Get CSV headers and normalize them
    $headers = fgetcsv($handle);
    if (!$headers) {
        fclose($handle);
        throw new Exception(__('Invalid fare_attributes.txt format or empty file.', 'gtfs-importer'));
    }
    
    // Find required columns
    $idx_fare_id = $this->find_column($headers, array('fare_id'));
    $idx_price = $this->find_column($headers, array('price'));
    $idx_currency = $this->find_column($headers, array('currency_type'));
    $idx_payment_method = $this->find_column($headers, array('payment_method'));
    $idx_transfers = $this->find_column($headers, array('transfers'));
    $idx_duration = $this->find_column($headers, array('transfer_duration'));
    $idx_agency = $this->find_column($headers, array('agency_id'));
    
    if ($idx_fare_id === false || $idx_price === false || $idx_currency === false || $idx_payment_method === false) {
        fclose($handle);
        throw new Exception(__('Required columns missing in fare_attributes.txt', 'gtfs-importer'));
    }
    
    $count = 0;
    
    // Process the file line by line
    while (($row = fgetcsv($handle)) !== false) {
        if (count($row) < count($headers)) {
            continue; // Skip malformed rows
        }
        
        $fare_id = $row[$idx_fare_id] ?? '';
        $price = $row[$idx_price] ?? '';
        $currency = $row[$idx_currency] ?? '';
        $payment_method = $row[$idx_payment_method] ?? '';
        
        if (empty($fare_id) || empty($price) || empty($currency) || empty($payment_method)) {
            continue; // Skip rows with missing required fields
        }
        
        $fare_data = array(
            'fare_id' => $fare_id,
            'price' => $price,
            'currency_type' => $currency,
            'payment_method' => $payment_method
        );
        
        // Add optional fields if they exist
        if ($idx_transfers !== false && isset($row[$idx_transfers])) {
            $fare_data['transfers'] = $row[$idx_transfers];
        }
        
        if ($idx_duration !== false && isset($row[$idx_duration])) {
            $fare_data['transfer_duration'] = $row[$idx_duration];
        }
        
        if ($idx_agency !== false && isset($row[$idx_agency])) {
            $fare_data['agency_id'] = $row[$idx_agency];
        }
        
        $result = $fare_model->insert_fare_attribute($fare_data);
        
        if ($result) {
            $count++;
        }
    }
    
    fclose($handle);
    return $count;
}

/**
 * Import fare rules from fare_rules.txt
 *
 * @param string $fare_rules_file Path to fare_rules.txt
 * @param GTFS_Fare_Model $fare_model Fare model object
 * @return int Number of fare rules imported
 * @throws Exception If file can't be read or parsed
 */
private function import_fare_rules($fare_rules_file, $fare_model) {
    if (!file_exists($fare_rules_file)) {
        return 0; // Skip if file doesn't exist
    }
    
    $handle = fopen($fare_rules_file, 'r');
    if (!$handle) {
        throw new Exception(__('Unable to open fare_rules.txt for reading.', 'gtfs-importer'));
    }
    
    // Get CSV headers and normalize them
    $headers = fgetcsv($handle);
    if (!$headers) {
        fclose($handle);
        throw new Exception(__('Invalid fare_rules.txt format or empty file.', 'gtfs-importer'));
    }
    
    // Find required columns
    $idx_fare_id = $this->find_column($headers, array('fare_id'));
    $idx_route_id = $this->find_column($headers, array('route_id'));
    $idx_origin_id = $this->find_column($headers, array('origin_id'));
    $idx_destination_id = $this->find_column($headers, array('destination_id'));
    $idx_contains_id = $this->find_column($headers, array('contains_id'));
    
    if ($idx_fare_id === false) {
        fclose($handle);
        throw new Exception(__('Required column fare_id missing in fare_rules.txt', 'gtfs-importer'));
    }
    
    $count = 0;
    
    // Process the file line by line
    while (($row = fgetcsv($handle)) !== false) {
        if (count($row) < count($headers)) {
            continue; // Skip malformed rows
        }
        
        $fare_id = $row[$idx_fare_id] ?? '';
        
        if (empty($fare_id)) {
            continue; // Skip rows with missing fare_id
        }
        
        $rule_data = array(
            'fare_id' => $fare_id
        );
        
        // Add optional fields if they exist
        if ($idx_route_id !== false && isset($row[$idx_route_id]) && !empty($row[$idx_route_id])) {
            $rule_data['route_id'] = $row[$idx_route_id];
        }
        
        if ($idx_origin_id !== false && isset($row[$idx_origin_id]) && !empty($row[$idx_origin_id])) {
            $rule_data['origin_id'] = $row[$idx_origin_id];
        }
        
        if ($idx_destination_id !== false && isset($row[$idx_destination_id]) && !empty($row[$idx_destination_id])) {
            $rule_data['destination_id'] = $row[$idx_destination_id];
        }
        
        if ($idx_contains_id !== false && isset($row[$idx_contains_id]) && !empty($row[$idx_contains_id])) {
            $rule_data['contains_id'] = $row[$idx_contains_id];
        }
        
        $result = $fare_model->insert_fare_rule($rule_data);
        
        if ($result) {
            $count++;
        }
    }
    
    fclose($handle);
    return $count;
}

        
        // Force proper file encoding detection
        $file_content = file_get_contents($stops_file);
        $encoding = mb_detect_encoding($file_content, array('UTF-8', 'ISO-8859-1', 'WINDOWS-1252'), true);
        
        // Log the detected encoding for debugging
        error_log('Stops.txt detected encoding: ' . $encoding);
        
        if ($encoding != 'UTF-8') {
            $file_content = mb_convert_encoding($file_content, 'UTF-8', $encoding);
            file_put_contents($stops_file . '.utf8', $file_content);
            $stops_file = $stops_file . '.utf8';
        }
        
        $handle = fopen($stops_file, 'r');
        if (!$handle) {
            throw new Exception(__('Unable to open stops.txt for reading.', 'gtfs-importer'));
        }
        
        // Get CSV headers and normalize them
        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            throw new Exception(__('Invalid stops.txt format or empty file.', 'gtfs-importer'));
        }
        
        // Normalize headers (trim whitespace, lowercase)
        $headers = array_map(function($header) {
            return strtolower(trim($header));
        }, $headers);
        
        // Log headers for debugging
        error_log('Stops.txt headers: ' . implode(', ', $headers));
        
        // Find required columns with flexible matching
        $idx_stop_id = $this->find_column($headers, array('stop_id'));
        $idx_name = $this->find_column($headers, array('stop_name', 'name'));
        $idx_lat = $this->find_column($headers, array('stop_lat', 'latitude', 'lat'));
        $idx_lon = $this->find_column($headers, array('stop_lon', 'longitude', 'long', 'lng'));
        
        // Check for required columns
        if ($idx_stop_id === false) {
            fclose($handle);
            throw new Exception(__('Required column stop_id not found in stops.txt', 'gtfs-importer'));
        }
        
        if ($idx_name === false) {
            fclose($handle);
            throw new Exception(__('Required column stop_name not found in stops.txt', 'gtfs-importer'));
        }
        
        // Log found column indices for debugging
        error_log("Stops column indices - stop_id: $idx_stop_id, stop_name: $idx_name, latitude: $idx_lat, longitude: $idx_lon");
        
        // Clear existing stops if setting is enabled
        if (get_option('gtfs_clear_on_import', '1') === '1') {
            $stop_model->truncate_table();
        }
        
        $count = 0;
        $row_count = 0;
        $error_count = 0;
        
        // Process the file line by line
        while (($row = fgetcsv($handle)) !== false) {
            $row_count++;
            
            // Skip rows with insufficient columns
            if (count($row) < count($headers)) {
                error_log("Warning: Row $row_count in stops.txt has fewer columns than expected");
                $error_count++;
                continue;
            }
            
            $stop_id = $idx_stop_id !== false && isset($row[$idx_stop_id]) ? trim($row[$idx_stop_id]) : '';
            $stop_name = $idx_name !== false && isset($row[$idx_name]) ? trim($row[$idx_name]) : '';
            $stop_lat = $idx_lat !== false && isset($row[$idx_lat]) ? trim($row[$idx_lat]) : null;
            $stop_lon = $idx_lon !== false && isset($row[$idx_lon]) ? trim($row[$idx_lon]) : null;
            
            if (empty($stop_id)) {
                error_log("Warning: Skipping row $row_count due to missing stop_id");
                $error_count++;
                continue;
            }
            
            if (empty($stop_name)) {
                $stop_name = "Stop " . $stop_id; // Fallback name
            }
            
            // Insert into database
            $stop_data = array(
                'stop_id' => $stop_id,
                'stop_name' => $stop_name,
            );
            
            // Add coordinates if they exist and are valid numbers
            if ($stop_lat !== null && $stop_lon !== null && 
                is_numeric($stop_lat) && is_numeric($stop_lon)) {
                $stop_data['stop_lat'] = (float) $stop_lat;
                $stop_data['stop_lon'] = (float) $stop_lon;
            }
            
            $result = $stop_model->insert_stop($stop_data);
            
            if ($result) {
                $count++;
            } else {
                error_log("Failed to insert stop: " . print_r($stop_data, true));
                $error_count++;
            }
        }
        
        fclose($handle);
        
        if ($count === 0) {
            error_log("Warning: No stops were imported from stops.txt. Total rows processed: $row_count, Errors: $error_count");
        } else {
            error_log("Successfully imported $count stops from stops.txt. Total rows: $row_count, Errors: $error_count");
        }
        
        return $count;
    }
    
    /**
     * Find column index by potential names (flexible matching)
     * 
     * @param array $headers Array of header names
     * @param array $possible_names Possible names for the column
     * @return int|bool Index of the column or false if not found
     */
    private function find_column($headers, $possible_names) {
        foreach ($possible_names as $name) {
            $idx = array_search($name, $headers);
            if ($idx !== false) {
                return $idx;
            }
        }
        return false;
    }
    
    /**
     * Record import history
     *
     * @param string $filename Original filename
     * @param array $counts Import statistics
     */
    private function record_import_history($filename, $counts) {
        $history = get_option('gtfs_importer_history', array());
        
        // Add the new import record at the beginning
        array_unshift($history, array(
            'time' => time(),
            'filename' => $filename,
            'counts' => $counts
        ));
        
        // Keep only the most recent 10 imports
        $history = array_slice($history, 0, 10);
        
        update_option('gtfs_importer_history', $history);
    }

    /**
     * Unzip the GTFS file to a temporary directory
     *
     * @param string $zip_path Path to the ZIP file
     * @return string|bool Path to the temp directory or false on failure
     */
    private function unzip_to_temp_dir($zip_path) {
        // Create a unique temporary directory
        $tmp_dir = sys_get_temp_dir() . '/gtfs_' . uniqid();
        
        if (!mkdir($tmp_dir, 0777, true)) {
            error_log("Failed to create temporary directory: $tmp_dir");
            return false;
        }
        
        $zip = new ZipArchive;
        $result = $zip->open($zip_path);
        
        if ($result === true) {
            // Log some information about the ZIP file
            error_log("ZIP file contains " . $zip->numFiles . " files");
            
            $extract_result = $zip->extractTo($tmp_dir);
            $zip->close();
            
            if (!$extract_result) {
                error_log("Failed to extract ZIP file to: $tmp_dir");
                $this->delete_directory($tmp_dir);
                return false;
            }
            
            error_log("Successfully extracted GTFS ZIP to: $tmp_dir");
            return $tmp_dir;
        }
        
        // Clean up on failure
        error_log("Failed to open ZIP file: $zip_path (Error code: $result)");
        $this->delete_directory($tmp_dir);
        return false;
    }

    /**
     * Delete a directory and all its contents recursively
     *
     * @param string $dir Path to the directory
     */
    private function delete_directory($dir) {
        if (!file_exists($dir)) {
            return;
        }
        
        $objects = array_diff(scandir($dir), array('.', '..'));
        
        foreach ($objects as $object) {
            $path = $dir . DIRECTORY_SEPARATOR . $object;
            
            if (is_dir($path)) {
                $this->delete_directory($path);
            } else {
                unlink($path);
            }
        }
        
        rmdir($dir);
    }
}
