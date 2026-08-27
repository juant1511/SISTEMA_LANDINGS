<?php
/**
 * supabase.php — Supabase REST API wrapper that emulates PDO
 * Drop-in replacement for MySQL PDO in the Mundo Gorras project.
 */

define('SUPABASE_URL', 'https://iwqhaxegjefuhanfmejh.supabase.co');
define('SUPABASE_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Iml3cWhheGVnamVmdWhhbmZtZWpoIiwicm9sZSI6ImFub24iLCJpYXQiOjE3ODcyMDc0OTQsImV4cCI6MjEwMjc4MzQ5NH0.XhLiH4zndNkPyui73bjeW_Qaa0vDgGaTwARxT00DnfM');

class SupabaseStatement {
    private $sql;
    private $params = [];
    private $result = [];
    private $rowCount = 0;
    private $lastId = null;
    private $fetched = false;
    
    public function __construct($sql) {
        $this->sql = $sql;
    }
    
    public function execute($params = []) {
        $this->params = $params;
        $parsed = SupabaseSQL::parse($this->sql, $this->params);
        
        $response = SupabaseHTTP::request(
            $parsed['method'],
            $parsed['endpoint'],
            $parsed['body'],
            $parsed['headers'] ?? []
        );
        
        if ($parsed['method'] === 'GET') {
            $this->result = $response['data'];
            $this->rowCount = count($this->result);
        } else {
            $this->result = $response['data'];
            $this->rowCount = is_array($response['data']) ? count($response['data']) : 0;
            // Extract last ID from INSERT response
            if ($parsed['method'] === 'POST' && !empty($this->result)) {
                $row = is_array($this->result) && isset($this->result[0]) ? $this->result[0] : $this->result;
                if (isset($row['id'])) {
                    $this->lastId = $row['id'];
                    SupabasePDO::$_lastId = $row['id'];
                }
            }
        }
        
        $this->fetched = false;
        return true;
    }
    
    public function fetch($mode = null) {
        if (empty($this->result)) return false;
        if (!$this->fetched) {
            $this->fetched = true;
            return is_array($this->result) && isset($this->result[0]) 
                ? $this->result[0] 
                : $this->result;
        }
        return false;
    }
    
    public function fetchAll($mode = null) {
        return $this->result ?: [];
    }
    
    public function rowCount() {
        return $this->rowCount;
    }
    
    public function fetchColumn($col = 0) {
        $row = $this->fetch();
        if (!$row) return false;
        $values = array_values($row);
        return $values[$col] ?? false;
    }
}

class SupabaseSQL {
    // Parse SQL and convert to Supabase REST API call
    public static function parse($sql, $params = []) {
        $sql = trim($sql);
        
        // For complex queries (JOIN, GROUP BY, aggregates, subqueries),
        // fall back to Supabase RPC raw SQL execution
        if (self::isComplexQuery($sql)) {
            return self::parseViaRPC($sql, $params);
        }
        
        // Determine operation type
        if (preg_match('/^SELECT/i', $sql)) {
            return self::parseSelect($sql, $params);
        } elseif (preg_match('/^INSERT/i', $sql)) {
            return self::parseInsert($sql, $params);
        } elseif (preg_match('/^UPDATE/i', $sql)) {
            return self::parseUpdate($sql, $params);
        } elseif (preg_match('/^DELETE/i', $sql)) {
            return self::parseDelete($sql, $params);
        }
        
        throw new Exception("Unsupported SQL: $sql");
    }
    
    private static function isComplexQuery($sql) {
        // Detect JOINs, GROUP BY, subqueries, aggregates with table aliases
        return preg_match('/\bJOIN\b/i', $sql) 
            || preg_match('/\bGROUP\s+BY\b/i', $sql)
            || preg_match('/\bHAVING\b/i', $sql)
            || preg_match('/\bUNION\b/i', $sql)
            || preg_match('/\w+\.\*/i', $sql)  // table.* aliases like p.*
            || preg_match('/COALESCE\s*\(/i', $sql)
            || (preg_match('/\bCOUNT\s*\(/i', $sql) && preg_match('/\bFROM\b/i', $sql))
            || (preg_match('/\bAVG\s*\(/i', $sql) && preg_match('/\bFROM\b/i', $sql))
            || (preg_match('/\bSUM\s*\(/i', $sql) && preg_match('/\bFROM\b/i', $sql));
    }
    
