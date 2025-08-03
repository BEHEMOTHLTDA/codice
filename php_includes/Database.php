<?php
/**
 * Códice do Criador - Classe Database
 * 
 * Classe responsável pelo gerenciamento de conexões com o banco de dados
 * e operações básicas de CRUD. Implementa o padrão Singleton para garantir
 * uma única instância de conexão.
 * 
 * @author Manus AI
 * @version 1.0
 */

class Database {
    private static $instance = null;
    private $pdo;
    
    /**
     * Construtor privado para implementar Singleton
     */
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => true,
            ];
            
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            logError("Erro de conexão com banco de dados: " . $e->getMessage());
            throw new Exception("Erro de conexão com o banco de dados");
        }
    }
    
    /**
     * Obtém a instância única da classe Database
     * 
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Obtém a conexão PDO
     * 
     * @return PDO
     */
    public function getConnection() {
        return $this->pdo;
    }
    
    /**
     * Executa uma query SELECT e retorna todos os resultados
     * 
     * @param string $sql
     * @param array $params
     * @return array
     */
    public function select($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            logError("Erro na query SELECT: " . $e->getMessage() . " | SQL: " . $sql);
            throw new Exception("Erro ao executar consulta");
        }
    }
    
    /**
     * Executa uma query SELECT e retorna apenas o primeiro resultado
     * 
     * @param string $sql
     * @param array $params
     * @return array|false
     */
    public function selectOne($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            logError("Erro na query SELECT: " . $e->getMessage() . " | SQL: " . $sql);
            throw new Exception("Erro ao executar consulta");
        }
    }
    
    /**
     * Executa uma query INSERT
     * 
     * @param string $table
     * @param array $data
     * @return int ID do registro inserido
     */
    public function insert($table, $data) {
        try {
            $columns = implode(', ', array_keys($data));
            $placeholders = ':' . implode(', :', array_keys($data));
            
            $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($data);
            
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            logError("Erro na query INSERT: " . $e->getMessage() . " | Tabela: " . $table);
            throw new Exception("Erro ao inserir dados");
        }
    }
    
    /**
     * Executa uma query UPDATE
     * 
     * @param string $table
     * @param array $data
     * @param array $where
     * @return int Número de linhas afetadas
     */
    public function update($table, $data, $where) {
        try {
            $setClause = [];
            foreach ($data as $key => $value) {
                $setClause[] = "{$key} = :{$key}";
            }
            $setClause = implode(', ', $setClause);
            
            $whereClause = [];
            foreach ($where as $key => $value) {
                $whereClause[] = "{$key} = :where_{$key}";
            }
            $whereClause = implode(' AND ', $whereClause);
            
            $sql = "UPDATE {$table} SET {$setClause} WHERE {$whereClause}";
            
            // Combinar parâmetros de dados e where
            $params = $data;
            foreach ($where as $key => $value) {
                $params["where_{$key}"] = $value;
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->rowCount();
        } catch (PDOException $e) {
            logError("Erro na query UPDATE: " . $e->getMessage() . " | Tabela: " . $table);
            throw new Exception("Erro ao atualizar dados");
        }
    }
    
    /**
     * Executa uma query DELETE
     * 
     * @param string $table
     * @param array $where
     * @return int Número de linhas afetadas
     */
    public function delete($table, $where) {
        try {
            $whereClause = [];
            foreach ($where as $key => $value) {
                $whereClause[] = "{$key} = :{$key}";
            }
            $whereClause = implode(' AND ', $whereClause);
            
            $sql = "DELETE FROM {$table} WHERE {$whereClause}";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($where);
            
            return $stmt->rowCount();
        } catch (PDOException $e) {
            logError("Erro na query DELETE: " . $e->getMessage() . " | Tabela: " . $table);
            throw new Exception("Erro ao deletar dados");
        }
    }
    
    /**
     * Executa uma query personalizada
     * 
     * @param string $sql
     * @param array $params
     * @return PDOStatement
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            logError("Erro na query personalizada: " . $e->getMessage() . " | SQL: " . $sql);
            throw new Exception("Erro ao executar query");
        }
    }
    
    /**
     * Inicia uma transação
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    /**
     * Confirma uma transação
     */
    public function commit() {
        return $this->pdo->commit();
    }
    
    /**
     * Desfaz uma transação
     */
    public function rollback() {
        return $this->pdo->rollback();
    }
    
    /**
     * Verifica se está em uma transação
     * 
     * @return bool
     */
    public function inTransaction() {
        return $this->pdo->inTransaction();
    }
    
    /**
     * Obtém o último ID inserido
     * 
     * @return string
     */
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Conta registros em uma tabela com condições opcionais
     * 
     * @param string $table
     * @param array $where
     * @return int
     */
    public function count($table, $where = []) {
        try {
            $sql = "SELECT COUNT(*) as total FROM {$table}";
            
            if (!empty($where)) {
                $whereClause = [];
                foreach ($where as $key => $value) {
                    $whereClause[] = "{$key} = :{$key}";
                }
                $sql .= " WHERE " . implode(' AND ', $whereClause);
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($where);
            $result = $stmt->fetch();
            
            return (int) $result['total'];
        } catch (PDOException $e) {
            logError("Erro na query COUNT: " . $e->getMessage() . " | Tabela: " . $table);
            throw new Exception("Erro ao contar registros");
        }
    }
    
    /**
     * Verifica se um registro existe
     * 
     * @param string $table
     * @param array $where
     * @return bool
     */
    public function exists($table, $where) {
        return $this->count($table, $where) > 0;
    }
    
    /**
     * Busca com paginação
     * 
     * @param string $sql
     * @param array $params
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function paginate($sql, $params = [], $page = 1, $perPage = 20) {
        try {
            // Contar total de registros
            $countSql = "SELECT COUNT(*) as total FROM ({$sql}) as count_query";
            $stmt = $this->pdo->prepare($countSql);
            $stmt->execute($params);
            $totalRecords = $stmt->fetch()['total'];
            
            // Calcular offset
            $offset = ($page - 1) * $perPage;
            
            // Buscar registros da página atual
            $paginatedSql = $sql . " LIMIT {$perPage} OFFSET {$offset}";
            $stmt = $this->pdo->prepare($paginatedSql);
            $stmt->execute($params);
            $records = $stmt->fetchAll();
            
            return [
                'data' => $records,
                'total' => $totalRecords,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($totalRecords / $perPage),
                'has_next' => $page < ceil($totalRecords / $perPage),
                'has_prev' => $page > 1
            ];
        } catch (PDOException $e) {
            logError("Erro na paginação: " . $e->getMessage() . " | SQL: " . $sql);
            throw new Exception("Erro ao paginar resultados");
        }
    }
    
    /**
     * Previne clonagem da instância
     */
    private function __clone() {}
    
    /**
     * Previne deserialização da instância
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}
?>

