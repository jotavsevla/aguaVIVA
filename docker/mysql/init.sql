-- Database: aguaVIVA
-- Estrutura inicial para o MVP

-- Tabela de usuários (já existente no sistema)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    access_level ENUM('admin', 'supervisor') NOT NULL DEFAULT 'admin',
    last_login DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de clientes (já existente no sistema)
CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(200) NOT NULL,
    cpf VARCHAR(14) NULL,
    endereco VARCHAR(500) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    telefone2 VARCHAR(20) NULL,
    latitude DECIMAL(10, 8) NULL,
    longitude DECIMAL(11, 8) NULL,
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de entregadores
CREATE TABLE IF NOT EXISTS entregadores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(200) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    veiculo VARCHAR(100) NULL,
    placa VARCHAR(10) NULL,
    status ENUM('disponivel', 'em_entrega', 'indisponivel') DEFAULT 'indisponivel',
    latitude_atual DECIMAL(10, 8) NULL,
    longitude_atual DECIMAL(11, 8) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de entregas
CREATE TABLE IF NOT EXISTS entregas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    entregador_id INT NULL,
    endereco_entrega VARCHAR(500) NOT NULL,
    latitude_destino DECIMAL(10, 8) NULL,
    longitude_destino DECIMAL(11, 8) NULL,
    distancia_km DECIMAL(10, 2) NULL,
    tempo_estimado_min INT NULL,
    status ENUM('pendente', 'atribuida', 'em_andamento', 'concluida', 'cancelada') DEFAULT 'pendente',
    observacoes TEXT NULL,
    data_solicitacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atribuicao DATETIME NULL,
    data_inicio DATETIME NULL,
    data_conclusao DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (entregador_id) REFERENCES entregadores(id) ON DELETE SET NULL
);

-- Usuário admin padrão (senha: admin123)
INSERT INTO users (user, password, access_level) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
ON DUPLICATE KEY UPDATE user=user;

-- Entregadores de exemplo
INSERT INTO entregadores (nome, telefone, veiculo, placa, status) VALUES
('Carlos Silva', '(11) 99999-0001', 'Moto Honda CG 160', 'ABC-1234', 'disponivel'),
('Maria Santos', '(11) 99999-0002', 'Moto Yamaha Factor', 'DEF-5678', 'disponivel'),
('João Oliveira', '(11) 99999-0003', 'Fiorino', 'GHI-9012', 'indisponivel');