    private static function parseViaRPC($sql, $params) {
        // Replace ? placeholders with actual values for the RPC call
        $index = 0;
        $finalSql = preg_replace_callback('/\?/', function($m) use ($params, &$index) {
            $val = $params[$index++] ?? null;
            if ($val === null) return 'NULL';
            if (is_numeric($val)) return $val;
            return "'" . str_replace("'", "''", $val) . "'";
        }, $sql);
        
        $url = SUPABASE_URL . '/rest/v1/rpc/exec_sql';
        
        return [
            'method' => 'POST',
            'endpoint' => $url,
            'body' => ['query' => $finalSql],
            'headers' => ['Prefer: return=representation'],
            'table' => '_rpc'
        ];
    }
    

    private static function parseSelect($sql, $params) {
        // Extract table name
        preg_match('/FROM\s+[`]?([\w]+)[`]?/i', $sql, $m);
        $table = $m[1] ?? '';
        
        $queryParams = ['select' => '*'];
        
        // Check for specific columns in SELECT
        if (preg_match('/^SELECT\s+(.+?)\s+FROM/i', $sql, $cm)) {
            $cols = trim($cm[1]);
            if ($cols !== '*') {
                // Handle aggregates
                if (preg_match('/COUNT\s*\(/i', $cols) || preg_match('/AVG\s*\(/i', $cols) || preg_match('/SUM\s*\(/i', $cols)) {
                    // For aggregates, fetch all and compute in PHP
                    $queryParams['select'] = '*';
                }
            }
        }
        
        // Parse WHERE clause
        if (preg_match('/WHERE\s+(.+?)(?:\s+ORDER|\s+LIMIT|\s+GROUP|$)/i', $sql, $wm)) {
            $where = trim($wm[1]);
            $paramIndex = 0;
            
            // Split by AND
            $conditions = preg_split('/\s+AND\s+/i', $where);
            foreach ($conditions as $cond) {
                $cond = trim($cond);
                
                // Handle IN (?, ?, ?)
                if (preg_match('/[`]?([\w]+)[`]?\s+IN\s*\(([^)]+)\)/i', $cond, $im)) {
                    $col = $im[1];
                    $placeholders = explode(',', $im[2]);
                    $values = [];
                    foreach ($placeholders as $ph) {
                        if (trim($ph) === '?') {
                            $values[] = $params[$paramIndex++] ?? '';
                        } else {
                            $values[] = trim($ph, "' ");
                        }
                    }
                    $queryParams[$col] = 'in.(' . implode(',', $values) . ')';
                }
                // Handle col <> ?
                elseif (preg_match('/[`]?([\w]+)[`]?\s*<>\s*\??/i', $cond, $nm)) {
                    $col = $nm[1];
                    $val = $params[$paramIndex++] ?? '0';
                    $queryParams[$col] = 'neq.' . $val;
                }
                // Handle col != ?
                elseif (preg_match('/[`]?([\w]+)[`]?\s*!=\s*\??/i', $cond, $nm)) {
                    $col = $nm[1];
                    $val = $params[$paramIndex++] ?? '0';
                    $queryParams[$col] = 'neq.' . $val;
                }
                // Handle col >= ?
                elseif (preg_match('/[`]?([\w]+)[`]?\s*>=\s*\??/i', $cond, $nm)) {
                    $col = $nm[1];
                    $val = $params[$paramIndex++] ?? '0';
                    $queryParams[$col] = 'gte.' . $val;
                }
                // Handle col <= ?
                elseif (preg_match('/[`]?([\w]+)[`]?\s*<=\s*\??/i', $cond, $nm)) {
                    $col = $nm[1];
                    $val = $params[$paramIndex++] ?? '0';
                    $queryParams[$col] = 'lte.' . $val;
                }
                // Handle col > ?
                elseif (preg_match('/[`]?([\w]+)[`]?\s*>\s*\??/i', $cond, $nm)) {
                    $col = $nm[1];
                    $val = $params[$paramIndex++] ?? '0';
                    $queryParams[$col] = 'gt.' . $val;
                }
                // Handle col < ?
                elseif (preg_match('/[`]?([\w]+)[`]?\s*<\s*\??/i', $cond, $nm)) {
                    $col = $nm[1];
                    $val = $params[$paramIndex++] ?? '0';
                    $queryParams[$col] = 'lt.' . $val;
                }
                // Handle col = ? or col=value
                elseif (preg_match('/[`]?([\w]+)[`]?\s*=\s*(.+)/i', $cond, $em)) {
                    $col = $em[1];
                    $valPart = trim($em[2]);
                    if ($valPart === '?') {
                        $val = $params[$paramIndex++] ?? '';
                    } else {
                        $val = trim($valPart, "' ");
                    }
                    $queryParams[$col] = 'eq.' . $val;
                }
                // Handle IS NULL
                elseif (preg_match('/[`]?([\w]+)[`]?\s+IS\s+NULL/i', $cond, $nm)) {
                    $queryParams[$nm[1]] = 'is.null';
                }
                // Handle IS NOT NULL
                elseif (preg_match('/[`]?([\w]+)[`]?\s+IS\s+NOT\s+NULL/i', $cond, $nm)) {
                    $queryParams[$nm[1]] = 'not.is.null';
                }
            }
        }
        
        // Parse ORDER BY
        if (preg_match('/ORDER\s+BY\s+[`]?([\w]+)[`]?\s*(ASC|DESC)?/i', $sql, $om)) {
            $dir = strtolower($om[2] ?? 'asc');
            $queryParams['order'] = $om[1] . '.' . ($dir === 'desc' ? 'desc' : 'asc');
        }
        
        // Parse LIMIT
        if (preg_match('/LIMIT\s+(\d+)/i', $sql, $lm)) {
            $queryParams['limit'] = $lm[1];
        }
        
        $url = SUPABASE_URL . '/rest/v1/' . $table . '?' . http_build_query($queryParams);
        
        return [
            'method' => 'GET',
            'endpoint' => $url,
            'body' => null,
            'table' => $table
        ];
    }
    
    private static function parseInsert($sql, $params) {
        // INSERT INTO table (col1, col2) VALUES (?, ?)
        preg_match('/INSERT\s+INTO\s+[`]?([\w]+)[`]?\s*\(([^)]+)\)\s*VALUES\s*\(([^)]+)\)/i', $sql, $m);
        $table = $m[1] ?? '';
        $cols = array_map('trim', explode(',', str_replace('`', '', $m[2] ?? '')));
        
        $body = [];
        $paramIndex = 0;
        $valueParts = array_map('trim', explode(',', $m[3] ?? ''));
        
        foreach ($cols as $i => $col) {
            $valPart = $valueParts[$i] ?? '?';
            if (trim($valPart) === '?') {
                $body[$col] = $params[$paramIndex++] ?? null;
            } elseif (strtoupper(trim($valPart)) === 'NOW()' || strtoupper(trim($valPart)) === 'CURRENT_TIMESTAMP') {
                $body[$col] = date('c');
            } else {
                $body[$col] = trim($valPart, "' ");
            }
        }
        
        $url = SUPABASE_URL . '/rest/v1/' . $table;
        
        return [
            'method' => 'POST',
            'endpoint' => $url,
            'body' => $body,
            'headers' => ['Prefer: return=representation'],
            'table' => $table
        ];
    }
    
    private static function parseUpdate($sql, $params) {
        // UPDATE table SET col1 = ?, col2 = ? WHERE id = ?
        preg_match('/UPDATE\s+[`]?([\w]+)[`]?\s+SET\s+(.+?)\s+WHERE\s+(.+)/i', $sql, $m);
        $table = $m[1] ?? '';
        $setPart = $m[2] ?? '';
        $wherePart = $m[3] ?? '';
        
        // Parse SET
        $body = [];
        $paramIndex = 0;
        $sets = self::splitSetClause($setPart);
        foreach ($sets as $set) {
            if (preg_match('/[`]?([\w]+)[`]?\s*=\s*(.+)/i', trim($set), $sm)) {
                $col = trim($sm[1]);
                $valPart = trim($sm[2]);
                if ($valPart === '?') {
                    $body[$col] = $params[$paramIndex++] ?? null;
                } elseif (strtoupper($valPart) === 'NULL') {
                    $body[$col] = null;
                } elseif (strtoupper($valPart) === 'NOW()' || strtoupper($valPart) === 'CURRENT_TIMESTAMP') {
                    $body[$col] = date('c');
                } else {
                    $body[$col] = trim($valPart, "' ");
                }
            }
        }
        
        // Parse WHERE for URL
        $queryParams = [];
        $conditions = preg_split('/\s+AND\s+/i', $wherePart);
        foreach ($conditions as $cond) {
            if (preg_match('/[`]?([\w]+)[`]?\s*=\s*(.+)/i', trim($cond), $wm)) {
                $col = trim($wm[1]);
                $valPart = trim($wm[2]);
                if ($valPart === '?') {
                    $val = $params[$paramIndex++] ?? '';
                } else {
                    $val = trim($valPart, "' ");
                }
                $queryParams[$col] = 'eq.' . $val;
            }
        }
        
        $url = SUPABASE_URL . '/rest/v1/' . $table . '?' . http_build_query($queryParams);
        
        return [
            'method' => 'PATCH',
            'endpoint' => $url,
            'body' => $body,
            'headers' => ['Prefer: return=representation'],
            'table' => $table
        ];
    }
    
    private static function parseDelete($sql, $params) {
        preg_match('/DELETE\s+FROM\s+[`]?([\w]+)[`]?\s+WHERE\s+(.+)/i', $sql, $m);
        $table = $m[1] ?? '';
        $wherePart = $m[2] ?? '';
        
        $queryParams = [];
        $paramIndex = 0;
        $conditions = preg_split('/\s+AND\s+/i', $wherePart);
        foreach ($conditions as $cond) {
            if (preg_match('/[`]?([\w]+)[`]?\s*=\s*(.+)/i', trim($cond), $wm)) {
                $col = trim($wm[1]);
                $valPart = trim($wm[2]);
                if ($valPart === '?') {
                    $val = $params[$paramIndex++] ?? '';
                } else {
                    $val = trim($valPart, "' ");
                }
                $queryParams[$col] = 'eq.' . $val;
            }
        }
        
        $url = SUPABASE_URL . '/rest/v1/' . $table . '?' . http_build_query($queryParams);
        
        return [
            'method' => 'DELETE',
            'endpoint' => $url,
            'body' => null,
            'table' => $table
        ];
    }
    
    private static function splitSetClause($setPart) {
        // Smart split by comma, respecting function calls like NOW()
        $parts = [];
        $depth = 0;
        $current = '';
        for ($i = 0; $i < strlen($setPart); $i++) {
            $ch = $setPart[$i];
            if ($ch === '(') $depth++;
            elseif ($ch === ')') $depth--;
            elseif ($ch === ',' && $depth === 0) {
                $parts[] = $current;
                $current = '';
                continue;
            }
            $current .= $ch;
        }
        if ($current !== '') $parts[] = $current;
        return $parts;
    }
}

class SupabaseHTTP {
    public static function request($method, $url, $body = null, $extraHeaders = []) {
        $ch = curl_init();
        
        $headers = [
            'apikey: ' . SUPABASE_KEY,
            'Authorization: Bearer ' . SUPABASE_KEY,
            'Content-Type: application/json'
        ];
        
        foreach ($extraHeaders as $h) {
            $headers[] = $h;
        }
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HEADER => false
        ]);
        
        switch (strtoupper($method)) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                if ($body) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
                break;
            case 'PATCH':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
                if ($body) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
            case 'GET':
            default:
                break;
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            error_log("Supabase cURL error: $error");
            throw new Exception("Supabase connection error: $error");
        }
        
        if ($httpCode >= 400) {
            error_log("Supabase HTTP $httpCode: $response (URL: $url)");
            // Don't throw for 404 on select, just return empty
            if ($httpCode === 406 || $httpCode === 404) {
                return ['data' => [], 'count' => 0];
            }
            throw new Exception("Supabase error ($httpCode): $response");
        }
        
        $data = json_decode($response, true);
        
        return ['data' => $data ?: [], 'count' => is_array($data) ? count($data) : 0];
    }
}

class SupabasePDO {
    public static $_lastId = null;
    
    const FETCH_ASSOC = 2;
    
    public function query($sql) {
        $stmt = new SupabaseStatement($sql);
        $stmt->execute([]);
        return $stmt;
    }
    
    public function prepare($sql) {
        return new SupabaseStatement($sql);
    }
    
    public function lastInsertId() {
        return self::$_lastId;
    }
    
    public function exec($sql) {
        $stmt = new SupabaseStatement($sql);
        $stmt->execute([]);
        return $stmt->rowCount();
    }
    
    public function beginTransaction() { return true; }
    public function commit() { return true; }
    public function rollBack() { return true; }
    public function inTransaction() { return false; }
    public function setAttribute($attr, $val) { return true; }
    public function getAttribute($attr) { return null; }
}

